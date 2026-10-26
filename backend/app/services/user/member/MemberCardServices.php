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

namespace app\services\user\member;


use app\dao\user\MemberCardDao;
use crmeb\basic\BaseService;
use app\services\user\MemberOrderServices;
use app\services\order\OrderCenterServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;

/**
 * 会员卡服务类
 * 负责会员卡的生成、领取、查询、状态管理等业务逻辑
 */
class MemberCardServices extends BaseService
{
    /**
     * @var MemberCardDao
     */
    protected $dao;

    /**
     * 会员类型前缀定义
     * @var array
     */
    public static $_memberTypePrefix = ['month', 'quarter', 'year', 'ever', 'free', 'owner'];

    /**
     * 初始化DAO层实例
     * MemberCardServices constructor.
     * @param MemberCardDao $memberCardDao
     */
    public function __construct(MemberCardDao $memberCardDao)
    {
        $this->dao = $memberCardDao;
    }

    /**
     * 获取会员卡列表
     * 根据条件查询会员卡列表，支持按卡号、手机号、使用状态筛选，关联查询用户信息
     * @param array $where 查询条件
     * @return array 返回分页后的会员卡列表数据
     */
    public function getSearchList(array $where = [])
    {
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        [$page, $limit] = $this->getPageValue();
        $where['batch_card_id'] = $where['card_batch_id'];
        //处理使用状态筛选
        if ($where['is_use'] !== "" && $where['is_use'] !== null) {
            if ($where['is_use'] == 0) {
                $where['use_time'] = 0;
            } else {
                $where['use_time'] = 1;
            }
        }
        unset($where['is_use']);
        $list = $this->dao->getSearchList($where, $page, $limit);
        //批量查询用户信息
        $userIds = array_column($list->toArray(), 'use_uid');
        $userList = $userService->getColumn([['uid', 'in', $userIds]], 'nickname,phone,real_name', 'uid');
        // Model 写入整型字段会被截断（如 "2026-10-26 ..." → 2026），先转普通数组再格式化时间
        $rows = [];
        foreach ($list as $v) {
            $item = is_object($v) ? $v->hidden([])->toArray() : (array)$v;
            if (!empty($item['use_uid'])) {
                $user = $userList[$item['use_uid']] ?? null;
                $item['username'] = $user ? (($user['real_name'] ?: $user['nickname']) ?: '') : '';
                $item['phone'] = $user['phone'] ?? '';
            } else {
                $item['username'] = '';
                $item['phone'] = '';
            }
            $item['add_time'] = !empty($item['add_time']) ? date('Y-m-d H:i:s', (int)$item['add_time']) : '';
            $item['use_time'] = !empty($item['use_time']) ? date('Y-m-d H:i:s', (int)$item['use_time']) : '未使用';
            $rows[] = $item;
        }
        $count = $this->dao->count($where);
        return ['list' => $rows, 'count' => $count];

    }

    /**
     * 生成免费会员卡
     * 根据批次信息批量生成会员卡，支持数据切片批量插入提高性能
     * @param array $data 会员卡数据，包含批次ID和生成数量
     * @return bool 返回生成结果
     * @throws AdminException 参数验证失败时抛出异常
     */
    public function addCard(array $data)
    {
        if (!isset($data['card_batch_id']) || !$data['card_batch_id'] || $data['card_batch_id'] == 0 || !isset($data['total_num']) || !$data['total_num'] || $data['total_num'] == 0) {
            throw new AdminException('参数错误');
        }
        try {
            if (!isset($data['total_num'])) throw new AdminException('参数错误');
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
            //数据切片批量插入，提高性能
            $chunk_inster_card = array_chunk($res, 100, true);
            foreach ($chunk_inster_card as $v) {
                $this->dao->insertAll($v);
            }
            return true;
        } catch (\Throwable $exception) {
            // Keep the original exception available to the framework logger while
            // exposing the stable business error to API consumers.
            throw new AdminException('生成卡失败', [], 0, $exception);
        }
    }

