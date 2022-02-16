<?php


namespace app\dao\wechat;


use app\dao\BaseDao;
use app\model\wechat\WechatQrcodeRecord;

class WechatQrcodeRecordDao extends BaseDao
{
    /**
     * @return string
     */
    protected function setModel(): string
    {
        return WechatQrcodeRecord::class;
    }

    /**
     * 获取列表
     * @param $where
     * @param int $page
     * @param int $limit
     * @param int $is_distinct
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList($where, $page = 0, $limit = 0, $is_distinct = 0)
    {
        return $this->search($where)->with(['user'])->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->when($is_distinct, function ($query) {
            $query->distinct(true)->field('uid');
        })->order('id desc')->select()->toArray();
    }

    /**
     * 扫码趋势
     * @param $qid
     * @param $time
     * @param $timeType
     * @param $field
     * @param $str
     * @return mixed
     */
    public function getRecordTrend($qid, $time, $timeType, $field, $str, $orderStatus = '')
    {
        return $this->getModel()->where(function ($query) use ($field, $orderStatus) {
            if ($orderStatus == 'yes') {
                $query->where('is_follow', 1);
            }
        })->where(function ($query) use ($time, $field) {
            if ($time[0] == $time[1]) {
                $query->whereDay($field, $time[0]);
            } else {
                $query->whereTime($field, 'between', $time);
            }
        })->where('qid', $qid)->field("FROM_UNIXTIME($field,'$timeType') as days,$str as num")->group('days')->select()->toArray();
    }
}