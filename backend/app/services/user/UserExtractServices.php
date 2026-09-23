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
declare (strict_types=1);

namespace app\services\user;

use app\dao\user\UserExtractDao;
use crmeb\basic\BaseService;
use app\services\order\OrderCenterServices;
use app\services\pay\PayRecordServices;
use app\services\pay\PayServices;
use app\services\pay\PayTransferNotifyServices;
use app\services\statistic\CapitalFlowServices;
use app\services\system\admin\SystemAdminServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\exceptions\PayException;
use crmeb\services\AliPayService;
use crmeb\utils\FormBuilder as Form;
use crmeb\services\pay\Pay;
use crmeb\services\pay\PayChannelConfig;
use crmeb\services\workerman\ChannelService;

use think\exception\ValidateException;
use think\facade\Route as Url;

/**
 * 用户提现服务
 * Class UserExtractServices
 * @package app\services\user
 */
class UserExtractServices extends BaseService
{
    /**
     * @var UserExtractDao
     */
    protected $dao;

    /**
     * UserExtractServices constructor.
     * @param UserExtractDao $dao
     */
    public function __construct(UserExtractDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 通过事件代理查询用户 openid，避免 app 层直接引用 modules
     * @param int $uid 用户ID
     * @param string $type 用户类型（wechat/routine/app）
     * @return string openid，未取到返回空字符串
     */
    private function proxyUidToOpenid(int $uid, string $type): string
    {
        $results = event('ChannelServiceProxy', ['endpoint' => 'wechat_mp', 'service' => 'WechatUserServices', 'action' => 'uidToOpenid', 'args' => [$uid, $type]]);
        return proxy_event_result($results, '');
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
            $item['receive_price'] = bcsub((string)$item['extract_price'], (string)$item['extract_fee'], 2);
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
        $changed = $this->transaction(function () use ($user, $uid, $id, $extract_number, $message, $userServices, $status, $fail_time) {
            $locked = $this->dao->getDb()->where('id', $id)->lock(true)->find();
            if (!$locked) throw new AdminException('提现记录不存在');
            if ((int)$locked['status'] === -1) return false;
            // 原转账合同先于远端调用落库，响应未知也不能按未转账返佣。
            $transfer = ($locked['extract_type'] ?? '') === 'weixin' && !empty($locked['wechat_order_id'])
                ? app()->make(PayRecordServices::class)->find($locked['wechat_order_id'], true) : [];
            $confirmedFailure = in_array($locked['state'] ?? '', ['FAIL', 'CANCELLED'], true);
            if ((int)$locked['status'] === 1 || (!$confirmedFailure && (!empty($locked['state']) || $transfer))) {
                throw new AdminException('转账已受理或到账，请先在微信确认撤销/失败结果');
            }
            //增加佣金记录（事务内自动增加余额并记录流水）
            /** @var UserBrokerageServices $userBrokerageServices */
            $userBrokerageServices = app()->make(UserBrokerageServices::class);
            $userBrokerageServices->brokerageIncome('extract_fail', (int)$locked['uid'], $locked['extract_price'], $id);
            if (!$this->dao->update($id, ['fail_time' => $fail_time, 'fail_msg' => $message, 'status' => $status])) {
                throw new AdminException('修改失败');
            }
            return true;
        });

        if (!$changed) return true;

        //触发余额变动事件（消息发送已移至 UserBalanceChangeListener）
        event('UserBalanceChange', [
            $uid,
            'sub',
            $extract_number,
            $user['now_money'] ?? 0,
            '提现失败，余额已返还'
        ]);

        return true;
    }

    /**
     * 通过提现申请
     * @param int $id
     * @param $userExtract
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function changeSuccess(int $id, $userExtract)
    {
        $userExtract = $this->dao->get($id);
        if (!$userExtract || (int)$userExtract['status'] !== 0) throw new AdminException('提现状态已变化，不能重复审核');
        if ($userExtract['extract_type'] === 'weixin' && !empty($userExtract['wechat_order_id'])
            && app()->make(PayRecordServices::class)->find($userExtract['wechat_order_id'])) {
            if (!app()->make(PayTransferNotifyServices::class)->sync($userExtract['wechat_order_id'])) {
                throw new PayException('转账结果尚未确认，请稍后重试');
            }
            return 'v3_extract';
        }
        if (!empty($userExtract['state'])) return 'v3_extract';
        $extractNumber = bcsub($userExtract['extract_price'], $userExtract['extract_fee'], 2);
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userType = $userServices->value(['uid' => $userExtract['uid']], 'user_type');
        $nickname = $userServices->value(['uid' => $userExtract['uid']], 'nickname');
        $phone = $userServices->value(['uid' => $userExtract['uid']], 'phone');
        $order_id = $userExtract['wechat_order_id'] !== '' ? $userExtract['wechat_order_id'] : app()->make(OrderCenterServices::class)->getNewOrderId('tx');
        $insertData = ['wechat_order_id' => $order_id, 'nickname' => $nickname, 'phone' => $phone];

        //微信自动提现到零钱
        if (sys_config('weixin_extract_type', 0) && $userExtract['extract_type'] == 'weixin') {
            $type = '';
            $openid = $this->proxyUidToOpenid((int)$userExtract['uid'], (string)$userExtract['channel_type']);
            if ($userExtract['channel_type'] == 'wechat') {
                $type = 'JSAPI';
            } elseif ($userExtract['channel_type'] == 'routine') {
                $type = 'mini';
            } elseif ($userExtract['channel_type'] == 'app') {
                $type = 'APP';
            }
            if (!$openid) {
                $openid = $this->proxyUidToOpenid((int)$userExtract['uid'], 'wechat');
                $type = 'JSAPI';
            }
            if (!$openid) {
                $openid = $this->proxyUidToOpenid((int)$userExtract['uid'], 'routine');
                $type = 'mini';
            }
            if (!$openid) {
                $openid = $this->proxyUidToOpenid((int)$userExtract['uid'], 'app');
                $type = 'APP';
            }
            if (!$openid) {
                throw new ValidateException('该用户暂不支持自动转账到零钱，请手动转账');
            }
            //v3商家转账
            $payServices = app()->make(PayServices::class);
            if ($payServices->hasCapability('wechat', 'merchant_pay_new')) {
                $pay = app()->make(Pay::class, ['v3_wechat_pay']);
                // 支付配置统一存储于 eb_pay_channel.config_fields，不再读取 system_config
                $v3PayConfig = PayChannelConfig::get('weixin_v3');
                // 确认收款使用单笔商家转账，与平台公钥或证书验签模式无关。
                $context = ['channel_key' => 'weixin_v3', 'platform' => $type === 'mini' ? 'wechat_mini' : ($type === 'APP' ? 'app' : 'wechat_mp'),
                    'mode' => 'transfer', 'mch_id' => trim((string)($v3PayConfig['pay_weixin_mchid'] ?? '')),
                    'sub_mch_id' => '', 'app_id' => trim((string)($v3PayConfig[$type === 'mini' ? 'routine_appId' : ($type === 'APP' ? 'wechat_app_appid' : 'wechat_appid')] ?? '')),
                    'openid' => $openid, 'payer_uid' => (int)$userExtract['uid']];
                app()->make(PayRecordServices::class)->prepareTransfer($id, $order_id, $extractNumber, $context);
                $res = $pay->merchantPayNew(
                    $type,
                    $order_id,
                    $v3PayConfig['v3_transfer_scene_id'] ?? '1000',
                    $openid,
                    $userExtract['real_name'],
                    bcmul($extractNumber, '100', 0),
                    '佣金提现到零钱',
                    sys_config('site_url') . '/api/transfer/notify/' . $type,
                    '劳务报酬',
                    [
                        [
                            'info_type' => '岗位类型',
                            'info_content' => '推广员奖励'
                        ],
                        [
                            'info_type' => '报酬说明',
                            'info_content' => '推广订单奖励提现'
                        ],
                    ]
                );
                $this->saveTransferAcceptance($id, $order_id, $res);
                // 只由已验签查单/通知推进终态并记到账流水，受理和待确认均不算到账。
                if (in_array($res['state'] ?? '', ['SUCCESS', 'FAIL', 'CANCELLED'], true)) {
                    app()->make(PayTransferNotifyServices::class)->sync($order_id);
                }
                return 'v3_extract';
            } else {
                $context = (new Pay('wechat_pay'))->getTransferContext([
                    'platform' => $type === 'mini' ? 'wechat_mini' : ($type === 'APP' ? 'app' : 'wechat_mp'),
                    'openid' => $openid, 'payer_uid' => (int)$userExtract['uid'],
                ]);
                app()->make(PayRecordServices::class)->prepareTransfer($id, $order_id, $extractNumber, $context);
                // 微信提现（v2企业付款到零钱）：经事件代理执行，避免 app 层直调 driver 层
                $res = proxy_event_result(event('WechatMerchantPay', [
                    'openid' => $openid,
                    'order_id' => $order_id,
                    'amount' => (string)$extractNumber,
                    'desc' => '提现佣金到零钱',
                    'type' => $type,
                ]));
                if (!$res) throw new ApiException('企业付款尚未确认，请查询原单号后重试');
                $this->saveTransferAcceptance($id, $order_id, ['state' => 'PROCESSING']);
                app()->make(PayTransferNotifyServices::class)->sync($order_id);
                return 'v3_extract';
            }

            if (!$res) {
                throw new ApiException('企业付款到零钱失败，请稍后再试');
            }
        }
        if (sys_config('alipay_extract_type', 0) && $userExtract['extract_type'] == 'alipay') {
            // 构造支付宝提现参数
            // 支付配置统一存储于 eb_pay_channel.config_fields，不再读取 system_config
            $alipaySignType = (int)(PayChannelConfig::get('alipay')['alipay_sign_type'] ?? 0);
            if ($alipaySignType == 0) {
                $bizParams = [
                    'payee_type' => 'ALIPAY_LOGONID', // 收款方账户类型，ALIPAY_LOGONID-支付宝登录账号
                    'payee_account' => $userExtract['real_name'], // 收款方账户，实名认证的支付宝账号
                    'amount' => $extractNumber, // 提现金额
                    'payer_show_name' => sys_config('site_name'), // 付款方姓名/个人名称
                    'payee_real_name' => $userExtract['user_name'], // 收款方真实姓名/个人名称
                    'remark' => '提现 ¥' . $extractNumber . ' 到支付宝', // 业务备注
                ];
            } else {
                $bizParams = [
                    'out_biz_no' => $order_id, // 商户订单号
                    'trans_amount' => $extractNumber,
                    'biz_scene' => 'DIRECT_TRANSFER',
                    'product_code' => 'TRANS_ACCOUNT_NO_PWD',
                    'order_title' => sys_config('site_name') . '提现',
                    'payee_info' => [
                        'identity' => $userExtract['alipay_code'],
                        'identity_type' => 'ALIPAY_LOGON_ID',
                        'name' => $userExtract['user_name'],
                    ],
                    'remark' => '提现 ¥' . $extractNumber . ' 到支付宝', // 业务备注
                ];
            }
            // 调用支付宝服务发起支付宝提现请求
            $res = AliPayService::instance()->merchantPay($bizParams, $alipaySignType);
            // 如果支付宝提现请求失败，则抛出异常
            if (!$res) {
                throw new ApiException('提现失败，请稍查看日志！');
            }
        }

        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        $user = $userService->getUserInfo($userExtract['uid']);
        $insertData['nickname'] = $user['nickname'];
        $insertData['phone'] = $user['phone'];

        /** @var CapitalFlowServices $capitalFlowServices */
        $capitalFlowServices = app()->make(CapitalFlowServices::class);
        $capitalFlowServices->setFlow([
            'order_id' => $order_id,
            'uid' => $userExtract['uid'],
            'price' => bcmul('-1', $extractNumber, 2),
            'pay_type' => $userExtract['extract_type'],
            'nickname' => $insertData['nickname'],
            'phone' => $insertData['phone']
        ], 'extract');

        if (!$this->dao->update($id, ['status' => 1])) {
            throw new AdminException('修改失败');
        }
        //触发提现成功事件（消息发送已移至 UserExtractListener）
        event('UserExtract', [
            $userExtract['uid'],
            $userExtract,
            $extractNumber,
            0,
            strtolower($userType),
            $nickname,
            $user['phone'] ?? ''
        ]);

        return true;
    }

