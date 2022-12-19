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
        return app('json')->success($this->services->bank($uid));
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
            return app('json')->fail(410114);
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', (float)$extractInfo['money'])) return app('json')->fail(410115);
        if (!$extractInfo['cardnum'] == '')
            if (!preg_match('/^([1-9]{1})(\d{15}|\d{16}|\d{18})$/', $extractInfo['cardnum']))
                return app('json')->fail(410116);
        if ($extractInfo['extract_type'] == 'alipay') {
            if (trim($extractInfo['name']) == '') return app('json')->fail(410117);
        } else if ($extractInfo['extract_type'] == 'bank') {
            if (!$extractInfo['cardnum']) return app('json')->fail(410118);
            if (!$extractInfo['bankname']) return app('json')->fail(410119);
        }
        $uid = (int)$request->uid();
        if ($this->services->cash($uid, $extractInfo))
            return app('json')->success(410120);
        else
            return app('json')->fail(410121);
    }
}
