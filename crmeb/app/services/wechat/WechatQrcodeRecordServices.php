<?php


namespace app\services\wechat;


use app\dao\wechat\WechatQrcodeRecordDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;

class WechatQrcodeRecordServices extends BaseServices
{
    /**
     * WechatQrcodeRecordServices constructor.
     * @param WechatQrcodeRecordDao $dao
     */
    public function __construct(WechatQrcodeRecordDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取用户列表
     * @param $qid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userList($qid)
    {
        [$page, $limit] = $this->getPageValue();
        $where['qid'] = $qid;
        $list = $this->dao->getList($where, $page, $limit, 1);
        $count = $this->dao->getDistinctCount($where, 'uid');
        return compact('list', 'count');
    }

    /**
     * 渠道码统计
     * @param $where
     * @param $time
     * @return mixed
     */
    public function qrcodeStatistic($where, $time)
    {
        $data['all_follow'] = $this->dao->count($where + ['is_follow' => 1]);
        $data['all_scan'] = $this->dao->count($where);
        $data['y_follow'] = $this->dao->count($where + ['is_follow' => 1, 'time' => 'yesterday']);
        $data['y_scan'] = $this->dao->count($where + ['time' => 'yesterday']);
        $data['trend'] = $this->getTrend($where['qid'], explode('-', $time));
        return $data;
    }

    /**
     * 余额趋势
     * @param $qid
     * @param $time
     * @return array
     */
    public function getTrend($qid, $time)
    {
        if (count($time) != 2) throw new AdminException(100100);
        $dayCount = (strtotime($time[1]) - strtotime($time[0])) / 86400 + 1;
        $data = [];
        if ($dayCount == 1) {
            $data = $this->trend($qid, $time, 0);
        } elseif ($dayCount > 1 && $dayCount <= 31) {
            $data = $this->trend($qid, $time, 1);
        } elseif ($dayCount > 31 && $dayCount <= 92) {
            $data = $this->trend($qid, $time, 3);
        } elseif ($dayCount > 92) {
            $data = $this->trend($qid, $time, 30);
        }
        return $data;
    }

    /**
     * 余额趋势
     * @param $qid
     * @param $time
     * @param $num
     * @param false $excel
     * @return array
     */
    public function trend($qid, $time, $num)
    {
        if ($num == 0) {
            $xAxis = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
            $timeType = '%H';
        } elseif ($num != 0) {
            $dt_start = strtotime($time[0]);
            $dt_end = strtotime($time[1]);
            while ($dt_start <= $dt_end) {
                if ($num == 30) {
                    $xAxis[] = date('Y-m', $dt_start);
                    $dt_start = strtotime("+1 month", $dt_start);
                    $timeType = '%Y-%m';
                } else {
                    $xAxis[] = date('m-d', $dt_start);
                    $dt_start = strtotime("+$num day", $dt_start);
                    $timeType = '%m-%d';
                }
            }
        }
        $time[1] = date("Y-m-d", strtotime("+1 day", strtotime($time[1])));
        $follow = array_column($this->dao->getRecordTrend($qid, $time, $timeType, 'add_time', 'count(uid)', 'yes'), 'num', 'days');
        $scan = array_column($this->dao->getRecordTrend($qid, $time, $timeType, 'add_time', 'count(uid)', 'no'), 'num', 'days');
        $data = $series = [];
        foreach ($xAxis as $item) {
            $data['新增关注'][] = isset($follow[$item]) ? floatval($follow[$item]) : 0;
            $data['新增参与'][] = isset($scan[$item]) ? floatval($scan[$item]) : 0;
        }
        foreach ($data as $key => $item) {
            $series[] = [
                'name' => $key,
                'data' => $item,
                'type' => 'line',
            ];
        }
        return compact('xAxis', 'series');
    }
}
