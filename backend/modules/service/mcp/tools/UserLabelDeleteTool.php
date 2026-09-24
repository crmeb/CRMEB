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

use app\services\user\UserLabelServices;
use crmeb\exceptions\AdminException;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\McpException;

/**
 * MCP 工具：删除用户标签
 *
 * 与管理后台「用户标签」同链路：调用 UserLabelServices::delLabel 删除标签；
 * 参数预检在工具层完成（McpException 消息可透传），服务层业务校验不因 AI 调用而豁免。
 */
class UserLabelDeleteTool implements McpToolInterface
{
    /**
     * 用户标签服务
     * @var UserLabelServices
     */
    private UserLabelServices $services;

    /**
     * 构造函数
     * @param UserLabelServices $services 用户标签服务
     */
    public function __construct(UserLabelServices $services)
    {
        $this->services = $services;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_user_label_delete';
    }

    /**
     * 交互声明：删除标签会不可逆地移除系统配置数据，需用户确认后才真实执行；
     * delete=删除类写操作，后台权限勾选与工具列表按「删」标注展示
     * @return string delete=删除类，需确认后执行
     */
    public function interaction(): string
    {
        return 'delete';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '删除一个用户标签，删除后该标签从标签列表消失且不可恢复；'
            . '必填参数：id 标签 ID（可通过 crmeb_user_label_list 查询）；'
            . '仅删除标签本身，不清理用户与该标签的既有关联记录（与管理后台删除行为一致），'
            . '删除前建议先用 crmeb_user_list 按 label_id 筛选确认受影响用户；'
            . '创建标签用 crmeb_user_label_create，修改标签用 crmeb_user_label_update。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '标签删除';
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
                'id' => ['type' => 'integer', 'minimum' => 1, 'description' => '待删除的标签 ID，可通过 crmeb_user_label_list 查询'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（id）
     * @return array 删除结果摘要
     * @throws McpException 参数非法、标签不存在或服务层业务校验失败
     */
    public function execute(array $args): array
    {
        $id = self::positiveIntegerArg($args, 'id');

        $label = $this->services->getLable($id);
        if (!$label) {
            throw new McpException('标签不存在，可通过 crmeb_user_label_list 查询有效标签 ID');
        }
        $name = (string)$label->getData('label_name');

        try {
            $this->services->delLabel($id);
        } catch (AdminException $e) {
            // 服务层业务校验失败：转 McpException 让消息透传给 AI 客户端
            throw new McpException($e->getMessage());
        }

        return [
            'id'          => $id,
            'label_name'  => $name,
            'message'     => '用户标签删除成功，可通过 crmeb_user_label_list 查询确认',
        ];
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
