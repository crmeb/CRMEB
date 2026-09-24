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

use app\services\user\UserLabelRelationServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\McpException;

/**
 * MCP 工具：设置用户备注
 *
 * 与管理后台「编辑用户」同链路：读取用户当前值合并新备注后调用
 * UserServices::updateInfo 落库，只改 mark 一个字段，不影响其他资料。
 */
class UserMarkSetTool implements McpToolInterface
{
    /**
     * 用户服务
     * @var UserServices
     */
    private UserServices $services;

    /**
     * 用户标签关联服务（读取当前标签避免被清空）
     * @var UserLabelRelationServices
     */
    private UserLabelRelationServices $labelServices;

    /**
     * 构造函数
     * @param UserServices $services 用户服务
     * @param UserLabelRelationServices $labelServices 用户标签关联服务
     */
    public function __construct(UserServices $services, UserLabelRelationServices $labelServices)
    {
        $this->services = $services;
        $this->labelServices = $labelServices;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_user_mark_set';
    }

    /**
     * 交互声明：写备注会变更用户数据，需用户确认后才真实执行
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
        return '给单个用户写运营备注（mark 字段），等同管理后台「编辑用户」里的备注项；只修改备注，不影响昵称、手机号、分组、标签等其他资料，成功返回更新后的备注。'
            . '必填参数：uid 用户ID；mark 备注内容（255 字以内，传空字符串即清空备注）。'
            . '备注面向运营人员记录，用户端不可见；需要修改姓名、手机号等完整资料时用 crmeb_user_update。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '用户备注';
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
            'required'   => ['uid', 'mark'],
            'properties' => [
                'uid'  => ['type' => 'number', 'description' => '用户ID'],
                'mark' => ['type' => 'string', 'maxLength' => 255, 'description' => '备注内容，255 字以内，空字符串表示清空备注'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（uid、mark）
     * @return array 更新后的备注
     * @throws McpException 参数非法或服务层业务校验失败
     */
    public function execute(array $args): array
    {
        $uid = (int)($args['uid'] ?? 0);
        if ($uid <= 0) {
            throw new McpException('参数错误：缺少 uid 或格式不正确');
        }
        if (!array_key_exists('mark', $args) || !is_string($args['mark'])) {
            throw new McpException('参数错误：缺少 mark 或格式不正确');
        }
        $mark = trim($args['mark']);
        if (mb_strlen($mark) > 255) {
            throw new McpException('mark 备注不能超过 255 个字');
        }

        $data = UserEditPayload::build($this->services, $this->labelServices, $uid, ['mark' => $mark]);

        try {
            $this->services->updateInfo($uid, $data);
        } catch (AdminException $e) {
            throw new McpException($e->getMessage());
        }

        return [
            'uid'     => $uid,
            'mark'    => $mark,
            'message' => $mark === '' ? '用户备注已清空' : '用户备注已更新，可通过 crmeb_user_detail 查看',
        ];
    }
}
