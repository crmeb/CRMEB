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

use app\services\user\UserLabelCateServices;
use app\services\user\UserLabelServices;
use crmeb\exceptions\AdminException;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\McpException;

/**
 * MCP 工具：修改用户标签
 *
 * 与管理后台「用户标签」同链路：调用 UserLabelServices::save 更新已有标签；
 * 未提供的参数沿用原值；参数预检在工具层完成（McpException 消息可透传），
 * 服务层业务校验不因 AI 调用而豁免。
 */
class UserLabelUpdateTool implements McpToolInterface
{
    /**
     * 用户标签服务
     * @var UserLabelServices
     */
    private UserLabelServices $services;

    /**
     * 标签分类服务
     * @var UserLabelCateServices
     */
    private UserLabelCateServices $cateServices;

    /**
     * 构造函数
     * @param UserLabelServices $services 用户标签服务
     * @param UserLabelCateServices $cateServices 标签分类服务
     */
    public function __construct(UserLabelServices $services, UserLabelCateServices $cateServices)
    {
        $this->services = $services;
        $this->cateServices = $cateServices;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_user_label_update';
    }

    /**
     * 交互声明：修改标签会变更系统配置数据，需用户确认后才真实执行
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
        return '修改一个已有用户标签的名称或所属分类，未提供的字段沿用原值，返回修改后的标签信息；'
            . '必填参数：id 标签 ID（可通过 crmeb_user_label_list 查询）；'
            . '可选参数：label_name 新名称（非空，50 字以内）、label_cate 新分类 ID（必须是已存在的分类，'
            . '可用分类见 crmeb_user_label_list 返回的 cates 字段），两者至少提供一个；'
            . '同一分类下不允许与其他标签重名；创建标签用 crmeb_user_label_create，删除标签用 crmeb_user_label_delete。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '标签修改';
    }

    /**
     * 工具分类（管理后台分组展示）
     * @return string
     */
    public function category(): string
    {
        return '用户';
    }

    /**
     * 入参 Schema
     * @return array
     */
    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'required'   => ['id'],
            'properties' => [
                'id'         => ['type' => 'integer', 'minimum' => 1, 'description' => '标签 ID，可通过 crmeb_user_label_list 查询'],
                'label_name' => ['type' => 'string', 'maxLength' => 50, 'description' => '新标签名称，非空，50 字以内；不传沿用原值'],
                'label_cate' => ['type' => 'integer', 'minimum' => 1, 'description' => '新标签分类 ID，必须是已存在的分类；不传沿用原值'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（id，label_name/label_cate 至少其一）
     * @return array 修改后的标签信息
     * @throws McpException 参数非法、标签不存在或服务层业务校验失败
     */
    public function execute(array $args): array
    {
        $id = self::positiveIntegerArg($args, 'id');
        if (!isset($args['label_name']) && !isset($args['label_cate'])) {
            throw new McpException('label_name 与 label_cate 至少提供一个');
        }

        $label = $this->services->getLable($id);
        if (!$label) {
            throw new McpException('标签不存在，可通过 crmeb_user_label_list 查询有效标签 ID');
        }

        $name = trim((string)($args['label_name'] ?? $label->getData('label_name')));
        if ($name === '') {
            throw new McpException('label_name 不能为空');
        }
        if (mb_strlen($name) > 50) {
            throw new McpException('label_name 不能超过 50 个字');
        }

        $cateId = (int)$label->getData('label_cate');
        if (isset($args['label_cate'])) {
            $cateId = self::positiveIntegerArg($args, 'label_cate');
            if (!$this->cateExists($cateId)) {
                throw new McpException('label_cate 标签分类不存在，可用分类见 crmeb_user_label_list 返回的 cates');
            }
        }

        // 与 UserLabelController::save 的 postMore 结构保持一致，由服务层完成入库与查重
        try {
            $this->services->save($id, ['id' => $id, 'label_cate' => $cateId, 'label_name' => $name]);
        } catch (AdminException $e) {
            // 服务层业务校验失败：转 McpException 让消息透传给 AI 客户端
            throw new McpException($e->getMessage());
        }

        return [
            'id'         => $id,
            'label_name' => $name,
            'label_cate' => $cateId,
            'message'    => '用户标签修改成功，可通过 crmeb_user_label_list 查询确认',
        ];
    }

    /**
     * 校验标签分类是否存在
     * @param int $cateId 分类 ID
     * @return bool 是否存在
     */
    private function cateExists(int $cateId): bool
    {
        $cateIds = array_map('intval', array_column($this->cateServices->getLabelCateAll(), 'id'));
        return in_array($cateId, $cateIds, true);
    }

    /**
     * @param array $args 工具参数
     * @param string $name 参数名称
     * @return int 大于 0 的整数
     * @throws McpException 非整数或小于等于 0
     */
    private static function positiveIntegerArg(array $args, string $name): int
    {
        $value = $args[$name] ?? null;
        if (!is_int($value) && (!is_string($value) || !ctype_digit($value))) {
            throw new McpException($name . ' 必须是大于 0 的整数');
        }
        $number = (int)$value;
        if ($number <= 0) {
            throw new McpException($name . ' 必须是大于 0 的整数');
        }
        return $number;
    }
}
