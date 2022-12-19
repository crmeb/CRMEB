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


use app\dao\user\MemberCardDao;
use app\services\BaseServices;
use app\services\order\OtherOrderServices;
use app\services\order\StoreOrderCreateServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\services\SystemConfigService;

class MemberCardServices extends BaseServices
{
    /** 初始化，获得dao层句柄
     * MemberCardServices constructor.
     * @param MemberCardDao $memberCardDao
     */
    public static $_memberTypePrefix = ['month', 'quarter', 'year', 'ever', 'free', 'owner'];

    public function __construct(MemberCardDao $memberCardDao)
    {
        $this->dao = $memberCardDao;
    }

    public function getSearchList(array $where = [])
    {
        /** @var  UserServices $userService */
        $userService = app()->make(UserServices::class);
        [$page, $limit] = $this->getPageValue();
        $where['batch_card_id'] = $where['card_batch_id'];
        if ($where['is_use'] != "") {
            if ($where['is_use'] == 0) {
                $where['use_time'] = 0;
            } else {
                $where['use_time'] = 1;
            }
        }
        unset($where['is_use']);
        $list = $this->dao->getSearchList($where, $page, $limit);
        if ($list) {
            foreach ($list as $k => $v) {
                if ($v['use_uid']) {
                    $user_info = $userService->getUserInfo($v['use_uid'], ['nickname', 'phone', 'real_name']);
                    $list[$k]['username'] = $user_info['real_name'] ? $user_info['real_name'] : $user_info['nickname'];
                    $list[$k]['phone'] = $user_info ? $user_info['phone'] : "";
                }
                $list[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                $list[$k]['use_time'] = $v['use_time'] != 0 ? date('Y-m-d H:i:s', $v['use_time']) : "未使用";
            }
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');

    }

    /** 生成免费会员卡
     * @param array $data
     */
    public function addCard(array $data)
    {
        if (!isset($data['card_batch_id']) || !$data['card_batch_id'] || $data['card_batch_id'] == 0 || !isset($data['total_num']) || !$data['total_num'] || $data['total_num'] == 0) {
            throw new AdminException(100100);
        }
        try {
            if (!isset($data['total_num'])) throw new AdminException(100100);
            $num = $data['total_num'];
            unset($data['total_num']);
            $res = [];
            for ($i = 0; $i < $num; $i++) {
                $data['card_number'] = $this->makeRandomNumber("CR", $data['card_batch_id']);
                $data['card_password'] = $this->makeRandomNumber();
                $data['status'] = 1;
                $data['add_time'] = time();
                $res[] = $data;
            }
            //数据切片批量插入，提高性能。
            $chunk_inster_card = array_chunk($res, 100, true);
            foreach ($chunk_inster_card as $v) {
                $this->dao->saveAll($v);
            }
            return true;
        } catch (\Exception $exception) {
            throw new AdminException(400620);
        }
    }

    /**获取制卡卡号随机数
     * @param bool $prefix
     * @param bool $random
     * @return string
     */
    public function makeRandomNumber($prefix = false, $random = false)
    {
        if (!$prefix) {
            $prefix = "";
        }
        if (!$random || !is_numeric($random)) {
            $one_random = mt_rand(11111, 99999);
        } else {
            $one_random = sprintf("%05d", $random);
        }
        $date_random = date('ymd', time());
        $random_tmp = strlen($one_random);
        $two_randow = str_pad(mt_rand(1, 99999), $random_tmp, '0', STR_PAD_LEFT);
        if (!$random) {
            return $two_randow;
        } else {
            return $prefix . $one_random . $date_random . $two_randow;
        }
    }

    /** 领取会员卡
     * @param array $data
     * @param int $uid
     */
    public function drawMemberCard(array $data, int $uid)
    {
        if (!$uid || !$data) throw new ApiException(100100);
        $isOpenMember = $this->isOpenMemberCard();
        if (!$isOpenMember) throw new ApiException(400621);
        if (!isset($data['member_card_code']) || !$data['member_card_code']) throw new ApiException(400622);
        if (!isset($data['member_card_code']) || !$data['member_card_pwd']) throw new ApiException(400623);
        $card_info = $this->dao->getOneByWhere(['card_number' => trim($data['member_card_code'])]);
        if (!$card_info) throw new ApiException(400624);
        /** @var MemberCardBatchServices $memberBatchServices */
        $memberBatchServices = app()->make(MemberCardBatchServices::class);
        $batch_info = $memberBatchServices->getOne($card_info['card_batch_id']);
        if (!$batch_info) throw new ApiException(400625);
        if ($batch_info->status != 1) throw new ApiException(400625);
        if ($card_info['status'] == 0) throw new ApiException(400625);
        if ($card_info['card_password'] != trim($data['member_card_pwd'])) throw new ApiException(400626);
        if ($card_info['use_uid'] && $card_info['use_time']) throw new ApiException(400627);
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user_info = $userServices->getUserInfo($uid);
        if (!$user_info) throw new ApiException(400214);
        if ($user_info->is_money_level > 0 && $user_info->is_ever_level == 1) throw new ApiException(400628);


        /**
         * 批次卡具体使用期限，业务需要打开即可，勿删。
         */
        if ($card_info->status != 1) throw new ApiException(400625);
        $this->transaction(function () use ($card_info, $user_info, $batch_info, $memberBatchServices, $userServices, $data) {
            $res1 = $this->dao->update($card_info->id, ['use_uid' => $user_info->uid, 'use_time' => time(), 'update_time' => time()], 'id');
            if ($res1) {
                $res2 = $memberBatchServices->useCardSetInc($batch_info->id, 'use_num', 1);
                $overdue_time = 0;
                switch ($user_info->is_money_level) {
                    case 1:
                    case 2:
                    case 3:
                        $overdue_time = bcadd(bcmul($batch_info->use_day, 86400, 0), $user_info->overdue_time, 0);
                        $overdue_time = mktime(date('H'), date('i'), date('s'), date('m', $overdue_time), date('d', $overdue_time), date('Y', $overdue_time));
                        break;
                    case 0:
                        $overdue_time = bcadd(bcmul($batch_info->use_day, 86400, 0), time(), 0);
                        break;
                }
                $channel_type = $data['from'];
                /** @var OtherOrderServices $OtherOrderServices */
                $OtherOrderServices = app()->make(OtherOrderServices::class);
                $storeOrderCreateService = app()->make(StoreOrderCreateServices::class);
                $record_data['uid'] = $user_info->uid;
                $record_data['member_code'] = $card_info->card_number;
                $record_data['use_day'] = $batch_info->use_day;
                $record_data['overdue_time'] = $overdue_time;
                $record_data['order_id'] = $storeOrderCreateService->getNewOrderId();
                $record_data['channel_type'] = $channel_type;
                $record_data['member_type'] = "free";
                $record_data['vip_day'] = $batch_info->use_day;
                $record_data['type'] = 2;
                $record_data['paid'] = 1;
                $record_data['pay_time'] = time();
                $res3 = $OtherOrderServices->addOtherOrderData($record_data);
                //if ($res3) $res4 = $userServices->update($user_info->uid, ['level' => 1, 'overdue_time' => $overdue_time, 'is_permanent' => 0], 'uid');
                /** @var UserServices $userServices */
                $userServices = app()->make(UserServices::class);
                $res4 = $userServices->setMemberOverdueTime($batch_info->use_day, $user_info->uid, 2, $record_data['member_type']);
                $res5 = $res1 && $res2 && $res3 && $res4;
                return $res5;
            }
        });


    }

    /**  验证是否存在此类型会员卡
     * @param string $member_type
     * @return bool
     */
    public function checkmemberType(string $member_type)
    {
        $member_type_arr = $this->getMemberTypeInfo();
        if (!array_key_exists($member_type, $member_type_arr)) throw new ApiException(400629);
        return true;
    }

    /** 获取会员权益和说明配置
     * @return array
     */
    public function getMemberRightsInfo()
    {
        /** @var MemberRightServices $memberRightService */
        $memberRightService = app()->make(MemberRightServices::class);
        $memberRight = $memberRightService->getSearchList(['status' => 1]);
        if ($memberRight['list']) {
            foreach ($memberRight['list'] as $k => &$v) {
                $v['title'] = $v['show_title'];
                $v['pic'] = $v['image'];
                $v['right'] = $v['explain'];
//                $v['number'] = $v['number'];
            }
        }

        return ['member_right' => $memberRight['list']];
    }

    /**获取会员卡配置
     * @return array
     */
    public function getMemberTypeInfo()
    {
        /** @var SystemConfigService $systemConfigService */
        $systemConfigService = app()->make(SystemConfigService::class);
        $data = [];
        foreach (self::$_memberTypePrefix as $v) {
            $data[$v] = $systemConfigService::more([$v . '_title', $v . '_vip_day', $v . '_pre_price', $v . '_price']);
        }
        return $data;
    }

    /**会员卡数据处理
     * @return array
     */
    public function DoMemberType()
    {
        $data = array();
        /** @var MemberShipServices $memberShipService */
        $memberShipService = app()->make(MemberShipServices::class);
        $list = $memberShipService->getApiList(['is_del' => 0]);
        foreach ($list as $v) {
            $data[] = [
                'mc_id' => $v['id'],
                'title' => $v['title'],
                'type' => $v['type'],
                'vip_day' => $v['vip_day'],
                'pre_price' => $v['pre_price'],
                'price' => $v['price'],
            ];
        }
        return $data;
    }

    /**会员类型数据
     * @return bool
     */
    public function getMemberTypeValue()
    {
        $member_type = $this->DoMemberType();
        if (!$member_type) return false;
        $new_member_data = [];
        foreach ($member_type as $k => $v) {
            $new_member_data[$v['mc_id']] = $v;
        }
        return $new_member_data;
    }

    /**导出会员卡
     * @param $where
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getExportData($where)
    {
        $data = $this->dao->getSearchList($where);
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        /** @var MemberCardBatchServices $batchService */
        $batchService = app()->make(MemberCardBatchServices::class);
        foreach ($data as $k => $v) {
            $data[$k]['use_time'] = $v['use_time'] != 0 ? date('Y-m-d H:i:s', $v['use_time']) : "";
            $data[$k]['user_name'] = '';
            $data[$k]['user_phone'] = '';
            if ($v['use_uid'] != 0) {
                $userInfo = $userService->get($v['use_uid']);
                $data[$k]['user_name'] = $userInfo['nickname'] ?: $userInfo['account'];
                $data[$k]['user_phone'] = $userInfo['phone'];
            }
        }
        $batchInfo = $batchService->getOne($where['batch_card_id']);
        $dataArray['title'] = $batchInfo ? $batchInfo['title'] : "";
        $dataArray['data'] = $data;
        return $dataArray;
    }

    /**获取会员记录
     * @param array $where
     * @return array
     */
    public function getSearchRecordList(array $where)
    {
        /** @var OtherOrderServices $otherOrderSevice */
        $otherOrderSevice = app()->make(OtherOrderServices::class);
        return $otherOrderSevice->getMemberRecord($where);
    }

    /**
     * 看是否开启会员功能
     * @param string $rightType
     * @param bool $get_number
     * @return bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function isOpenMemberCard(string $rightType = '', bool $get_number = true)
    {
        $isOpen = sys_config('member_card_status', 1);
        //如果传入权益类别，查看是否具有某权益
        if (!$rightType) {
            if ($isOpen) return true;
            return false;
        } else {
            /** @var MemberRightServices $memberRightService */
            $memberRightService = app()->make(MemberRightServices::class);
            $memberRight = $memberRightService->getOne(['right_type' => $rightType], 'status,number');
            if ($isOpen && $memberRight && $memberRight['status']) {
                if ($get_number) {
                    $number = $memberRight['number'];
                    if (!$number) return false;
                    return $number;
                }
                return true;
            }
            return false;
        }

    }

    /**
     * 修改会员卡状态
     * @param $id
     * @param $status
     * @return bool
     */
    public function setStatus($id, $status)
    {
        $res = $this->dao->update($id, ['status' => $status]);
        if ($res) return true;
        return false;
    }
}
