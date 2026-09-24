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

use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\McpException;

/**
 * MCP 工具：设置用户标签
 *
 * 与管理后台「设置标签」同链路：调用 UserServices::saveSetLabel 批量
 * 覆盖 / 追加 / 移除用户标签；标签存在性与写入结果由服务层校验。
 */
class UserLabelSetTool implements McpToolInterface
{
    /**
     * 标签操作类型：清空原有后设置（覆盖）
     */
    private const LABEL_TYPE_REPLACE = 0;

    /**
     * 标签操作类型：保留原有叠加（追加）
     */
    private const LABEL_TYPE_APPEND = 1;

    /**
     * 标签操作类型：移除指定标签
     */
    private const LABEL_TYPE_REMOVE = 2;

    /**
     * 用户服务
     * @var UserServices
     */
    private UserServices $services;

    /**
     * 构造函数
     * @param UserServices $services 用户服务
     */
    public function __construct(UserServices $services)
    {
        $this->services = $services;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_user_label_set';
    }

    /**
     * 交互声明：调整用户标签会变更用户数据，需用户确认后才真实执行
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
        return '为一个或多个用户设置用户标签，等同管理后台「设置标签」，支持覆盖、追加、移除三种方式，成功返回本次操作的标签与用户数。'
            . '必填参数：uids 用户ID数组（支持批量）；label_ids 标签ID数组。'
            . 'label_type 操作方式：0=覆盖（默认，清空用户原有标签后设置 label_ids）、1=追加（保留原有标签叠加 label_ids）、'
            . '2=移除（从用户身上移除 label_ids 指定的标签）。'
            . 'label_ids 必须是已存在的用户标签，标签 ID 与分类清单可用 crmeb_user_label_list 查询（创建新标签用 crmeb_user_label_create），'
            . '标签不存在时服务层会整体拒绝。'
            . '不能创建或删除标签本身；改用户分组用 crmeb_user_group_set。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '标签设置';
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
            'required'   => ['uids', 'label_ids'],
            'properties' => [
                'uids'       => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => '用户ID数组，支持批量设置'],
                'label_ids'  => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => '标签ID数组；label_type=2 时为要移除的标签'],
                'label_type' => ['type' => 'integer', 'enum' => [0, 1, 2], 'description' => '0=覆盖原有标签（默认），1=追加标签，2=移除指定标签'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（uids、label_ids、label_type）
     * @return array 本次标签操作结果
     * @throws McpException 参数非法或服务层业务校验失败
     */
    public function execute(array $args): array
    {
        $uids = UserEditPayload::idArray($args['uids'] ?? [], 'uids');
        $uids = array_values(array_filter($uids, static fn(int $id) => $id > 0));
        if (!$uids) {
            throw new McpException('uids 必须是至少一个大于 0 的用户ID数组');
        }
        $labelIds = UserEditPayload::idArray($args['label_ids'] ?? [], 'label_ids');
        if (!$labelIds) {
            throw new McpException('label_ids 必须是至少一个标签ID');
        }
        $labelType = (int)($args['label_type'] ?? self::LABEL_TYPE_REPLACE);
        if (!in_array($labelType, [self::LABEL_TYPE_REPLACE, self::LABEL_TYPE_APPEND, self::LABEL_TYPE_REMOVE], true)) {
            throw new McpException('label_type 只能是 0（覆盖）、1（追加）或 2（移除）');
        }

        try {
            $this->services->saveSetLabel($uids, $labelIds, $labelType);
        } catch (AdminException $e) {
            // 「有标签不存在或被删除」「设置标签失败」等业务错误透传
            throw new McpException($e->getMessage());
        }

        $actionText = [self::LABEL_TYPE_REPLACE => '覆盖为', self::LABEL_TYPE_APPEND => '追加', self::LABEL_TYPE_REMOVE => '移除'][$labelType];
        return [
            'label_type' => $labelType,
            'label_ids'  => $labelIds,
            'uid_count'  => count($uids),
            'uids'       => $uids,
            'message'    => '已把 ' . count($uids) . ' 个用户的标签' . $actionText . ' ' . implode(',', $labelIds),
        ];
    }
}
