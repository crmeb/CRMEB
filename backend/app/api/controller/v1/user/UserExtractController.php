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
namespace app\api\controller\v1\user;

use app\Request;
use app\services\user\UserExtractServices;
use crmeb\services\CacheService;
use think\facade\Config;
use think\exception\ValidateException;

/**
 * 提现类
 * Class UserExtractController
 * @package app\api\controller\user
 */
class UserExtractController
{
    /**
     * @var UserExtractServices
     */
    protected $services;

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
        return app('json')->success($this->services->bank($uid));
    }

    /**
     * 收款前核验微信转账状态，过期失败的转账自动返还佣金。
     * @param Request $request 当前用户请求
     * @return mixed
     */
    public function checkTransfer(Request $request)
    {
        $data = $request->postMore([['order_id', '']]);
        if (isset($data['order_id']) && !is_string($data['order_id'])) throw new ValidateException('转账单号输入有误');
        validate(['order_id' => 'require|alphaNum|max:64'])->check($data);
        return app('json')->success($this->services->transferInfo((int)$request->uid(), $data['order_id'], true));
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
            ['user_name', ''],
            ['name', ''],
            ['bankname', ''],
            ['cardnum', ''],
            ['weixin', ''],
            ['qrcode_url', ''],
        ]);
        // 每10秒只能提现一次
        $uid = (int)$request->uid();
        $cacheKey = 'extract_limit_' . $uid;
        if (CacheService::has($cacheKey)) {
            return app('json')->fail('操作过于频繁，请10秒后再试');
        }
        CacheService::set($cacheKey, 1, 10);
        $extractInfo['channel_type'] = $request->getFromType();
        $extractType = Config::get('pay.extractType', []);
        if (!in_array($extractInfo['extract_type'], $extractType))
            return app('json')->fail('提现方式不存在');
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', (float)$extractInfo['money'])) return app('json')->fail('提现金额输入有误');
        if (trim((string)$extractInfo['cardnum']) !== '')
            if (!preg_match('/^([1-9]{1})(\d{15}|\d{16}|\d{18})$/', $extractInfo['cardnum']))
                return app('json')->fail('银行卡号输入有误');
        if ($extractInfo['extract_type'] == 'weixin') {
            if (trim($extractInfo['user_name']) === '') return app('json')->fail('请填写真实姓名');
        } elseif ($extractInfo['extract_type'] == 'alipay') {
            if (trim($extractInfo['alipay_code']) === '') return app('json')->fail('请输入支付宝账号');
            if (trim($extractInfo['user_name']) === '') return app('json')->fail('请填写真实姓名');
        } elseif ($extractInfo['extract_type'] == 'bank') {
            if (!$extractInfo['cardnum']) return app('json')->fail('请输入银行卡账号');
            if (!$extractInfo['bankname']) return app('json')->fail('请输入开户行信息');
        }
        if ($this->services->cash($uid, $extractInfo))
            return app('json')->success('申请提现成功');
        else
            return app('json')->fail('申请提现失败');
    }
}
