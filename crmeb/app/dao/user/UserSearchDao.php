<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\dao\user;

use app\dao\BaseDao;
use app\model\user\UserSearch;

/**
 * 用户搜索
 * Class UserSearchDao
 * @package app\dao\user
 */
class UserSearchDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return UserSearch::class;
    }

    /**
     * 获取列表
     * @param array $where
     * @param string $order
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where = [], string $order = 'id desc', int $page = 0, int $limit = 0): array
    {
        return $this->search($where)->order($order)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->select()->toArray();
    }

    /**
     * * 获取全局|用户某个关键词搜素结果
     * @param int $uid
     * @param string $keyword 关键词
     * @param int $preTime 多长时间内认为结果集有效
     * @return array|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getKeywordResult(int $uid, string $keyword, int $preTime = 7200)
    {
        if (!$keyword) return [];
        $where = ['keyword' => $keyword];
        if ($uid) $where['uid'] = $uid;
        return $this->search($where)->when($uid && $preTime == 0, function ($query) {
                $query->where('is_del', 0);
            })->when($preTime > 0, function ($query) use ($preTime) {
                $query->where('add_time', '>', time() - $preTime);
            })->order('add_time desc,id desc')->find() ?? [];
    }
}