    /**
     * 仅保存转账受理信息，不能提前写终态或覆盖先到的通知。
     * @param int $id 提现ID
     * @param string $orderNo 固定商户单号
     * @param array $response 已验签的受理响应
     * @return void
     */
    public function saveTransferAcceptance(int $id, string $orderNo, array $response): void
    {
        if (($response['out_bill_no'] ?? $orderNo) !== $orderNo) throw new AdminException('转账受理单号不一致');
        $this->transaction(function () use ($id, $orderNo, $response) {
            $source = $this->dao->getDb()->where('id', $id)->lock(true)->find();
            if (!$source || $source['wechat_order_id'] !== $orderNo) throw new AdminException('转账原单号已变化');
            if ((int)$source['status'] !== 0 || in_array($source['state'] ?? '', ['SUCCESS', 'FAIL', 'CANCELLED'], true)) return;
            $state = (string)($response['state'] ?? 'PROCESSING');
            $data = ['out_bill_no' => $orderNo, 'package_info' => $response['package_info'] ?? '',
                'state' => in_array($state, ['SUCCESS', 'FAIL', 'CANCELLED'], true) ? 'PROCESSING' : $state];
            if (!empty($response['transfer_bill_no'])) $data['transfer_bill_no'] = $response['transfer_bill_no'];
            $source->save($data);
        });
    }

