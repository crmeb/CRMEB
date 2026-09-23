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
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\SensitiveData;

/**
 * MCP 工具：用户列表
 *
 * 支持按昵称或手机号搜索。
 */
class UserListTool implements McpToolInterface
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
        return 'crmeb_user_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '查询用户列表，支持按昵称/手机号关键词筛选，返回 UID、昵称、余额、积分、等级、注册时间等字段；'
            . '手机号已脱敏（中间四位掩码），不含用户收货地址与订单/消费记录，'
            . '查询单个用户完整信息请用 crmeb_user_detail。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '用户列表';
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
                'page'    => ['type' => 'number', 'description' => '页码'],
                'limit'   => ['type' => 'number', 'description' => '每页数量'],
                'keyword' => ['type' => 'string', 'description' => '搜索关键词，匹配昵称/手机号'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（page/limit/keyword）
     * @return array 用户列表和总数
     */
    public function execute(array $args): array
    {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(100, max(1, (int)($args['limit'] ?? 10)));

        $where = [];
        // 关键词搜索：转义通配符防止注入
        if (!empty($args['keyword'])) {
            $keyword = addcslashes($args['keyword'], '%_');
            $where[] = ['nickname|phone', 'like', '%' . $keyword . '%'];
        }

        $list = $this->dao->search($where)
            ->field('uid,nickname,avatar,phone,now_money,integral,level,add_time')
            ->order('uid desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $count = $this->dao->search($where)->count();
        foreach ($list as &$item) {
            $item['phone'] = SensitiveData::phone((string)($item['phone'] ?? ''));
        }
        unset($item);
        return ['list' => $list, 'count' => $count];
    }
}