    /**
     * 生成随机卡号
     * 根据前缀和随机数生成唯一的会员卡卡号
     * @param bool $prefix 卡号前缀
     * @param bool $random 随机数
     * @return string 返回生成的卡号
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

    /**
     * 用户领取会员卡
     * 验证会员卡信息，更新用户会员状态，记录订单信息
     * @param array $data 领取数据，包含卡号和密码
     * @param int $uid 用户ID
     * @throws ApiException 参数验证或业务规则校验失败时抛出异常
     */
    public function drawMemberCard(array $data, int $uid)
    {
        if (!$uid || !$data) throw new ApiException('参数错误');
        $isOpenMember = $this->isOpenMemberCard();
        if (!$isOpenMember) throw new ApiException('会员功能暂未开启');
        if (!isset($data['member_card_code']) || !$data['member_card_code']) throw new ApiException('请输入会员卡号');
        if (!isset($data['member_card_code']) || !$data['member_card_pwd']) throw new ApiException('请输入领取卡密');
        $card_info = $this->dao->getOneByWhere(['card_number' => trim($data['member_card_code'])]);
        if (!$card_info) throw new ApiException('会员卡不存在');
        /** @var MemberCardBatchServices $memberBatchServices */
        $memberBatchServices = app()->make(MemberCardBatchServices::class);
        $batch_info = $memberBatchServices->getOne($card_info['card_batch_id']);
        if (!$batch_info) throw new ApiException('会员卡未激活，暂无法使用');
        if ($batch_info->status != 1) throw new ApiException('会员卡未激活，暂无法使用');
        if ($card_info['status'] == 0) throw new ApiException('会员卡未激活，暂无法使用');
        if ($card_info['card_password'] != trim($data['member_card_pwd'])) throw new ApiException('会员卡密码有误');
        if ($card_info['use_uid'] && $card_info['use_time']) throw new ApiException('会员卡已使用');
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user_info = $userServices->getUserInfo($uid);
        if (!$user_info) throw new ApiException('用户不存在');
        if ($user_info->is_money_level > 0 && $user_info->is_ever_level == 1) throw new ApiException('您已是永久会员，无需再领取，可以将此卡转送亲朋好友，一起享受优惠');

        //使用事务保证数据一致性
        $this->transaction(function () use ($card_info, $user_info, $batch_info, $memberBatchServices, $userServices, $data) {
            $res1 = $this->dao->update($card_info->id, ['use_uid' => $user_info->uid, 'use_time' => time(), 'update_time' => time()], 'id');
            if ($res1) {
                $res2 = $memberBatchServices->useCardSetInc($batch_info->id, 'use_num', 1);
                $overdue_time = 0;
                //根据用户当前会员等级计算过期时间
                switch ($user_info->is_money_level) {
                    case 1:
                    case 2:
                    case 3:
                        $overdue_time = bcadd(bcmul((string)$batch_info->use_day, '86400', 0), (string)$user_info->overdue_time, 0);
                        $overdue_time = mktime(date('H'), date('i'), date('s'), date('m', $overdue_time), date('d', $overdue_time), date('Y', $overdue_time));
                        break;
                    case 0:
                        $overdue_time = bcadd(bcmul((string)$batch_info->use_day, '86400', 0), (string)time(), 0);
                        break;
                }
                $channel_type = $data['from'];
                /** @var MemberOrderServices $MemberOrderServices */
                $MemberOrderServices = app()->make(MemberOrderServices::class);
                /** @var OrderCenterServices $orderCenterService */
                $orderCenterService = app()->make(OrderCenterServices::class);
                $record_data['uid'] = $user_info->uid;
                $record_data['member_code'] = $card_info->card_number;
                $record_data['use_day'] = $batch_info->use_day;
                $record_data['overdue_time'] = $overdue_time;
                $record_data['order_id'] = $orderCenterService->getNewOrderId();
                $record_data['channel_type'] = $channel_type;
                $record_data['member_type'] = "free";
                $record_data['vip_day'] = $batch_info->use_day;
                $record_data['type'] = 2;
                $record_data['paid'] = 1;
                $record_data['pay_time'] = time();
                $res3 = $MemberOrderServices->addMemberOrderData($record_data);
                /** @var UserServices $userServices */
                $userServices = app()->make(UserServices::class);
                $res4 = $userServices->setMemberOverdueTime($batch_info->use_day, $user_info->uid, 2, $record_data['member_type']);
                $res5 = $res1 && $res2 && $res3 && $res4;
                return $res5;
            }
        });
    }

