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

namespace app\dao\service;


use app\dao\BaseDao;
use app\model\service\StoreServiceRecord;

/**
 * Class StoreServiceRecordDao
 * @package app\dao\service
 */
class StoreServiceRecordDao extends BaseDao
{

    /**
     * StoreServiceRecordDao constructor.
     */
    public function __construct()
    {
        $this->deleteWeekRecord();
    }

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreServiceRecord::class;
    }

    /**
     * 删除上周游客记录
     */
    protected function deleteWeekRecord()
    {
        $this->search(['time' => 'last week', 'timeKey' => 'update_time', 'is_tourist' => 1])->delete();
    }

    /**
     *
     * @param array $where
     * @param array $data
     * @return \crmeb\basic\BaseModel
     */
    public function updateOnline(array $where, array $data)
    {
        return $this->getModel()->whereNotIn('to_uid', $where['notUid'])->update($data);
    }

    /**
     * 获取客服聊天用户列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @param array $with
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getServiceList(array $where, int $page, int $limit, array $with = [])
    {
        return $this->search($where)->page($page, $limit)->when(count($with), function ($query) use ($with) {
            $query->with($with);
        })->order('update_time desc')->select()->toArray();
    }

    /**
     * 查询最近和用户聊天的uid用户
     * @param array $where
     * @param string $key
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLatelyMsgUid(array $where, string $key)
    {
        return $this->search($where)->order('update_time DESC')->value($key);
    }
}
