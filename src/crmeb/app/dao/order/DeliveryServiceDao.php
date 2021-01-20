<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\dao\order;

use app\dao\BaseDao;
use app\model\order\DeliveryService;

/**配送dao
 * Class DeliveryServiceDao
 * @package app\dao\service
 */
class DeliveryServiceDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return DeliveryService::class;
    }

    /**
     * 获取配送员列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getServiceList(array $where, int $page, int $limit)
    {
        return $this->search($where)->with('user')->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->when(isset($where['noId']), function ($query) use ($where) {
            $query->where('id', '<>', $where['noId']);
        })->order('id DESC')->field('id,uid,avatar,nickname as wx_name,status,add_time,phone')->select()->toArray();
    }

    /**获取所有配送员列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(int $page, int $limit)
    {
        $list = $this->getModel()->where(['status' => 1])->with('user')->order('id DESC')->limit($page, $limit)->field('id,uid,avatar,nickname as wx_name,status,add_time,phone')->select()->toArray();
        $count = $this->getModel()->where(['status' => 1])->count();
        return [$list, $count];
    }


}
