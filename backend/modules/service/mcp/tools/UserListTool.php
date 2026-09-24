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
        return '查询用户列表，支持按昵称/手机号关键词筛选，支持按用户标签筛选（label_id 传标签 ID 数组，满足任一标签即命中，'
            . '标签 ID 可通过 crmeb_user_label_list 查询），返回 UID、昵称、余额、积分、等级、注册时间等字段；'
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
                'page'    => ['type' => 'number', 'description' => '页码'],
                'limit'   => ['type' => 'number', 'description' => '每页数量'],
                'keyword' => ['type' => 'string', 'description' => '搜索关键词，匹配昵称/手机号'],
                'label_id' => [
                    'type'        => 'array',
                    'items'       => ['type' => 'integer'],
                    'description' => '按用户标签筛选，标签 ID 数组（满足任一标签即命中），标签 ID 可通过 crmeb_user_label_list 查询',
                ],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（page/limit/keyword/label_id）
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

        // 标签筛选：兼容单个 ID 或 ID 数组，空值跳过
        $labelIds = [];
        if (!empty($args['label_id'])) {
            $labelIds = is_array($args['label_id']) ? $args['label_id'] : [$args['label_id']];
            $labelIds = array_values(array_filter(array_map('intval', $labelIds)));
        }

        $list = $this->searchQuery($where, $labelIds)
            ->field('uid,nickname,avatar,phone,now_money,integral,level,add_time')
            ->order('uid desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $count = $this->searchQuery($where, $labelIds)->count();
        foreach ($list as &$item) {
            $item['phone'] = SensitiveData::phone((string)($item['phone'] ?? ''));
        }
        unset($item);
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 构建查询：基础条件 + 标签子查询（用户拥有任一所选标签即命中）
     *
     * @param array $where 基础查询条件
     * @param array $labelIds 标签 ID 数组
     * @return \think\db\Query
     */
    private function searchQuery(array $where, array $labelIds)
    {
        $query = $this->dao->search($where);
        if ($labelIds) {
            $query->whereIn('uid', function ($subQuery) use ($labelIds) {
                $subQuery->name('user_label_relation')->whereIn('label_id', $labelIds)->field('uid')->select();
            });
        }
        return $query;
    }
}
