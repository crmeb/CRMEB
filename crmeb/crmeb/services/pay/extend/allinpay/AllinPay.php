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
namespace crmeb\services\pay\extend\allinpay;


/**
 *
 * Class AllinPay
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2022/12/27
 * @package crmeb\services\pay\extend\allinpay
 */
class AllinPay extends Client
{

    //统一支付接口
    const UNITODER_PAY_API = 'unitorder/pay';

    //统一扫码接口
    const UNITORDER_SCANQRPAY = 'unitorder/scanqrpay';

    //退款
    const UNITODER_TRANX_REFUND = 'tranx/refund';

    //订单查询
    const  UNITODER_TRANX_QUERY = 'tranx/query';

    const  UNITODER_QPAY_AGREEAPPLY = 'qpay/agreeapply';

    //微信公众号内支付请求地址
    const UNITODER_H5UNIONPAY = 'https://vsp.allinpay.com/apiweb/h5unionpay/unionorder';

    /**
     * @var string
     */
    protected $api = '';

    /**
     * 支付类型
     * @var string
     */
    protected $payType = '';

    /**
     * @var string
     */
    protected $version = '';

    /**
     * @var string
     */
    protected $orderKey = 'reqsn';

    /**
     * @var int
     */
    protected $validtime = 720;

    /**
     * 创建支付订单
     * @param string $trxamt
     * @param string $orderId
     * @param string $body
     * @param string|null $openId
     * @param string|null $appid
     * @param string $frontUrl
     * @param string $remark
     * @return mixed
     */
    public function create(string $trxamt, string $orderId, string $body, string $returl = null, string $openId = null, string $appid = null, string $frontUrl = '', string $remark = '')
    {
        $totalFee = (int)bcmul($trxamt, '100');

        $data = [
            'trxamt' => $totalFee,
            $this->orderKey => $orderId,
            'validtime' => $this->validtime,
            'notify_url' => $this->notifyUrl
        ];

        if ($body) {
            $data['body'] = $body;
        }

        if ($remark) {
            $data['remark'] = $remark;
        }

        if ($this->payType) {
            $data['paytype'] = $this->payType;
        }

        if ($returl) {
            $data['returl'] = $returl;
        }

        if ($openId) {
            $data['acct'] = $openId;
        }

        if ($appid) {
            $data['sub_appid'] = $appid;
        }

        if ($frontUrl) {
            $data['front_url'] = $frontUrl;
        }

        if ($this->version) {
            $data['version'] = $this->version;
        }

        $form = !$this->api;
        $api = $this->api;

        $this->version = '';
        $this->payType = '';
        $this->api = '';
        $this->orderKey = 'reqsn';
        $this->validtime = 720;

        return $this->send($api, ['data' => $data, 'form' => $form]);
    }

    /**
     * 微信H5支付
     * @param string $trxamt
     * @param string $orderId
     * @param string $body
     * @param string $returl
     * @param string $remark
     * @return mixed
     */
    public function h5Pay(string $trxamt, string $orderId, string $body, string $returl, string $remark = '')
    {
        $this->version = self::VERSION_NUM_12;
        return $this->create($trxamt, $orderId, $body, $returl, null, null, '', $remark);
    }

    /**
     * 微信js支付
     * @param string $trxamt
     * @param string $orderId
     * @param string $body
     * @param string $openId
     * @param string $appId
     * @param string $frontUrl
     * @param string $remark
     * @return mixed
     */
    public function wechatPay(string $trxamt, string $orderId, string $body, string $openId, string $appId, string $frontUrl, string $remark = '')
    {
        $this->api = self::UNITODER_PAY_API;
        return $this->create($trxamt, $orderId, $body, null, $openId, $appId, $frontUrl, $remark);
    }

    /**
     * app支付 微信和支付宝
     * @param string $trxamt
     * @param string $orderId
     * @param string $body
     * @param bool $isWechat
     * @param string $remark
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/1/14
     */
    public function appPay(string $trxamt, string $orderId, string $body, string $appid, string $openid = null, bool $isWechat = true, string $remark = '')
    {
        $this->api = self::UNITODER_PAY_API;
        $this->payType = $isWechat ? 'A02' : 'A01';
        $this->version = self::VERSION_NUM_11;
        return $this->create($trxamt, $orderId, $body, null, $openid, $appid, '', $remark);
    }

