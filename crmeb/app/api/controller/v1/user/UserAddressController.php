<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\api\controller\v1\user;

use app\Request;
use app\services\user\UserAddressServices;
use app\services\wechat\WechatUserServices;

/**
 * 用户地址类
 * Class UserController
 * @package app\api\controller\store
 */
class UserAddressController
{
    protected $services = NUll;

    /**
     * UserController constructor.
     * @param UserAddressServices $services
     */
    public function __construct(UserAddressServices $services)
    {
        $this->services = $services;
    }

    /**
     * 地址 获取单个
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function address(Request $request, $id)
    {
        $uid = (int)$request->uid();
        if (!$id) {
            return app('json')->fail(100100);
        }
        $info = $this->services->address((int)$id);
        if ($info['uid'] != $uid) return app('json')->fail(100026);
        return app('json')->success($info);
    }

    /**
     * 地址列表
     * @param Request $request
     * @return mixed
     */
    public function address_list(Request $request)
    {
        $uid = (int)$request->uid();
        return app('json')->success($this->services->getUserAddressList($uid, 'id,real_name,phone,province,city,district,detail,is_default,city_id'));
    }

    /**
     * 设置默认地址
     * @param Request $request
     * @return mixed
     */
    public function address_default_set(Request $request)
    {
        list($id) = $request->getMore([['id', 0]], true);
        if (!$id || !is_numeric($id)) return app('json')->fail(100100);
        $uid = (int)$request->uid();
        $res = $this->services->setDefault($uid, (int)$id);
        $province = $this->services->value(['id' => $id], 'province');
        app()->make(WechatUserServices::class)->update(['uid' => $uid], ['province' => $province]);
        if (!$res)
            return app('json')->fail(410150);
        else
            return app('json')->success(100014);
    }

    /**
     * 获取默认地址
     * @param Request $request
     * @return mixed
     */
    public function address_default(Request $request)
    {
        $uid = (int)$request->uid();
        $defaultAddress = $this->services->getUserDefaultAddress($uid, 'id,real_name,phone,province,city,district,detail,is_default');
        if ($defaultAddress) {
            $defaultAddress = $defaultAddress->toArray();
            return app('json')->success($defaultAddress);
        }
        return app('json')->success('empty', []);
    }

    /**
     * 修改 添加地址
     * @param Request $request
     * @return mixed
     */
    public function address_edit(Request $request)
    {
        $addressInfo = $request->postMore([
            ['address', []],
            ['is_default', false],
            ['real_name', ''],
            ['post_code', ''],
            ['phone', ''],
            ['detail', ''],
            [['id', 'd'], 0],
            [['type', 'd'], 0]
        ]);
        if (!isset($addressInfo['address']['province']) || !$addressInfo['address']['province'] || $addressInfo['address']['province'] == '省') return app('json')->fail(410151);
        if (!isset($addressInfo['address']['city']) || !$addressInfo['address']['city'] || $addressInfo['address']['city'] == '市') return app('json')->fail(410152);
        if (!isset($addressInfo['address']['district']) || !$addressInfo['address']['district'] || $addressInfo['address']['district'] == '区') return app('json')->fail(410152);
        if (!isset($addressInfo['address']['city_id']) && $addressInfo['type'] == 0) return app('json')->fail(410153);
        if (!$addressInfo['detail']) return app('json')->fail(410154);
        $uid = (int)$request->uid();
        $res = $this->services->editAddress($uid, $addressInfo);
        if ($res) {
            app()->make(WechatUserServices::class)->update(['uid' => $uid], ['province' => $addressInfo['address']['province']]);
            return app('json')->success($res['type'] == 'edit' ? 100001 : $res['data']);
        } else {
            return app('json')->fail(100007);
        }

    }

    /**
     * 删除地址
     * @param Request $request
     * @return mixed
     */
    public function address_del(Request $request)
    {
        list($id) = $request->postMore([['id', 0]], true);
        if (!$id || !is_numeric($id)) return app('json')->fail(100100);
        $uid = (int)$request->uid();
        $re = $this->services->delAddress($uid, (int)$id);
        if ($re)
            return app('json')->success(100002);
        else
            return app('json')->fail(100008);
    }
}