    /**
     * 验证会员类型是否存在
     * 检查指定的会员类型是否在允许的类型列表中
     * @param string $member_type 会员类型
     * @return bool 返回验证结果
     * @throws ApiException 会员类型不存在时抛出异常
     */
    public function checkmemberType(string $member_type)
    {
        $member_type_arr = $this->getMemberTypeInfo();
        if (!array_key_exists($member_type, $member_type_arr)) throw new ApiException('暂无此类型会员卡');
        return true;
    }

    /**
     * 获取会员权益配置信息
     * 查询所有启用的会员权益，并格式化权益描述
     * @return array 返回会员权益配置数据
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
                //根据权益类型格式化描述
                if ($v['right_type'] == 'offline') $v['explain'] = '线下支付打' . floatval(bcdiv((string)$v['number'], '10', 1)) . '折';
                if ($v['right_type'] == 'sign') $v['explain'] = '签到多得' . (int)$v['number'] . '倍积分';
                if ($v['right_type'] == 'express') $v['explain'] = '运费打' . floatval(bcdiv((string)$v['number'], '10', 1)) . '折';
                if ($v['right_type'] == 'integral') $v['explain'] = '消费多返' . (int)$v['number'] . '倍积分';
            }
        }

        return ['member_right' => $memberRight['list']];
    }

    /**
     * 获取会员卡配置信息
     * 从系统配置中读取各种会员类型的配置
     * @return array 返回会员类型配置数据
     */
    public function getMemberTypeInfo()
    {
        $data = [];
        foreach (self::$_memberTypePrefix as $v) {
            $data[$v] = sys_config_more([$v . '_title', $v . '_vip_day', $v . '_pre_price', $v . '_price']);
        }
        return $data;
    }

    /**
     * 获取会员类型数据列表
     * 查询所有会员类型，并格式化返回
     * @return array 返回会员类型列表
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

    /**
     * 获取会员类型映射数据
     * 将会员类型列表转换为以ID为键的关联数组
     * @return bool|array 返回会员类型映射数据或false
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

    /**
     * 导出会员卡数据
     * 根据条件查询会员卡数据，用于导出功能
     * @param array $where 查询条件
     * @return \think\Collection 返回会员卡数据集合
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
        $rows = [];
        foreach ($data as $v) {
            $item = is_object($v) ? $v->hidden([])->toArray() : (array)$v;
            $item['use_time'] = !empty($item['use_time']) ? date('Y-m-d H:i:s', (int)$item['use_time']) : '';
            $item['user_name'] = '';
            $item['user_phone'] = '';
            if (!empty($item['use_uid'])) {
                $userInfo = $userService->get($item['use_uid']);
                $item['user_name'] = $userInfo['nickname'] ?: $userInfo['account'];
                $item['user_phone'] = $userInfo['phone'];
            }
            $rows[] = $item;
        }
        $batchInfo = $batchService->getOne($where['batch_card_id']);
        $dataArray['title'] = $batchInfo ? $batchInfo['title'] : "";
        $dataArray['data'] = $rows;
        return $dataArray;
    }

    /**
     * 获取会员记录列表
     * 查询用户购买或领取会员卡的记录
     * @param array $where 查询条件
     * @return array 返回会员记录列表数据
     */
    public function getSearchRecordList(array $where)
    {
        /** @var MemberOrderServices $memberOrderSevice */
        $memberOrderSevice = app()->make(MemberOrderServices::class);
        return $memberOrderSevice->getMemberRecord($where);
    }

    /**
     * 检查会员功能是否开启
     * 查询系统配置中会员功能的开关状态，支持查询特定权益
     * @param string $rightType 权益类型，为空时仅检查会员功能开关
     * @param bool $get_number 是否返回权益数值
     * @return bool|mixed 返回会员功能状态或权益数值
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
     * 更新会员卡的激活/冻结状态，修改前检查批次状态
     * @param int $id 会员卡ID
     * @param int $status 目标状态
     * @return bool 返回修改结果
     * @throws AdminException 批次未激活时抛出异常
     */
    public function setStatus($id, $status)
    {
        $card_batch_id = $this->dao->value(['id' => $id], 'card_batch_id');
        $card_batch_status = app()->make(MemberCardBatchServices::class)->value(['id' => $card_batch_id], 'status');
        if ($card_batch_status == 0) {
            throw new AdminException('批次未激活，暂无法使用');
        }
        $res = $this->dao->update($id, ['status' => $status]);
        if ($res) return true;
        return false;
    }
}