    /**
     * PC 收银台支付
     * @param string $trxamt
     * @param string $orderId
     * @param string $body
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/1/15
     */
    public function pcPay(string $trxamt, string $orderId, string $body, string $returl)
    {
        $totalFee = (int)bcmul($trxamt, '100');

        $data = [
            'paytype' => 'B2C',
            'trxamt' => $totalFee,
            'orderid' => $orderId,
            'notifyurl' => $this->notifyUrl,
            'goodsinf' => $body,
            'validtime' => $this->validtime,
            'returl' => $returl,
            'cusid' => $this->cusid,
            'appid' => $this->appid,
            'signtype' => $this->signType,
            'randomstr' => uniqid(),
            'charset' => 'UTF-8'
        ];

        $data['sign'] = $this->sign($data);

        return $data;
    }

    /**
     * @param string $trxamt
     * @param string $orderId
     * @param string $body
     * @param string $returl
     * @param string $remark
     * @return array
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/1/15
     */
    public function miniproPay(string $trxamt, string $orderId, string $body, string $remark = '')
    {
        $totalFee = bcmul($trxamt, '100');

        $data = [
            'paytype' => 'W06',
            'trxamt' => $totalFee,
            'reqsn' => $orderId,
            'notify_url' => $this->notifyUrl,
            'body' => $body,
            'validtime' => (string)$this->validtime,
            'cusid' => $this->cusid,
            'appid' => $this->appid,
            'signtype' => $this->signType,
            'randomstr' => uniqid(),
            'remark' => $remark,
            'version' => self::VERSION_NUM_12
        ];

        $data['sign'] = $this->sign($data);

        return $data;
    }

    public function agreeapply()
    {
        $data = [
            'meruserid' => 'eesssxxx',
            'accttype' => '00',
            'acctno' => '',
            'idtype' => 0,
            'idno' => '',
            'acctname' => '',
            'mobile' => '',
            'cvv2' => '',
            'reqip' => request()->ip(),
            'reqtime' => date('Y-m-d H:i:s'),
            'version' => self::VERSION_NUM_11,
        ];

        $data['cusid'] = $this->cusid;
        $data['appid'] = $this->appid;
        $data['signtype'] = 'RSA';
        $data['randomstr'] = uniqid();
        $data['sign'] = $this->sign($data);

        return $this->send(self::UNITODER_QPAY_AGREEAPPLY, ['data' => $data]);
    }


    /**
     * 查询订单
     * @param string $reqsn
     * @return array|mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/1/15
     */
    public function query(string $reqsn)
    {
        $data = [
            'reqsn' => $reqsn
        ];

        return $this->send(self::UNITODER_TRANX_QUERY, ['data' => $data]);
    }

    /**
     * 发起退款
     * @param string $trxamt
     * @param string $reqsn
     * @return array|mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/1/15
     */
    public function refund(string $trxamt, string $orderId, string $reqsn)
    {
        $totalFee = (int)bcmul($trxamt, '100');

        $data = [
            'trxamt' => $totalFee,
            'reqsn' => $orderId,
            'oldtrxid' => $reqsn,
        ];

        return $this->send(self::UNITODER_TRANX_REFUND, ['data' => $data]);
    }

    /**
     * 异步回调
     * @param callable $callback
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/1/15
     */
    public function handleNotify(callable $callback)
    {
        $params = [];
        foreach (request()->post() as $key => $val) {
            $params[$key] = $val;
        }

        $this->debugLog('通联支付回调数据' . json_encode($params));

        if (count($params) < 1) {
            //如果参数为空,则不进行处理
            return "error";
        }

        $res = $this->validSign($params);

        $this->debugLog('通联支付回调验证：' . json_encode(['res' => $res]));

        if ($res && isset($params['trxstatus']) && $params['trxstatus'] === '0000') {
            //验签成功
            return $callback($params) ? 'success' : 'error';
        } else {
            return "error";
        }
    }
}
