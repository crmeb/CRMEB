<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\service\mcp\tools;

use app\dao\product\CenterProductDao;
use app\dao\product\product\StoreCategoryDao;
use app\services\activity\coupon\StoreCouponIssueServices;
use crmeb\exceptions\AdminException;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\McpException;

/**
 * MCP 工具：添加优惠券（创建发行配置）。
 *
 * 与管理后台「添加优惠券」同链路：组装与 StoreCouponIssueController::saveCoupon
 * 一致的数据结构后调用 StoreCouponIssueServices::saveCoupon；参数预检在工具层
 * 完成（McpException 消息可透传），服务层业务校验不因 AI 调用而豁免。
 */
class CouponCreateTool implements McpToolInterface
{
    /**
     * 优惠券发行服务
     * @var StoreCouponIssueServices
     */
    private StoreCouponIssueServices $services;

    /**
     * 商品分类 DAO（品类券 ID 存在性校验）
     * @var StoreCategoryDao
     */
    private StoreCategoryDao $categoryDao;

    /**
     * 中台商品 DAO（商品券 ID 存在性校验）
     * @var CenterProductDao
     */
    private CenterProductDao $productDao;

    /**
     * 构造函数
     * @param StoreCouponIssueServices $services 优惠券发行服务
     * @param StoreCategoryDao $categoryDao 商品分类 DAO
     * @param CenterProductDao $productDao 中台商品 DAO
     */
    public function __construct(
        StoreCouponIssueServices $services,
        StoreCategoryDao $categoryDao,
        CenterProductDao $productDao
    ) {
        $this->services = $services;
        $this->categoryDao = $categoryDao;
        $this->productDao = $productDao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_coupon_create';
    }

    /**
     * 交互声明：创建优惠券会新增营销配置并可能被用户领取，需用户确认后才真实执行
     * @return string execute=需确认后执行
     */
    public function interaction(): string
    {
        return 'execute';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '创建一张新的优惠券发行配置，等同管理后台「添加优惠券」，成功返回新建的 coupon_id；'
            . '不能编辑或删除已有优惠券，创建后的停用、开启、删除需在管理后台操作。'
            . '必填参数：coupon_title 优惠券名称（18 字以内）；coupon_price 面值（元，必须大于 0）；'
            . 'use_min_price 使用门槛（元，0=无门槛，默认 0）。'
            . 'type 优惠券类型：0=通用券（默认）、1=品类券（必须传 category_id 品类 ID 数组）、2=商品券（必须传 product_id 商品 ID 数组）；'
            . '品类/商品 ID 必须真实存在（工具会校验），创建前先通过 crmeb_category_list 查品类、crmeb_product_list 查商品获取有效 ID。'
            . '有效期二选一：coupon_time 领取后有效天数（正整数，默认 0），coupon_time=0 时必须传 start_use_time 与 end_use_time 时间段'
            . '（格式 YYYY-MM-DD HH:mm:ss，开始时间不能早于今天且不能早于领取开始时间，结束时间不能早于开始时间；coupon_time 大于 0 时忽略时间段参数）。'
            . 'user_type 适用用户：1=普通用户（默认）、2=付费会员专享（此时 receive_type 参数被忽略，服务端固定为会员领取）。'
            . 'receive_type 发送方式：1=用户主动领取（默认）、3=系统赠送（固定不限量，忽略发布数量设置）。'
            . '发布数量：is_permanent=1 不限量（默认）；is_permanent=0 限量时 total_count 发布张数（≥1）必须 ≥ receive_limit 每人限领张数（默认 1）。'
            . '领取时间可选：start_time 与 end_time 必须成对提供（YYYY-MM-DD HH:mm:ss，开始时间不能早于今天，结束时间不能早于开始时间），不传表示不限时；'
            . '使用有效期开始时间不能早于领取开始时间，若两者同日请将使用开始时间设置为不早于领取开始时刻。'
            . 'sort 排序值（默认 0）；status：1=开启（默认）、0=关闭。'
            . '创建后可通过 crmeb_coupon_list 查询配置，用户领取记录见 crmeb_coupon_claim_list。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '优惠券创建';
    }

    /**
     * 工具分类（管理后台分组展示）
     * @return string
     */
    public function category(): string
    {
        return '营销';
    }