    /**
     * 获取当前用户的商家转账详情，查单时由可信终态统一完成到账或返佣。
     * @param int $uid 用户ID
     * @param string $orderNo 原商户转账单号
     * @param bool $sync 是否向微信核验并同步状态
     * @return array 前端收款所需字段
     */
    public function transferInfo(int $uid, string $orderNo, bool $sync = false): array
    {
        $info = $this->dao->getExtractByOrderId($uid, $orderNo);
        if (!$info || $info['extract_type'] !== 'weixin') throw new ApiException('提现记录不存在');
        $record = app()->make(PayRecordServices::class)->find($orderNo);
        if ($record && (($record['channel_key'] ?? '') !== 'weixin_v3' || ($record['context']['mode'] ?? '') !== 'transfer')) {
            throw new ApiException('该提现不支持确认收款');
        }
        if ($sync && (int)$info['status'] === 0 && $record) {
            if (!app()->make(PayTransferNotifyServices::class)->sync($orderNo)) {
                throw new PayException('转账状态核验失败，请稍后重试');
            }
            $info = $this->dao->getExtractByOrderId($uid, $orderNo);
        }
        $context = $record['context'] ?? [];
        $canReceive = (int)$info['status'] === 0 && ($info['state'] ?? '') === 'WAIT_USER_CONFIRM'
            && !empty($info['package_info']) && !empty($context['mch_id']) && !empty($context['app_id']);
        return [
            'id' => (int)$info['id'],
            'wechat_order_id' => $orderNo,
            'out_bill_no' => $info['out_bill_no'] ?? '',
            'transfer_bill_no' => $info['transfer_bill_no'] ?? '',
            'status' => (int)$info['status'],
            'state' => $info['state'] ?? '',
            'fail_reason' => $info['fail_reason'] ?? '',
            'fail_msg' => $info['fail_msg'] ?? '',
            'true_extract_price' => bcsub((string)$info['extract_price'], (string)$info['extract_fee'], 2),
            'package_info' => $canReceive ? $info['package_info'] : '',
            'mchid' => $context['mch_id'] ?? '',
            'wechat_appid' => $context['app_id'] ?? '',
            'can_receive' => $canReceive,
        ];
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
        $brokerage_count = $userBrokerageServices->getUsersBokerageSum($where);
        $refund_brokerage = $userBrokerageServices->sum(['type' => 'refund'], 'number');
        $extract_statistics['brokerage_count'] = bcsub((string)$brokerage_count, (string)$refund_brokerage, 2);
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
            throw new AdminException('数据不存在');
        }
        $f = array();
        $f[] = Form::input('real_name', '姓名', $UserExtract['real_name']);
        $f[] = Form::number('extract_price', '提现金额', (float)$UserExtract['extract_price'])->precision(2)->disabled(true);
        if ($UserExtract['extract_type'] == 'alipay') {
            $f[] = Form::input('alipay_code', '支付宝账号', $UserExtract['alipay_code']);
        } else if ($UserExtract['extract_type'] == 'weixin') {
            $f[] = Form::input('wechat', '微信号', $UserExtract['wechat']);
        } else if ($UserExtract['extract_type'] == 'balance') {
        } else {
            $f[] = Form::input('bank_code', '银行卡号', $UserExtract['bank_code']);
            $f[] = Form::input('bank_address', '开户行', $UserExtract['bank_address']);
        }
        $f[] = Form::input('mark', '备注', $UserExtract['mark'])->type('textarea');
        return create_form('编辑', $f, Url::buildUrl('/finance/extract/' . $id), 'PUT');
    }

    public function update(int $id, array $data)
    {
        if (array_key_exists('extract_price', $data)) {
            $source = $this->dao->get($id);
            if ($source && $source['extract_type'] === 'weixin'
                && bccomp((string)$source['extract_price'], (string)$data['extract_price'], 2) !== 0) {
                throw new AdminException('已扣除的提现佣金金额不能修改');
            }
        }
        if (!$this->dao->update($id, $data))
            throw new AdminException('修改失败');
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
            throw new AdminException('数据不存在');
        }
        if ($extract->status == 1) {
            throw new AdminException('已经提现');
        }
        if ($extract->status == -1) {
            throw new AdminException('您的提现申请已被拒绝');
        }
        $res = $this->changeFail($id, $extract, $message);
        if ($res) {
            return true;
        } else {
            throw new AdminException('操作失败');
        }
    }

    /**
     * 通过
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function adopt(int $id)
    {
        $extract = $this->getExtract($id);
        if (!$extract) {
            throw new AdminException('数据不存在');
        }
        if ($extract->status == 1) {
            throw new AdminException('已经提现');
        }
        if ($extract->status == -1) {
            throw new AdminException('您的提现申请已被拒绝');
        }
        $res = $this->changeSuccess($id, $extract);
        if ($res) {
            return $res;
        } else {
            throw new AdminException('操作失败');
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
        $user = $userService->getUserInfo($uid, 'brokerage_price,uid');
        if (!$user) {
            throw new ApiException('数据不存在');
        }
        $profile = event('DistributionServiceProxy', [
            'service' => 'DistributionUserServices',
            'action' => 'profile',
            'args' => [$uid, $user->toArray()],
        ]);
        if (is_array($profile) && is_array($profile[0] ?? null)) {
            $user['brokerage_price'] = $profile[0]['brokerage_price'];
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
        $data['weixinExtractType'] = (int)sys_config('weixin_extract_type', 0);//微信到账方式
        $data['alipayExtractType'] = (int)sys_config('alipay_extract_type', 0);//支付宝到账方式
        $data['withdrawal_fee'] = sys_config('withdrawal_fee', 0);//提现手续费
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
            throw new ApiException('数据不存在');
        }
        $profile = event('DistributionServiceProxy', [
            'service' => 'DistributionUserServices',
            'action' => 'profile',
            'args' => [$uid, $user->toArray()],
        ]);
        if (is_array($profile) && is_array($profile[0] ?? null)) {
            $user['brokerage_price'] = $profile[0]['brokerage_price'];
        }

        if ($data['extract_type'] == 'weixin' && !sys_config('weixin_extract_type', 0) && !$data['weixin']) {
            throw new ApiException('请输入微信账号');
        }

        if ($data['extract_type'] == 'weixin' && bccomp($data['money'], '0.1', 2) < 0) {
            throw new ApiException('微信提现最低不能小于0.1元');
        }

        //通过事件代理查询用户 openid，避免 app 层直接引用 modules
        $openid = $this->proxyUidToOpenid($uid, 'wechat');
        if (!$openid) {
            $openid = $this->proxyUidToOpenid($uid, 'routine');
        }

        if ($data['extract_type'] == 'weixin' && sys_config('weixin_extract_type', 0) && !$openid) {
            throw new ApiException('请先关注公众号');
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
            throw new ApiException('可提现佣金不足');
        }

        $extractPrice = $user['brokerage_price'];
        $userExtractMinPrice = sys_config('user_extract_min_price');
        if ($data['money'] < $userExtractMinPrice) {
            throw new ApiException('提现金额不能小于{:money}元', ['money' => $userExtractMinPrice]);
        }
        if ($extractPrice < 0) {
            throw new ApiException('提现佣金不足{:money}元', ['money' => $data['money']]);
        }
        if ($data['money'] > $extractPrice) {
            throw new ApiException('提现佣金不足{:money}元', ['money' => $data['money']]);
        }
        if ($data['money'] <= 0) {
            throw new ApiException('提现佣金大于0');
        }
        $data['extract_price'] = bcmul($data['money'], '1', 2);
        $insertData = [
            'wechat_order_id' => app()->make(OrderCenterServices::class)->getNewOrderId('tx'),
            'uid' => $user['uid'],
            'extract_type' => $data['extract_type'],
            'extract_price' => $data['extract_price'],
            'extract_fee' => bcmul((string)$data['extract_price'], bcdiv((string)sys_config('withdrawal_fee', '0'), '100', 4), 2),
            'add_time' => time(),
            'balance' => $user['brokerage_price'],
            'status' => 0,
            'channel_type' => $data['channel_type']
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
        $feeMark = sys_config('withdrawal_fee', 0) == 0 ? '' : '，手续费' . $insertData['extract_fee'] . '元';
        if ($data['extract_type'] == 'alipay') {
            $insertData['alipay_code'] = $data['alipay_code'];
            $insertData['qrcode_url'] = $data['qrcode_url'];
            $insertData['user_name'] = $data['user_name'];
            $insertData['real_name'] = $data['user_name'];
            $mark = '使用支付宝提现' . $insertData['extract_price'] . '元' . $feeMark;
        } else if ($data['extract_type'] == 'bank') {
            $mark = '使用银联卡' . $insertData['bank_code'] . '提现' . $insertData['extract_price'] . '元' . $feeMark;
        } else if ($data['extract_type'] == 'weixin') {
            $insertData['user_name'] = $data['user_name'];
            $insertData['real_name'] = $data['user_name'];
            $insertData['qrcode_url'] = $data['qrcode_url'];
            $mark = '使用微信提现' . $insertData['extract_price'] . '元' . $feeMark;
            if (sys_config('weixin_extract_type', 0) && $openid) {
                if ($data['extract_price'] < 0.1) {
                    throw new ApiException('企业微信付款到零钱最低金额为1元');
                }
            }
        }
        $res1 = $this->transaction(function () use ($insertData, $data, $uid, $userService, $user, $mark) {
            if (!$res1 = $this->dao->save($insertData)) {
                throw new ApiException('申请提现失败');
            }

            //保存佣金记录（事务内自动扣减余额并记录流水）
            /** @var UserBrokerageServices $userBrokerageServices */
            $userBrokerageServices = app()->make(UserBrokerageServices::class);
            $userBrokerageServices->brokerageIncome('extract', $uid, ['mark' => $mark, 'number' => $data['extract_price']], $res1['id']);
            return $res1;
        });

        try {
            ChannelService::instance()->send('WITHDRAW', ['id' => $res1->id]);
        } catch (\Exception $e) {
        }
        /** @var SystemAdminServices $systemAdmin */
        $systemAdmin = app()->make(SystemAdminServices::class);
        $systemAdmin->adminNewPush();
        //触发提现申请事件（消息发送已移至 UserExtractApplyListener）
        event('UserExtractApply', [
            $user['nickname'],
            $data['extract_price']
        ]);

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
