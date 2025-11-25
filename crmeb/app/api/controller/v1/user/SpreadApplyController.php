<?php

namespace app\api\controller\v1\user;

use app\Request;
use app\services\agent\SpreadApplyServices;
use crmeb\services\CacheService;

class SpreadApplyController
{
    public function __construct(SpreadApplyServices $services)
    {
        $this->services = $services;
    }

    public function applyInfo(Request $request)
    {
        $uid = $request->uid();
        $data = $this->services->applyInfo($uid);
        return app('json')->success($data);
    }

    public function applyPromoter(Request $request, $id)
    {
        $data = $request->postMore([
            ['uid', 0],
            ['nickname', ''],
            ['real_name', ''],
            ['phone', ''],
            ['content', ''],
            ['code', 0]
        ]);
        $data['uid'] = $request->uid();
        $userInfo = $request->user();
        $verifyCode = CacheService::get('code_' . $data['phone']);
        if (!$verifyCode) return app('json')->fail('请先获取验证码');
        if ($verifyCode != $data['code']) return app('json')->fail('验证码错误');
        unset($data['code']);
        $id = $this->services->applyPromoter($data, $id, $userInfo);
        return app('json')->success('申请成功', ['id' => $id]);
    }
}
