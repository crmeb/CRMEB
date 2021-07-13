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
namespace app\api\controller\v1\user;

use app\Request;
use app\services\user\UserExtractServices;
use think\facade\Config;

/**
 * 提现类
 * Class UserExtractController
 * @package app\api\controller\user
 */
class UserExtractController
{
    protected $services = NUll;

    /**
     * UserExtractController constructor.
     * @param UserExtractServices $services
     */
    public function __construct(UserExtractServices $services)
    {
        $this->services = $services;
    }

    /**
     * 提现银行
     * @param Request $request
     * @return mixed
     */
    public function bank(Request $request)
    {
        $uid = (int)$request->uid();
        return app('json')->successful($this->services->bank($uid));
    }

    /**
     * 提现申请
     * @param Request $request
     * @return mixed
     */
    public function cash(Request $request)
    {
        $extractInfo = $request->postMore([
            ['alipay_code', ''],
            ['extract_type', ''],
            ['money', 0],
            ['name', ''],
            ['bankname', ''],
            ['cardnum', ''],
            ['weixin', ''],
            ['qrcode_url', ''],
        ]);
        $extractType = Config::get('pay.extractType', []);
        if (!in_array($extractInfo['extract_type'], $extractType))
            return app('json')->fail('提现方式不存在');
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', (float)$extractInfo['money'])) return app('json')->fail('提现金额输入有误');
        if (!$extractInfo['cardnum'] == '')
            if (!preg_match('/^([1-9]{1})(\d{15}|\d{16}|\d{18})$/', $extractInfo['cardnum']))
                return app('json')->fail('银行卡号输入有误');
        if ($extractInfo['extract_type'] == 'alipay') {
            if (!$extractInfo['alipay_code']) return app('json')->fail('请输入支付宝账号');
        } else if ($extractInfo['extract_type'] == 'bank') {
            if (!$extractInfo['cardnum']) return app('json')->fail('请输入银行卡账号');
            if (!$extractInfo['bankname']) return app('json')->fail('请输入开户行信息');
        } else if ($extractInfo['extract_type'] == 'weixin') {
            if (!$extractInfo['weixin']) return app('json')->fail('请输入微信账号');
        }
        $uid = (int)$request->uid();
        if ($this->services->cash($uid, $extractInfo))
            return app('json')->successful('申请提现成功!');
        else
            return app('json')->fail('提现失败');
    }
}
