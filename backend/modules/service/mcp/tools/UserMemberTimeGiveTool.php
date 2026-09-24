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
 * MCP 工具：赠送付费会员时长
 *
 * 与管理后台「赠送付费会员时长」同链路：调用 UserServices::saveGiveLevelTime
 * 给用户增加付费会员有效期（或设为永久）；服务层会写入会员赠送订单并更新
 * 会员到期时间（overdue_time）与永久会员标记（is_ever_level）。
 */
class UserMemberTimeGiveTool implements McpToolInterface
{
    /**
     * 天数取值：永久会员
     */
    private const DAYS_FOREVER = -1;

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
        return 'crmeb_user_member_time_give';
    }

    /**
     * 交互声明：赠送付费会员会变更用户权益数据，需用户确认后才真实执行
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
        return '给单个用户赠送付费会员时长（延长会员到期时间或设为永久会员），等同管理后台「赠送付费会员时长」，成功返回赠送后的会员到期时间。'
            . '必填参数：uid 用户ID；days 赠送天数：-1=设为永久会员，正整数=在当前到期时间基础上增加天数（未开通过付费会员的用户从当前时间起算）。'
            . 'days=0 或小于 -1 会被拒绝；已是永久会员时无需赠送，工具会直接返回提示。'
            . '赠送会写入一条后台赠送的会员订单记录。注意与会员等级是两套体系：调整会员等级用 crmeb_user_level_give；'
            . '本工具不能扣减时长（不支持负数天数）、不能关闭付费会员资格。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '赠送会员时长';
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
            'required'   => ['uid', 'days'],
            'properties' => [
                'uid'  => ['type' => 'number', 'description' => '用户ID'],
                'days' => ['type' => 'integer', 'description' => '赠送天数：-1=永久会员，正整数=增加的天数'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（uid、days）
     * @return array 赠送后的会员状态
     * @throws McpException 参数非法或服务层业务校验失败
     */
    public function execute(array $args): array
    {
        $uid = (int)($args['uid'] ?? 0);
        if ($uid <= 0) {
            throw new McpException('参数错误：缺少 uid 或格式不正确');
        }
        $days = $args['days'] ?? null;
        if (!is_int($days) && (!is_string($days) || !ctype_digit($days) && (string)$days !== '-1')) {
            throw new McpException('days 必须是整数（-1 表示永久，正整数为天数）');
        }
        $days = (int)$days;
        if ($days === 0 || $days < self::DAYS_FOREVER) {
            throw new McpException('days 只能是 -1（永久）或正整数天数，不能为 0 或小于 -1');
        }

        // 服务层对永久会员直接跳过（返回 true 无动作），此处提前识别给出明确结果
        $user = $this->services->getUserInfo($uid, 'uid,is_ever_level,overdue_time,is_money_level');
        if (!$user) {
            throw new McpException('用户不存在');
        }
        if ((int)$user['is_ever_level'] === 1) {
            return [
                'uid'          => $uid,
                'days'         => $days,
                'is_ever_level' => 1,
                'overdue_time' => 0,
                'message'      => '该用户已是永久付费会员，无需赠送',
            ];
        }

        try {
            $this->services->saveGiveLevelTime($uid, $days);
        } catch (AdminException $e) {
            // 「赠送天数不能为0」等业务错误透传
            throw new McpException($e->getMessage());
        }

        // 重新读取会员状态，返回赠送后的到期时间
        $user = $this->services->getUserInfo($uid, 'uid,is_ever_level,overdue_time');
        $overdueTime = (int)($user['overdue_time'] ?? 0);

        return [
            'uid'           => $uid,
            'days'          => $days,
            'is_ever_level' => (int)($user['is_ever_level'] ?? 0),
            'overdue_time'  => $overdueTime,
            'overdue_date'  => $overdueTime > 0 ? date('Y-m-d H:i:s', $overdueTime) : '',
            'message'       => $days === self::DAYS_FOREVER
                ? '已把该用户设为永久付费会员'
                : '已赠送 ' . $days . ' 天付费会员，到期时间 ' . ($overdueTime > 0 ? date('Y-m-d H:i:s', $overdueTime) : '未知'),
        ];
    }
}
