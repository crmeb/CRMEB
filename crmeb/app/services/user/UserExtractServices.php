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

namespace app\services\user;

use app\dao\user\UserExtractDao;
use app\services\BaseServices;
use app\services\order\StoreOrderCreateServices;
use app\services\statistic\CapitalFlowServices;
use app\services\system\admin\SystemAdminServices;
use app\services\wechat\WechatUserServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\services\FormBuilder as Form;
use crmeb\services\app\WechatService;
use crmeb\services\pay\Pay;
use crmeb\services\workerman\ChannelService;
use think\facade\Route as Url;

/**
 *
 * Class UserExtractServices
 * @package app\services\user
 */
class UserExtractServices extends BaseServices
{

    /**
     * UserExtractServices constructor.
     * @param UserExtractDao $dao
     */
    public function __construct(UserExtractDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取一条提现记录
     * @param int $id
     * @param array $field
     * @return array|\think\Model|null
     */
    public function getExtract(int $id, array $field = [])
    {
        return $this->dao->get($id, $field);
    }

    /**
     * 获取某个用户提现总数
     * @param int $uid
     * @return float
     */
    public function getUserExtract(int $uid)
    {
        return $this->dao->getWhereSum(['uid' => $uid, 'status' => 1]);
    }

    /**
     * 获取某些用户的提现总数列表
     * @param array $uids
     */
    public function getUsersSumList(array $uids)
    {
        return $this->dao->getWhereSumList(['uid' => $uids, 'status' => 1]);
    }

    public function getCount(array $where = [])
    {
        return $this->dao->getCount($where);
    }

    /**
     * 获取提现列表
     * @param array $where
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserExtractList(array $where, string $field = '*')
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getExtractList($where, $field, $page, $limit);
        foreach ($list as &$item) {
            $item['nickname'] = $item['user']['nickname'] ?? '';
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 获取提现总数
     * @param array $where
     */
    public function getExtractSum(array $where)
    {
        return $this->dao->getExtractMoneyByWhere($where, 'extract_price');
    }

    /**
     * 拒绝提现申请
     * @param $id
     * @param $fail_msg
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function changeFail(int $id, $userExtract, $message)
    {
        $fail_time = time();
        $extract_number = $userExtract['extract_price'];
        $mark = '提现失败,退回佣金' . $extract_number . '元';
        $uid = $userExtract['uid'];
        $status = -1;
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserInfo($uid);
        $this->transaction(function () use ($user, $uid, $id, $extract_number, $message, $userServices, $status, $fail_time) {
            //增加佣金记录
            /** @var UserBrokerageServices $userBrokerageServices */
            $userBrokerageServices = app()->make(UserBrokerageServices::class);
            $now_brokerage = bcadd((string)$user['brokerage_price'], (string)$extract_number, 2);
            $userBrokerageServices->income('extract_fail', $uid, $extract_number, $now_brokerage, $id);
            if (!$userServices->update($uid, ['brokerage_price' => bcadd((string)$user['brokerage_price'], (string)$extract_number, 2)], 'uid'))
                throw new AdminException(400657);
            if (!$this->dao->update($id, ['fail_time' => $fail_time, 'fail_msg' => $message, 'status' => $status])) {
                throw new AdminException(100007);
            }
        });

        event('notice.notice', [['uid' => $uid, 'userType' => strtolower($user['user_type']), 'extract_number' => $extract_number, 'nickname' => $user['nickname'], 'message' => $message], 'user_balance_change']);
        return true;
    }

    /**
     * 通过提现申请
     * @param $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function changeSuccess(int $id, $userExtract)
    {
        $extractNumber = $userExtract['extract_price'];
        /** @var WechatUserServices $wechatServices */
        $wechatServices = app()->make(WechatUserServices::class);
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userType = $userServices->value(['uid' => $userExtract['uid']], 'user_type');
        $nickname = $userServices->value(['uid' => $userExtract['uid']], 'nickname');
        $phone = $userServices->value(['uid' => $userExtract['uid']], 'phone');
        event('notice.notice', [['uid' => $userExtract['uid'], 'userType' => strtolower($userType), 'extractNumber' => $extractNumber, 'nickname' => $nickname], 'user_extract']);

        if (!$this->dao->update($id, ['status' => 1])) {
            throw new AdminException(100007);
        }
        switch ($userExtract['extract_type']) {
            case 'bank':
                $order_id = $userExtract['bank_code'];
                break;
            case 'weixin':
                $order_id = $userExtract['wechat'];
                break;
            case 'alipay':
                $order_id = $userExtract['alipay_code'];
                break;
            default:
                $order_id = '';
                break;
        }

        $insertData = ['order_id' => $order_id, 'nickname' => $nickname, 'phone' => $phone];
        
        //自动提现到零钱
        if ($userExtract['extract_type'] == 'weixin' && sys_config('brokerage_type', 0)) {

            $openid = $wechatServices->uidToOpenid($userExtract['uid'], 'wechat');
            if (!$openid) $openid = $wechatServices->uidToOpenid($userExtract['uid'], 'routine');

            /** @var StoreOrderCreateServices $services */
            $services = app()->make(StoreOrderCreateServices::class);
            $insertData['order_id'] = $services->getNewOrderId();

            //v3商家转账到零钱
            if (sys_config('pay_wechat_type')) {
                $pay = new Pay('v3_wechat_pay');
                $res = $pay->merchantPay($openid, $insertData['order_id'], $userExtract['extract_price'], [
                    'batch_name' => '提现佣金到零钱',
                    'batch_remark' => '您于' . date('Y-m-d H:i:s') . '提现.' . $userExtract['extract_price'] . '元'
                ]);
            } else {
                // 微信提现
                $res = WechatService::merchantPay($openid, $insertData['order_id'], $userExtract['extract_price'], '提现佣金到零钱');
            }

            if (!$res) {
                throw new ApiException(400658);
            }

            // 更新 提现申请记录 wechat_order_id
            $this->dao->update($id, ['wechat_order_id' => $insertData['order_id']]);

            /** @var UserServices $userService */
            $userService = app()->make(UserServices::class);
            $user = $userService->getUserInfo($userExtract['uid']);

            $insertData['nickname'] = $user['nickname'];
            $insertData['phone'] = $user['phone'];
        }

        /** @var CapitalFlowServices $capitalFlowServices */
        $capitalFlowServices = app()->make(CapitalFlowServices::class);
        $capitalFlowServices->setFlow([
            'order_id' => $insertData['order_id'],
            'uid' => $userExtract['uid'],
            'price' => bcmul('-1', $extractNumber, 2),
            'pay_type' => $userExtract['extract_type'],
            'nickname' => $insertData['nickname'],
            'phone' => $insertData['phone']
        ], 'extract');
        return true;
    }

    /**
     * 显示资源列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(array $where)
    {
        $list = $this->getUserExtractList($where);
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        //待提现金额
        $where['status'] = 0;
        $extract_statistics['price'] = $this->getExtractSum($where);
        //已提现金额
        $where['status'] = 1;
        $extract_statistics['priced'] = $this->getExtractSum($where);
        /** @var UserBrokerageServices $userBrokerageServices */
        $userBrokerageServices = app()->make(UserBrokerageServices::class);
        $where['pm'] = 1;
        $extract_statistics['brokerage_count'] = $userBrokerageServices->getUsersBokerageSum($where);
        //未提现金额
        $extract_statistics['brokerage_not'] = $extract_statistics['brokerage_count'] > $extract_statistics['priced'] ? bcsub((string)$extract_statistics['brokerage_count'], (string)$extract_statistics['priced'], 2) : 0.00;
        return compact('extract_statistics', 'list');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit(int $id)
    {
        $UserExtract = $this->getExtract($id);
        if (!$UserExtract) {
            throw new AdminException(100026);
        }
        $f = array();
        $f[] = Form::input('real_name', '姓名', $UserExtract['real_name']);
        $f[] = Form::number('extract_price', '提现金额', (float)$UserExtract['extract_price'])->precision(2)->disabled(true);
        if ($UserExtract['extract_type'] == 'alipay') {
            $f[] = Form::input('alipay_code', '支付宝账号', $UserExtract['alipay_code']);
        } else if ($UserExtract['extract_type'] == 'weixin') {
            $f[] = Form::input('wechat', '微信号', $UserExtract['wechat']);
        } else {
            $f[] = Form::input('bank_code', '银行卡号', $UserExtract['bank_code']);
            $f[] = Form::input('bank_address', '开户行', $UserExtract['bank_address']);
        }
        $f[] = Form::input('mark', '备注', $UserExtract['mark'])->type('textarea');
        return create_form('编辑', $f, Url::buildUrl('/finance/extract/' . $id), 'PUT');
    }

    public function update(int $id, array $data)
    {
        if (!$this->dao->update($id, $data))
            throw new AdminException(100007);
        else
            return true;
    }

    /**
     * 拒绝
     * @param $id
     * @return mixed
     */
    public function refuse(int $id, string $message)
    {
        $extract = $this->getExtract($id);
        if (!$extract) {
            throw new AdminException(100026);
        }
        if ($extract->status == 1) {
            throw new AdminException(400659);
        }
        if ($extract->status == -1) {
            throw new AdminException(400660);
        }
        $res = $this->changeFail($id, $extract, $message);
        if ($res) {
            return true;
        } else {
            throw new AdminException(100005);
        }
    }

    /**
     * 通过
     * @param $id
     * @return mixed
     */
    public function adopt(int $id)
    {
        $extract = $this->getExtract($id);
        if (!$extract) {
            throw new AdminException(100026);
        }
        if ($extract->status == 1) {
            throw new AdminException(400659);
        }
        if ($extract->status == -1) {
            throw new AdminException(400660);
        }
        if ($this->changeSuccess($id, $extract)) {
            return true;
        } else {
            throw new AdminException(100005);
        }
    }

    /**待提现的数量
     * @return int
     */
    public function userExtractCount()
    {
        return $this->dao->count(['status' => 0]);
    }

    /**
     * 银行卡提现
     * @param int $uid
     * @return mixed
     */
    public function bank(int $uid)
    {
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        $user = $userService->getUserInfo($uid);
        if (!$user) {
            throw new ApiException(100026);
        }
        /** @var UserBrokerageServices $services */
        $services = app()->make(UserBrokerageServices::class);
        $data['broken_commission'] = $services->getUserFrozenPrice($uid);
        if ($data['broken_commission'] < 0)
            $data['broken_commission'] = '0';
        $data['brokerage_price'] = $user['brokerage_price'];
        //可提现佣金
        $data['commissionCount'] = bcsub((string)$data['brokerage_price'], (string)$data['broken_commission'], 2);
        $extractBank = sys_config('user_extract_bank') ?? []; //提现银行
        $extractBank = str_replace("\r\n", "\n", $extractBank);//防止不兼容
        $data['extractBank'] = explode("\n", is_array($extractBank) ? ($extractBank[0] ?? $extractBank) : $extractBank);
        $data['minPrice'] = sys_config('user_extract_min_price');//提现最低金额
        $data['brokerageType'] = sys_config('brokerage_type', 0);//到账方式
        return $data;
    }

    /**
     * 提现申请
     * @param int $uid
     * @param array $data
     */
    public function cash(int $uid, array $data)
    {
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        $user = $userService->getUserInfo($uid);
        if (!$user) {
            throw new ApiException(100026);
        }

        if ($data['extract_type'] == 'weixin' && !sys_config('brokerage_type', 0) && !$data['weixin']) {
            throw new ApiException(400110);
        }

        if ($data['extract_type'] == 'weixin' && bccomp($data['money'], '1', 2) < 0) {
            throw new ApiException(410112);
        }

        /** @var WechatUserServices $wechatServices */
        $wechatServices = app()->make(WechatUserServices::class);
        $openid = $wechatServices->uidToOpenid($uid, 'wechat');
        if (!$openid) $openid = $wechatServices->uidToOpenid($uid, 'routine');

        if ($data['extract_type'] == 'weixin' && sys_config('brokerage_type', 0) && !$openid) {
            throw new ApiException(410024);
        }

        /** @var UserBrokerageServices $services */
        $services = app()->make(UserBrokerageServices::class);
        $data['broken_commission'] = $services->getUserFrozenPrice($uid);
        if ($data['broken_commission'] < 0)
            $data['broken_commission'] = 0;
        $data['brokerage_price'] = $user['brokerage_price'];
        //可提现佣金
        $commissionCount = bcsub((string)$data['brokerage_price'], (string)$data['broken_commission'], 2);
        if ($data['money'] > $commissionCount) {
            throw new ApiException(400661);
        }

        $extractPrice = $user['brokerage_price'];
        $userExtractMinPrice = sys_config('user_extract_min_price');
        if ($data['money'] < $userExtractMinPrice) {
            throw new ApiException(400662, ['money' => $userExtractMinPrice]);
        }
        if ($extractPrice < 0) {
            throw new ApiException(400663, ['money' => $data['money']]);
        }
        if ($data['money'] > $extractPrice) {
            throw new ApiException(400663, ['money' => $data['money']]);
        }
        if ($data['money'] <= 0) {
            throw new ApiException(400664);
        }
        $insertData = [
            'uid' => $user['uid'],
            'extract_type' => $data['extract_type'],
            'extract_price' => $data['money'],
            'add_time' => time(),
            'balance' => $user['brokerage_price'],
            'status' => 0
        ];
        if (isset($data['name']) && strlen(trim($data['name']))) $insertData['real_name'] = $data['name'];
        else $insertData['real_name'] = $user['nickname'];
        if (isset($data['cardnum'])) $insertData['bank_code'] = $data['cardnum'];
        else $insertData['bank_code'] = '';
        if (isset($data['bankname'])) $insertData['bank_address'] = $data['bankname'];
        else $insertData['bank_address'] = '';
        if (isset($data['weixin'])) $insertData['wechat'] = $data['weixin'];
        else $insertData['wechat'] = $user['nickname'];
        $mark = '';
        if ($data['extract_type'] == 'alipay') {
            $insertData['alipay_code'] = $data['alipay_code'];
            $insertData['qrcode_url'] = $data['qrcode_url'];
            $mark = '使用支付宝提现' . $insertData['extract_price'] . '元';
        } else if ($data['extract_type'] == 'bank') {
            $mark = '使用银联卡' . $insertData['bank_code'] . '提现' . $insertData['extract_price'] . '元';
        } else if ($data['extract_type'] == 'weixin') {
            $insertData['qrcode_url'] = $data['qrcode_url'];
            $mark = '使用微信提现' . $insertData['extract_price'] . '元';
            if (sys_config('brokerage_type', 0) && $openid) {
                if ($data['money'] < 1) {
                    throw new ApiException(400665);
                }
            }
        }
        $res1 = $this->transaction(function () use ($insertData, $data, $uid, $userService, $user, $mark) {
            if (!$res1 = $this->dao->save($insertData)) {
                throw new ApiException(410121);
            }
            $balance = bcsub((string)$user['brokerage_price'], (string)$data['money'], 2) ?? 0;
            if (!$userService->update($uid, ['brokerage_price' => $balance], 'uid')) {
                throw new ApiException(410121);
            }

            //保存佣金记录
            /** @var UserBrokerageServices $userBrokerageServices */
            $userBrokerageServices = app()->make(UserBrokerageServices::class);
            $userBrokerageServices->income('extract', $uid, ['mark' => $mark, 'number' => $data['money']], $balance, $res1['id']);
            return $res1;
        });

        try {
            ChannelService::instance()->send('WITHDRAW', ['id' => $res1->id]);
        } catch (\Exception $e) {
        }
        /** @var SystemAdminServices $systemAdmin */
        $systemAdmin = app()->make(SystemAdminServices::class);
        $systemAdmin->adminNewPush();
        //消息
        event('notice.notice', [['nickname' => $user['nickname'], 'money' => $data['money']], 'kefu_send_extract_application']);

        return true;
    }

    /**
     * @param array $where
     * @param string $SumField
     * @param string $selectType
     * @param string $group
     * @return float|mixed
     */
    public function getOutMoneyByWhere(array $where, string $SumField, string $selectType, string $group = "")
    {
        switch ($selectType) {
            case "sum" :
                return $this->dao->getWhereSumField($where, $SumField);
            case "group" :
                return $this->dao->getGroupField($where, $SumField, $group);
        }
    }
}
