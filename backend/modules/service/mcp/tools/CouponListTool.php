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

use app\dao\activity\coupon\StoreCouponIssueDao;
use modules\service\mcp\McpException;
use crmeb\interfaces\McpToolInterface;

/**
 * MCP 工具：优惠券发行配置列表。
 */
class CouponListTool implements McpToolInterface
{
    /**
     * 优惠券发放 DAO
     * @var StoreCouponIssueDao
     */
    private StoreCouponIssueDao $dao;

    /**
     * 构造函数
     * @param StoreCouponIssueDao $dao 优惠券发放 DAO
     */
    public function __construct(StoreCouponIssueDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_coupon_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '查询优惠券发行配置（优惠门槛、领取期限、数量与启用状态），不是用户已领取或持有的优惠券。'
            . '默认包含未删除但已停用、过期或领完的配置；claimable_only=true 仅判断发行侧当前允许主动领取，'
            . '会员等用户资格仍需另行校验。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '优惠券配置';
    }

    /**
     * 入参 Schema
     * @return array
     */
    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 1000, 'description' => '页码，默认 1'],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'description' => '每页数量，默认 10，最多 100'],
                'keyword' => ['type' => 'string', 'maxLength' => 80, 'description' => '按发行名称搜索'],
                'coupon_type' => ['type' => 'integer', 'enum' => [0, 1, 2], 'description' => '0=通用券，1=品类券，2=商品券'],
                'claimable_only' => ['type' => 'boolean', 'description' => '仅返回发行侧允许主动领取的配置，不校验用户资格，默认 false'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 页码、发行名称、券类型及可领筛选
     * @return array 优惠券发行配置列表和总数
     * @throws McpException 参数错误
     */
    public function execute(array $args): array
    {
        $page = self::integerArg($args, 'page', 1, 1000);
        $limit = min(100, self::integerArg($args, 'limit', 10));
        $keyword = $args['keyword'] ?? '';
        if (!is_string($keyword) || mb_strlen($keyword) > 80) {
            throw new McpException('keyword 必须是 80 字以内的字符串');
        }
        $keyword = trim($keyword);
        $couponType = $args['coupon_type'] ?? null;
        if ($couponType !== null) {
            if (!is_int($couponType) && (!is_string($couponType) || !ctype_digit($couponType))) {
                throw new McpException('coupon_type 必须是 0、1 或 2');
            }
            $couponType = filter_var($couponType, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 2]]);
            if ($couponType === false) {
                throw new McpException('coupon_type 必须是 0、1 或 2');
            }
        }
        $claimableOnly = $args['claimable_only'] ?? false;
        if (!is_bool($claimableOnly)) {
            throw new McpException('claimable_only 必须是布尔值');
        }

        $list = $this->issueQuery($keyword, $couponType, $claimableOnly)
            ->field('id,cid,coupon_title,coupon_price,use_min_price,type,receive_type,status,total_count,remain_count,receive_limit,is_permanent,start_time,end_time,start_use_time,end_use_time,coupon_time')
            ->order('id desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $count = $this->issueQuery($keyword, $couponType, $claimableOnly)->count();
        return ['list' => $list, 'count' => $count];
    }

    /**
     * @param array $args 工具参数
     * @param string $name 参数名称
     * @param int $default 默认值
     * @param int|null $max 可选上限
     * @return int 校验后的正整数
     * @throws McpException 参数错误
     */
    private static function integerArg(array $args, string $name, int $default, ?int $max = null): int
    {
        $value = $args[$name] ?? $default;
        if (!is_int($value) && (!is_string($value) || !ctype_digit($value))) {
            throw new McpException($name . ' 必须是正整数');
        }
        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($number === false || ($max !== null && $number > $max)) {
            throw new McpException($name . ' 必须是有效的正整数');
        }
        return $number;
    }

    /**
     * @param string $keyword 发行名称关键词
     * @param int|null $couponType 券类型
     * @param bool $claimableOnly 是否仅查询可主动领取的配置
     * @return \think\Model 发行配置查询
     */
    private function issueQuery(string $keyword, ?int $couponType, bool $claimableOnly)
    {
        // 发行 DAO 默认不应用数组条件，使用显式 where 确保软删除过滤生效。
        $query = $this->dao->search()->where('is_del', 0);
        if ($keyword !== '') {
            $query->where('coupon_title', 'like', '%' . addcslashes($keyword, '%_') . '%');
        }
        if ($couponType !== null) {
            $query->where('type', $couponType);
        }
        if ($claimableOnly) {
            $now = time();
            $query->where('status', 1)->whereIn('receive_type', [1, 4])
                ->where(function ($q) {
                    $q->where('remain_count', '>', 0)->whereOr('is_permanent', 1);
                })->where(function ($q) use ($now) {
                    $q->where(function ($window) use ($now) {
                        $window->where('start_time', '<', $now)->where('end_time', '>', $now);
                    })->whereOr(function ($window) {
                        $window->where('start_time', 0)->where('end_time', 0);
                    });
                });
        }
        return $query;
    }
}
