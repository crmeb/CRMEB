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
 * MCP 工具：赠送会员等级
 *
 * 与管理后台「赠送等级」同链路：调用 UserServices::saveGiveLevel 给用户
 * 赠送系统会员等级配置中的一个等级；等级存在性、重复赠送由服务层校验，
 * 赠送成功后用户的 level 字段同步为该等级 ID，原等级作废。
 */
class UserLevelGiveTool implements McpToolInterface
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
        return 'crmeb_user_level_give';
    }

    /**
     * 交互声明：赠送会员等级会变更用户权益数据，需用户确认后才真实执行
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
        return '给单个用户赠送系统会员等级，等同管理后台「赠送等级」，成功返回赠送的等级信息。'
            . '必填参数：uid 用户ID；level_id 等级ID（系统会员等级配置「会员 → 等级设置」的主键 ID，'
            . '不是等级序数；目前没有查询等级配置的 MCP 工具，ID 不确定时先向运营确认）。'
            . '行为口径：赠送后用户原会员等级记录被作废并替换为新等级，用户 level 字段同步为 level_id，'
            . '等级附带经验差异会自动增减；用户已拥有该等级且未失效时赠送失败；只能赠等级，不能清除等级或修改经验。'
            . '注意与付费会员是两套体系：延长付费会员到期时间用 crmeb_user_member_time_give。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '赠送等级';
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
            'required'   => ['uid', 'level_id'],
            'properties' => [
                'uid'      => ['type' => 'number', 'description' => '用户ID'],
                'level_id' => ['type' => 'integer', 'minimum' => 1, 'description' => '系统会员等级配置ID（会员等级设置列表的主键，非等级序数）'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（uid、level_id）
     * @return array 赠送结果
     * @throws McpException 参数非法或服务层业务校验失败
     */
    public function execute(array $args): array
    {
        $uid = (int)($args['uid'] ?? 0);
        if ($uid <= 0) {
            throw new McpException('参数错误：缺少 uid 或格式不正确');
        }
        $levelId = (int)($args['level_id'] ?? 0);
        if ($levelId <= 0) {
            throw new McpException('level_id 必须是大于 0 的等级ID');
        }

        try {
            $this->services->saveGiveLevel($uid, $levelId);
        } catch (AdminException $e) {
            // 「用户不存在」「等级不存在或被删除」「已有该等级」等业务错误透传
            throw new McpException($e->getMessage());
        }

        return [
            'uid'      => $uid,
            'level_id' => $levelId,
            'message'  => '会员等级赠送成功，用户当前等级已设置为 ' . $levelId . '，可通过 crmeb_user_detail 查看',
        ];
    }
}
