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
 * MCP 工具：修改用户资料
 *
 * 与管理后台「编辑用户」同链路：先读用户当前值合并显式传入的修改项，
 * 再调用 UserServices::updateInfo 全量落库；只改传入字段，不传的字段
 * 保持原值，不会清空其他资料。服务层业务校验不因 AI 调用而豁免。
 */
class UserUpdateTool implements McpToolInterface
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
        return 'crmeb_user_update';
    }

    /**
     * 交互声明：修改用户资料会直接变更用户数据，需用户确认后才真实执行
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
        return '修改单个用户的基础资料，等同管理后台「编辑用户」；只修改显式传入的字段，未传字段保持原值，成功返回修改后的资料摘要。'
            . '必填参数：uid 用户ID。'
            . '可选字段：real_name 真实姓名（必填校验，不能为空、25 字以内）、phone 手机号（必填校验，11 位大陆手机号且全库唯一）、'
            . 'email 邮箱（可空，填了须合法）、card_id 身份证号（可空，填了须合法）、birthday 生日（YYYY-MM-DD）、'
            . 'addres 详细地址（255 字以内）、status 用户状态（1=开启、0=锁定）、group_id 用户分组 ID（0=未分组）、'
            . 'label_ids 用户标签 ID 数组（覆盖语义：传入后用户标签被替换为该数组；只想改资料不要传此参数）、'
            . 'mark 备注（255 字以内，仅改备注建议用 crmeb_user_mark_set）。'
            . '至少传入一个可选字段。不能通过本工具修改余额、积分、会员等级与密码；改分组用 crmeb_user_group_set，'
            . '改标签用 crmeb_user_label_set，赠送等级用 crmeb_user_level_give。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '资料修改';
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
            'required'   => ['uid'],
            'properties' => [
                'uid'       => ['type' => 'number', 'description' => '用户ID'],
                'real_name' => ['type' => 'string', 'maxLength' => 25, 'description' => '真实姓名，不能为空'],
                'phone'     => ['type' => 'string', 'description' => '手机号，11 位大陆手机号，全库唯一'],
                'email'     => ['type' => 'string', 'description' => '邮箱，可空'],
                'card_id'   => ['type' => 'string', 'description' => '身份证号码，可空'],
                'birthday'  => ['type' => 'string', 'description' => '生日，YYYY-MM-DD'],
                'addres'    => ['type' => 'string', 'maxLength' => 255, 'description' => '详细地址'],
                'status'    => ['type' => 'integer', 'enum' => [0, 1], 'description' => '用户状态：1=开启，0=锁定'],
                'group_id'  => ['type' => 'integer', 'minimum' => 0, 'description' => '用户分组 ID，0=未分组'],
                'label_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => '用户标签 ID 数组，覆盖语义，传入后整体替换用户标签'],
                'mark'      => ['type' => 'string', 'maxLength' => 255, 'description' => '备注，255 字以内'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（uid + 至少一个修改字段）
     * @return array 修改后的资料摘要
     * @throws McpException 参数非法或服务层业务校验失败
     */
    public function execute(array $args): array
    {
        $uid = (int)($args['uid'] ?? 0);
        if ($uid <= 0) {
            throw new McpException('参数错误：缺少 uid 或格式不正确');
        }

        $editable = ['real_name', 'phone', 'email', 'card_id', 'birthday', 'addres', 'mark', 'status', 'group_id', 'label_ids'];
        $overrides = array_intersect_key($args, array_flip($editable));
        if (!$overrides) {
            throw new McpException('未提供任何要修改的字段，可选字段：real_name、phone、email、card_id、birthday、addres、status、group_id、label_ids、mark');
        }

        $data = UserEditPayload::build($this->services, $this->labelServices, $uid, $overrides);

        try {
            $this->services->updateInfo($uid, $data);
        } catch (AdminException $e) {
            // 服务层业务校验失败：转 McpException 让消息透传给 AI 客户端
            throw new McpException($e->getMessage());
        }

        return [
            'uid'       => $uid,
            'real_name' => $data['real_name'],
            'phone'     => $data['phone'],
            'status'    => $data['status'],
            'group_id'  => $data['group_id'],
            'label_ids' => $data['label_id'],
            'mark'      => $data['mark'],
            'message'   => '用户资料修改成功，可通过 crmeb_user_detail 查看最新资料',
        ];
    }
}
