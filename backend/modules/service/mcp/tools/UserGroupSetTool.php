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
 * MCP 工具：设置用户分组
 *
 * 与管理后台「设置分组」同链路：调用 UserServices::saveSetGroup 批量
 * 将用户调整为指定分组；分组存在性与写入结果由服务层校验。
 */
class UserGroupSetTool implements McpToolInterface
{
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
        return 'crmeb_user_group_set';
    }

    /**
     * 交互声明：调整用户分组会变更用户数据，需用户确认后才真实执行
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
        return '把一个或多个用户设置到指定用户分组，等同管理后台「设置分组」，成功返回本次调整的分组与用户数。'
            . '必填参数：uids 用户ID数组（支持批量）；group_id 分组ID（大于 0，必须是已存在的用户分组，分组清单在管理后台「用户 → 用户分组」维护，'
            . '目前没有查询分组的 MCP 工具，ID 不确定时先向运营确认）。'
            . '本工具是覆盖式设置：执行后所选用户的分组统一变为 group_id，原分组被替换。'
            . '不能创建或删除分组，不能把用户移出分组（设为其他分组即是替换）；设置用户标签用 crmeb_user_label_set。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '分组设置';
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
            'required'   => ['uids', 'group_id'],
            'properties' => [
                'uids'     => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => '用户ID数组，支持批量设置'],
                'group_id' => ['type' => 'integer', 'minimum' => 1, 'description' => '目标分组ID，必须是已存在的用户分组'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（uids、group_id）
     * @return array 本次分组调整结果
     * @throws McpException 参数非法或服务层业务校验失败
     */
    public function execute(array $args): array
    {
        $uids = UserEditPayload::idArray($args['uids'] ?? [], 'uids');
        $uids = array_values(array_filter($uids, static fn(int $id) => $id > 0));
        if (!$uids) {
            throw new McpException('uids 必须是至少一个大于 0 的用户ID数组');
        }
        $groupId = (int)($args['group_id'] ?? 0);
        if ($groupId <= 0) {
            throw new McpException('group_id 必须是大于 0 的分组ID');
        }

        try {
            $this->services->saveSetGroup($uids, $groupId);
        } catch (AdminException $e) {
            // 「该分组不存在」「设置分组失败或无改动」等业务错误透传
            throw new McpException($e->getMessage());
        }

        return [
            'group_id' => $groupId,
            'uid_count' => count($uids),
            'uids'     => $uids,
            'message'  => '已把 ' . count($uids) . ' 个用户设置到分组 ' . $groupId,
        ];
    }
}