    /**
     * 入参 Schema
     * @return array
     */
    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'required'   => ['coupon_title', 'coupon_price'],
            'properties' => [
                'coupon_title'   => ['type' => 'string', 'maxLength' => 18, 'description' => '优惠券名称，18 字以内'],
                'coupon_price'   => ['type' => 'number', 'exclusiveMinimum' => 0, 'description' => '优惠券面值（元），必须大于 0'],
                'use_min_price'  => ['type' => 'number', 'minimum' => 0, 'description' => '使用门槛（最低消费金额，元），0=无门槛，默认 0'],
                'type'           => ['type' => 'integer', 'enum' => [0, 1, 2], 'description' => '0=通用券（默认），1=品类券，2=商品券'],
                'category_id'    => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => '品类券适用的品类 ID 数组（任意层级均可），type=1 时必填，ID 需真实存在，可先通过 crmeb_category_list 查询'],
                'product_id'     => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => '商品券适用的中台商品 ID 数组，type=2 时必填，ID 需真实存在，可先通过 crmeb_product_list 查询'],
                'coupon_time'    => ['type' => 'integer', 'minimum' => 0, 'description' => '领取后有效天数；0=改用 start_use_time/end_use_time 时间段（默认 0），大于 0 时忽略时间段'],
                'start_use_time' => ['type' => 'string', 'description' => '使用有效期开始时间，YYYY-MM-DD HH:mm:ss，coupon_time=0 时必填，不能早于今天及领取开始时间'],
                'end_use_time'   => ['type' => 'string', 'description' => '使用有效期结束时间，YYYY-MM-DD HH:mm:ss，coupon_time=0 时必填，不能早于开始时间'],
                'user_type'      => ['type' => 'integer', 'enum' => [1, 2], 'description' => '1=普通用户可领（默认），2=付费会员专享'],
                'receive_type'   => ['type' => 'integer', 'enum' => [1, 3], 'description' => '1=用户主动领取（默认），3=系统赠送；user_type=2 时被忽略'],
                'is_permanent'   => ['type' => 'integer', 'enum' => [0, 1], 'description' => '1=不限量（默认），0=限量发布'],
                'total_count'    => ['type' => 'integer', 'minimum' => 1, 'description' => '发布数量（张），is_permanent=0 时必须 ≥ receive_limit，默认 1'],
                'receive_limit'  => ['type' => 'integer', 'minimum' => 1, 'description' => '每人限领数量（张），默认 1'],
                'start_time'     => ['type' => 'string', 'description' => '领取开始时间，YYYY-MM-DD HH:mm:ss，与 end_time 成对提供，不能早于今天'],
                'end_time'       => ['type' => 'string', 'description' => '领取结束时间，YYYY-MM-DD HH:mm:ss，与 start_time 成对提供，不能早于领取开始时间'],
                'sort'           => ['type' => 'integer', 'description' => '排序值，默认 0'],
                'status'         => ['type' => 'integer', 'enum' => [0, 1], 'description' => '1=开启（默认），0=关闭'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 优惠券配置参数
     * @return array 新建优惠券 ID 与生效配置摘要
     * @throws McpException 参数非法或服务层业务校验失败
     */
    public function execute(array $args): array
    {
        $title = trim((string)($args['coupon_title'] ?? ''));
        if ($title === '') {
            throw new McpException('coupon_title 不能为空');
        }
        if (mb_strlen($title) > 18) {
            throw new McpException('coupon_title 不能超过 18 个字');
        }
        $couponPrice = self::numberArg($args, 'coupon_price', 0.0);
        if ($couponPrice <= 0) {
            throw new McpException('coupon_price 必须大于 0');
        }
        $useMinPrice = self::numberArg($args, 'use_min_price', 0.0);
        if ($useMinPrice < 0) {
            throw new McpException('use_min_price 不能小于 0');
        }

        $type = self::integerArg($args, 'type', 0, 0, 2);
        $categoryId = [];
        $productId = [];
        if ($type === 1) {
            $categoryId = self::idArrayArg($args, 'category_id');
            if (!$categoryId) {
                throw new McpException('品类券（type=1）必须提供 category_id 品类 ID 数组');
            }
            $this->validateCategoryIds($categoryId);
        } elseif ($type === 2) {
            $productId = self::idArrayArg($args, 'product_id');
            if (!$productId) {
                throw new McpException('商品券（type=2）必须提供 product_id 商品 ID 数组');
            }
            $this->validateProductIds($productId);
        }

        // 领取时间可选，成对提供；user_type=2 时服务层强制会员领取，这里对齐后台传值
        $userType = self::integerArg($args, 'user_type', 1, 1, 2);
        $receiveType = $userType === 2 ? 1 : self::integerArg($args, 'receive_type', 1, 1, 3);
        $startTime = self::dateTimeArg($args, 'start_time');
        $endTime = self::dateTimeArg($args, 'end_time');
        if (($startTime === '') !== ($endTime === '')) {
            throw new McpException('start_time 与 end_time 必须同时提供');
        }
        if ($startTime !== '') {
            if (strtotime($startTime) < strtotime(date('Y-m-d 00:00:00'))) {
                throw new McpException('start_time 领取开始时间不能早于当前时间');
            }
            if (strtotime($endTime) < strtotime($startTime)) {
                throw new McpException('end_time 领取结束时间不能早于领取开始时间');
            }
        }

        // 使用有效期：整天数模式忽略时间段；时间段模式校验成对、不早于今天与先后顺序
        $couponTime = self::integerArg($args, 'coupon_time', 0, 0);
        $startUseTime = $endUseTime = '';
        if ($couponTime === 0) {
            $startUseTime = self::dateTimeArg($args, 'start_use_time');
            $endUseTime = self::dateTimeArg($args, 'end_use_time');
            if ($startUseTime === '' || $endUseTime === '') {
                throw new McpException('coupon_time 为 0 时必须提供 start_use_time 与 end_use_time（YYYY-MM-DD HH:mm:ss）');
            }
            if (strtotime($startUseTime) < strtotime(date('Y-m-d 00:00:00'))) {
                throw new McpException('start_use_time 开始使用时间不能早于当前时间');
            }
            if (strtotime($endUseTime) < strtotime($startUseTime)) {
                throw new McpException('end_use_time 结束使用时间不能早于开始使用时间');
            }
            // 与服务层规则一致：使用开始不能早于领取开始
            if ($startTime !== '' && strtotime($startUseTime) < strtotime($startTime)) {
                throw new McpException('start_use_time 使用开始时间不能早于 start_time 领取开始时间');
            }
        }

        // 数量规则：系统赠送固定不限量；限量时每人限领不得大于发布数量
        $receiveLimit = self::integerArg($args, 'receive_limit', 1, 1);
        $isPermanent = self::integerArg($args, 'is_permanent', 1, 0, 1);
        if ($receiveType === 3) {
            $isPermanent = 1;
        }
        $totalCount = self::integerArg($args, 'total_count', 1, 1);
        if ($isPermanent === 1) {
            $totalCount = 0;
        } elseif ($receiveLimit > $totalCount) {
            throw new McpException('receive_limit 每人限领数量不能大于 total_count 发布数量');
        }

        $status = self::integerArg($args, 'status', 1, 0, 1);
        $sort = self::integerArg($args, 'sort', 0);

        // 与 StoreCouponIssueController::saveCoupon 的 postMore 结构保持一致，由服务层完成入库
        $data = [
            'id'             => 0,
            'coupon_title'   => $title,
            'coupon_price'   => $couponPrice,
            'use_min_price'  => $useMinPrice,
            'coupon_time'    => $couponTime,
            'start_use_time' => $startUseTime === '' ? 0 : $startUseTime,
            'end_use_time'   => $endUseTime === '' ? 0 : $endUseTime,
            'start_time'     => $startTime === '' ? 0 : $startTime,
            'end_time'       => $endTime === '' ? 0 : $endTime,
            'receive_type'   => $receiveType,
            'is_permanent'   => $isPermanent,
            'total_count'    => $totalCount,
            'product_id'     => $productId ? implode(',', $productId) : '',
            'category_id'    => $categoryId,
            'type'           => $type,
            'sort'           => $sort,
            'status'         => $status,
            'receive_limit'  => $receiveLimit,
            'user_type'      => $userType,
        ];

        try {
            $couponId = $this->services->saveCoupon($data);
        } catch (AdminException $e) {
            // 服务层业务校验失败：转 McpException 让消息透传给 AI 客户端
            throw new McpException($e->getMessage());
        }

        return [
            'coupon_id'     => (int)$couponId,
            'coupon_title'  => $title,
            'coupon_price'  => $couponPrice,
            'use_min_price' => $useMinPrice,
            'type'          => $type,
            'receive_type'  => $userType === 2 ? 4 : $receiveType,
            'is_permanent'  => $isPermanent,
            'total_count'   => $totalCount,
            'receive_limit' => $receiveLimit,
            'status'        => $status,
            'message'       => '优惠券创建成功，可通过 crmeb_coupon_list 查询',
        ];
    }

    /**
     * @param array $args 工具参数
     * @param string $name 参数名称
     * @param float $default 默认值
     * @return float 数值
     * @throws McpException 非数字
     */
    private static function numberArg(array $args, string $name, float $default): float
    {
        $value = $args[$name] ?? $default;
        if (is_int($value) || is_float($value)) {
            return (float)$value;
        }
        if (is_string($value) && $value !== '' && is_numeric($value)) {
            return (float)$value;
        }
        throw new McpException($name . ' 必须是数字');
    }

    /**
     * @param array $args 工具参数
     * @param string $name 参数名称
     * @param int $default 默认值
     * @param int|null $min 最小值
     * @param int|null $max 最大值
     * @return int 整数
     * @throws McpException 非整数或超出范围
     */
    private static function integerArg(array $args, string $name, int $default, ?int $min = null, ?int $max = null): int
    {
        $value = $args[$name] ?? $default;
        if (!is_int($value) && (!is_string($value) || !ctype_digit($value))) {
            throw new McpException($name . ' 必须是整数');
        }
        $options = array_filter(['min_range' => $min, 'max_range' => $max], static fn($v) => $v !== null);
        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => $options]);
        if ($number === false) {
            throw new McpException($name . ' 取值超出允许范围');
        }
        return $number;
    }

    /**
     * @param array $args 工具参数
     * @param string $name 参数名称
     * @return int[] 去重后的正整数 ID 列表
     * @throws McpException 不是 ID 数组
     */
    private static function idArrayArg(array $args, string $name): array
    {
        $value = $args[$name] ?? [];
        if (is_int($value) || (is_string($value) && $value !== '')) {
            $value = [$value];
        }
        if (!is_array($value)) {
            throw new McpException($name . ' 必须是 ID 数组');
        }
        $ids = [];
        foreach ($value as $item) {
            $valid = is_int($item) || (is_string($item) && ctype_digit($item));
            if (!$valid || (int)$item <= 0) {
                throw new McpException($name . ' 数组内必须全部是大于 0 的整数 ID');
            }
            $ids[] = (int)$item;
        }
        return array_values(array_unique($ids));
    }

    /**
     * 校验品类 ID 均真实存在（服务层不校验，防止创建出永远匹配不到商品的无效品类券）
     * @param int[] $categoryIds 品类 ID 列表
     * @return void
     * @throws McpException 存在不存在的品类 ID
     */
    private function validateCategoryIds(array $categoryIds): void
    {
        $exist = (array)$this->categoryDao->getColumn([['id', 'in', $categoryIds]], 'id');
        $missing = array_diff($categoryIds, array_map('intval', $exist));
        if ($missing) {
            throw new McpException('category_id 中以下品类 ID 不存在：' . implode('、', $missing) . '，请先通过 crmeb_category_list 查询有效品类 ID');
        }
    }

    /**
     * 校验商品 ID 均真实存在（服务层不校验，防止创建出无法关联商品的无效商品券）
     * @param int[] $productIds 商品 ID 列表
     * @return void
     * @throws McpException 存在不存在的商品 ID
     */
    private function validateProductIds(array $productIds): void
    {
        $exist = (array)$this->productDao->getColumn([['id', 'in', $productIds]], 'id');
        $missing = array_diff($productIds, array_map('intval', $exist));
        if ($missing) {
            throw new McpException('product_id 中以下商品 ID 不存在：' . implode('、', $missing) . '，请先通过 crmeb_product_list 查询有效商品 ID');
        }
    }

    /**
     * @param array $args 工具参数
     * @param string $name 参数名称
     * @return string 可解析的时间字符串，未传返回空串
     * @throws McpException 时间格式无效
     */
    private static function dateTimeArg(array $args, string $name): string
    {
        $value = $args[$name] ?? '';
        if ($value === '' || $value === 0 || $value === '0') {
            return '';
        }
        if ((!is_string($value) && !is_int($value)) || strtotime((string)$value) === false) {
            throw new McpException($name . ' 必须是有效的时间字符串，格式 YYYY-MM-DD HH:mm:ss');
        }
        return (string)$value;
    }
}
