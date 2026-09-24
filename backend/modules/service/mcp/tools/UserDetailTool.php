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

use app\dao\user\UserDao;
use modules\service\mcp\McpException;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\SensitiveData;

/**
 * MCP 工具：用户详情
 *
 * 按 UID 查询，仅返回必要字段。
 */
class UserDetailTool implements McpToolInterface
{
    /**
     * 用户 DAO
     * @var UserDao
     */
    private UserDao $dao;

    /**
     * 构造函数
     * @param UserDao $dao 用户 DAO
     */
    public function __construct(UserDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_user_detail';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '按用户ID（uid）查询单个用户详情，返回昵称、手机号（已脱敏）、余额、积分、等级、注册与最后活跃时间；'
            . '不含收货地址、订单与消费记录，需要用户清单时用 crmeb_user_list。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '用户详情';
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
            'properties' => [
                'uid' => ['type' => 'number', 'description' => '用户ID'],
            ],
            'required'   => ['uid'],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（uid）
     * @return array 用户详情（已过滤敏感字段）
     * @throws McpException 参数错误或用户不存在
     */
    public function execute(array $args): array
    {
        if (!isset($args['uid']) || !is_numeric($args['uid'])) {
            throw new McpException('参数错误：缺少 uid 或格式不正确');
        }
        $info = $this->dao->search([['uid', '=', (int)$args['uid']]])
            ->field('uid,nickname,avatar,phone,now_money,integral,level,add_time,last_time')->find();
        if (!$info) {
            throw new McpException('用户不存在');
        }
        $info = is_object($info) ? $info->toArray() : (array)$info;

        // 过滤敏感字段，只返回必要信息
        return [
            'uid'       => $info['uid'],
            'nickname'  => $info['nickname'] ?? '',
            'avatar'    => $info['avatar'] ?? '',
            'phone'     => SensitiveData::phone((string)($info['phone'] ?? '')),
            'now_money' => $info['now_money'] ?? 0,
            'integral'  => $info['integral'] ?? 0,
            'level'     => $info['level'] ?? 0,
            'add_time'  => $info['add_time'] ?? 0,
            'last_time' => $info['last_time'] ?? 0,
        ];
    }
}
