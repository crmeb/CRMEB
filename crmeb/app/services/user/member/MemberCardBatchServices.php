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

namespace app\services\user\member;


use app\dao\user\MemberCardBatchDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use think\App;

class MemberCardBatchServices extends BaseServices
{
    /**
     * 初始化，获得dao层句柄
     * MemberCardServices constructor.
     * @param MemberCardBatchDao $memberCardDao
     */
    public function __construct(MemberCardBatchDao $memberCardBatchDao)
    {
        $this->dao = $memberCardBatchDao;
    }

    /**
     * 获取会员卡批次列表
     * @param array $where
     */
    public function getList(array $where = [])
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);

        if ($list) {
            foreach($list as &$v){
                $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                //$v['qrcode'] = json_decode($v['qrcode'], true);
            }
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * @param int $id
     * @param array $data
     */
    public function save(int $id, array $data)
    {
        if (!$data['title']) throw new AdminException(400614);
        if (!$data['total_num']) throw new AdminException(400615);
        if (!is_numeric($data['total_num']) || $data['total_num'] < 0) throw new AdminException(400616);
        if ($data['total_num'] > 6000) throw new AdminException(400617);
        if (!$data['use_day'] || !is_numeric($data['use_day'])) throw new AdminException(400618);
        if ($data['use_day'] < 0) throw new AdminException(400619);
        /**
         * 具体时间段试用，业务需要打开即可
         */
/*        $use_start_time = strtotime($data['use_start_time']);
        $use_end_time = strtotime($data['use_end_time']);
        if (!$use_start_time) {
            $use_start_time = strtotime(date('Y-m-d 00:00:00', strtotime('+1 day')));
        }else{
            $use_start_time = strtotime($data['use_start_time']);
        }
        if (!$use_end_time) {
            $use_end_time = strtotime(date('Y-m-d 23:59:59', strtotime('+1 day')));
        }else{
            $use_end_time = strtotime($data['use_end_time']);
        }
        if ($use_end_time < time()) throw new AdminException("体验结束时间不能小于当天");
        if ($use_end_time < $use_start_time) throw new AdminException("体验结束时间不能小于体验开始时间");
        $data['use_start_time'] = $use_start_time;
        $data['use_end_time'] = $use_end_time;*/
        $data['use_day'] = abs(ceil($data['use_day']));
        $data['total_num'] = abs(ceil($data['total_num']));
        $data['add_time'] = time();
        $this->transaction(function () use ($id, $data) {
            if ($id) {
                unset($data['total_num']);
                $data['update_time'] = time();
                return $this->dao->update($id, $data);
                //return ['status' => 1, "msg" => "编辑批次卡成功"];
            } else {
                /** @var MemberCardServices $memberCardService */
                $memberCardService = app()->make(MemberCardServices::class);
                $res = $this->dao->save($data);
                $add_card['card_batch_id'] = $res->id;
                $add_card['total_num'] = $data['total_num'];
                return $memberCardService->addCard($add_card);
                // return ['status' => 2, "msg" => "生成批次卡成功"];
            }
        });
    }

    /**
     * 列表操作
     * @param int $id
     * @param array $data
     */
    public function setValue(int $id, array $data)
    {
        if (!is_numeric($id) || !$id) throw new AdminException(100100);
        if (!isset($data['field']) || !isset($data['value']) || !$data['field']) throw new AdminException(100100);
        /**  */
        $this->dao->update($id, [$data['field'] => $data['value']]);
    }


    /**
     * 获取单条卡批次资源
     * @param array $uid
     * @param string $field
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOne(int $bid, $field = '*')
    {
        if (is_string($field)) $field = explode(',', $field);
        return $this->dao->get($bid, $field);
    }

    /**
     * 批次卡数量统计
     * @param int $id
     * @param string $field
     * @param int $inc
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function useCardSetInc(int $id, string $field, int $inc = 1)
    {
        return $this->dao->bcInc($id, $field, $inc);
    }

}
