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
use modules\service\mcp\McpException;

/**
 * 用户编辑数据组装辅助（MCP 内部共享，非工具类）
 *
 * UserServices::updateInfo 走基本信息分支时按全字段覆盖写库，并以 label_id
 * 全量重设用户标签；本类负责先读用户当前值，合并工具显式传入的修改项后
 * 构造 updateInfo 所需的完整 $data，保证「只改一项」不会清空其他资料，
 * 与管理后台编辑表单（读出当前值 → 改一项 → 提交）链路一致。
 *
 * 文件名不以 Tool.php 结尾，不会被注册中心自动发现，仅供用户编辑类工具复用。
 */
final class UserEditPayload
{
    /**
     * 构造 UserServices::updateInfo 所需的全量编辑数据
     *
     * @param UserServices $services 用户服务（用于读取用户与手机号唯一性校验）
     * @param UserLabelRelationServices $labelServices 用户标签关联服务（读取当前标签）
     * @param int $uid 用户 ID
     * @param array $overrides 显式要修改的字段（仅支持 real_name/phone/email/card_id/birthday/addres/mark/status/group_id/label_ids）
     * @return array updateInfo 完整入参
     * @throws McpException 用户不存在或字段校验失败
     */
    public static function build(UserServices $services, UserLabelRelationServices $labelServices, int $uid, array $overrides): array
    {
        $user = $services->getUserInfo($uid);
        if (!$user) {
            throw new McpException('用户不存在');
        }
        $user = is_object($user) ? $user->toArray() : (array)$user;

        // 真实姓名（对齐后台必填与 eb_user.real_name varchar(25)）
        $realName = trim((string)($overrides['real_name'] ?? $user['real_name'] ?? ''));
        if ($realName === '') {
            throw new McpException('real_name 真实姓名不能为空');
        }
        if (mb_strlen($realName) > 25) {
            throw new McpException('real_name 真实姓名不能超过 25 个字');
        }

        // 手机号：必填、格式与全库唯一（对齐 UserController::update 校验）
        $phone = trim((string)($overrides['phone'] ?? $user['phone'] ?? ''));
        if ($phone === '') {
            throw new McpException('phone 手机号不能为空');
        }
        if (!preg_match('/^1[3456789]\d{9}$/', $phone)) {
            throw new McpException('phone 手机号格式错误');
        }
        if ($phone !== (string)($user['phone'] ?? '')
            && $services->count(['phone' => $phone, 'is_del' => 0, 'not_uid' => $uid])) {
            throw new McpException('phone 手机号已被其他用户使用');
        }

        // 邮箱：可空，填了必须合法
        $email = trim((string)($overrides['email'] ?? $user['email'] ?? ''));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new McpException('email 邮箱格式错误');
        }

        // 身份证：可空，填了必须合法（全局辅助函数校验）
        $cardId = trim((string)($overrides['card_id'] ?? $user['card_id'] ?? ''));
        if ($cardId !== '' && !check_card($cardId)) {
            throw new McpException('card_id 身份证号码不正确');
        }

        // 生日：传日期字符串（YYYY-MM-DD）；未传时回填当前值（时间戳 0 回填纪元日，strtotime 后仍近似为空）
        if (array_key_exists('birthday', $overrides)) {
            $birthday = trim((string)$overrides['birthday']);
            if ($birthday !== '' && strtotime($birthday) === false) {
                throw new McpException('birthday 必须是有效日期，格式 YYYY-MM-DD');
            }
        } else {
            $birthdayTimestamp = (int)($user['birthday'] ?? 0);
            $birthday = $birthdayTimestamp > 0 ? date('Y-m-d H:i:s', $birthdayTimestamp) : '1970-01-01 08:00:00';
        }

        // 状态：1=开启，0=锁定
        $status = (int)($overrides['status'] ?? $user['status'] ?? 1);
        if (!in_array($status, [0, 1], true)) {
            throw new McpException('status 只能为 0（锁定）或 1（开启）');
        }

        // 备注（eb_user.mark varchar(255)）
        $mark = trim((string)($overrides['mark'] ?? $user['mark'] ?? ''));
        if (mb_strlen($mark) > 255) {
            throw new McpException('mark 备注不能超过 255 个字');
        }

        // 详细地址
        $addres = trim((string)($overrides['addres'] ?? $user['addres'] ?? ''));
        if (mb_strlen($addres) > 255) {
            throw new McpException('addres 详细地址不能超过 255 个字');
        }

        // 分组：0 表示未分组
        $groupId = max(0, (int)($overrides['group_id'] ?? $user['group_id'] ?? 0));

        // 标签：未显式传入时回填当前标签，避免 updateInfo 以空数组全量清空
        if (array_key_exists('label_ids', $overrides)) {
            $labelIds = self::idArray($overrides['label_ids'], 'label_ids');
        } else {
            $labelIds = array_map('intval', (array)$labelServices->getUserLabels($uid));
        }

        // updateInfo 基本信息分支要求的全量结构；等级/余额/积分不在编辑范围，
        // 保持当前值避免触发分支逻辑，密码固定空串即不修改
        return [
            'money_status'       => 0,
            'money'              => '0',
            'integration_status' => 0,
            'integration'        => '0',
            'is_other'           => false,
            'label_id'           => $labelIds,
            'pwd'                => '',
            'status'             => $status,
            'real_name'          => $realName,
            'card_id'            => $cardId,
            'birthday'           => $birthday,
            'mark'               => $mark,
            'level'              => (int)($user['level'] ?? 0),
            'phone'              => $phone,
            'addres'             => $addres,
            'email'              => $email,
            'group_id'           => $groupId,
        ];
    }

    /**
     * 校验并规范化正整数 ID 数组（去重）
     *
     * @param mixed $value 原始值
     * @param string $name 参数名称（报错用）
     * @return int[] ID 列表（允许为空数组）
     * @throws McpException 不是合法 ID 数组
     */
    public static function idArray(mixed $value, string $name): array
    {
        if (is_int($value) || (is_string($value) && $value !== '')) {
            $value = [$value];
        }
        if (!is_array($value)) {
            throw new McpException($name . ' 必须是 ID 数组');
        }
        $ids = [];
        foreach ($value as $item) {
            if (!is_int($item) && (!is_string($item) || !ctype_digit($item))) {
                throw new McpException($name . ' 数组内必须全部是整数 ID');
            }
            $ids[] = (int)$item;
        }
        return array_values(array_unique($ids));
    }
}
