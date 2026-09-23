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
use app\services\user\UserAddressServices;

/**
 * 用户地址类
 * Class UserController
 * @package app\api\controller\store
 */
class UserAddressController
{
    /**
     * @var UserAddressServices
     */
    protected $services;

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
            return app('json')->fail('参数错误');
        }
        $info = $this->services->address((int)$id);
        if ($info['uid'] != $uid) return app('json')->fail('数据不存在');
        return app('json')->success($info);
    }

    /**
     * 地址列表
     * @param Request $request
     * @return mixed
     */
    public function addressList(Request $request)
    {
        $uid = (int)$request->uid();
        return app('json')->success($this->services->getUserAddressList($uid, 'id,real_name,phone,province,city,district,detail,is_default,city_id'));
    }

    /**
     * 设置默认地址
     * @param Request $request
     * @return mixed
     */
    public function addressDefaultSet(Request $request)
    {
        list($id) = $request->getMore([['id', 0]], true);
        if (!$id || !is_numeric($id)) return app('json')->fail('参数错误');
        $uid = (int)$request->uid();
        $res = $this->services->setDefault($uid, (int)$id);
        $province = $this->services->value(['id' => $id], 'province');
        //通过事件代理更新微信用户省份，避免 app 层直接引用 modules
        event('ChannelServiceProxy', ['endpoint' => 'wechat_mp', 'service' => 'WechatUserServices', 'action' => 'update', 'args' => [['uid' => $uid], ['province' => $province]]]);
        if (!$res)
            return app('json')->fail('地址不存在');
        else
            return app('json')->success('设置成功');
    }

    /**
     * 获取默认地址
     * @param Request $request
     * @return mixed
     */
    public function addressDefault(Request $request)
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
    public function addressEdit(Request $request)
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
        if (!isset($addressInfo['address']['province']) || !$addressInfo['address']['province'] || $addressInfo['address']['province'] == '省') return app('json')->fail('收货地址格式错误');
        if (!isset($addressInfo['address']['city']) || !$addressInfo['address']['city'] || $addressInfo['address']['city'] == '市') return app('json')->fail('收货地址格式错误或系统未完善当前地址');
        if (!isset($addressInfo['address']['district']) || !$addressInfo['address']['district'] || $addressInfo['address']['district'] == '区') return app('json')->fail('收货地址格式错误或系统未完善当前地址');
        if (!isset($addressInfo['address']['city_id']) && $addressInfo['type'] == 0) return app('json')->fail('收货地址格式错误，请重新选择');
        if (!$addressInfo['detail']) return app('json')->fail('请填写详细地址');
        $uid = (int)$request->uid();
        $res = $this->services->editAddress($uid, $addressInfo);
        if ($res) {
            //通过事件代理更新微信用户省份，避免 app 层直接引用 modules
            event('ChannelServiceProxy', ['endpoint' => 'wechat_mp', 'service' => 'WechatUserServices', 'action' => 'update', 'args' => [['uid' => $uid], ['province' => $addressInfo['address']['province']]]]);
            return app('json')->success($res['type'] == 'edit' ? '修改成功' : $res['data']);
        } else {
            return app('json')->fail('修改失败');
        }

    }

    /**
     * 删除地址
     * @param Request $request
     * @return mixed
     */
    public function addressDel(Request $request)
    {
        list($id) = $request->postMore([['id', 0]], true);
        if (!$id || !is_numeric($id)) return app('json')->fail('参数错误');
        $uid = (int)$request->uid();
        $re = $this->services->delAddress($uid, (int)$id);
        if ($re)
            return app('json')->success('删除成功');
        else
            return app('json')->fail('删除失败');
    }
}
