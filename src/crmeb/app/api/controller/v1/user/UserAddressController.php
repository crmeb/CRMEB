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
use app\services\user\UserAddressServices;

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
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function address(Request $request, $id)
    {
        if (!$id) {
            app('json')->fail('缺少参数');
        }
        return app('json')->successful($this->services->address((int)$id));
    }

    /**
     * 地址列表
     * @param Request $request
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function address_list(Request $request)
    {
        $uid = (int)$request->uid();
        return app('json')->successful($this->services->getUserAddressList($uid, 'id,real_name,phone,province,city,district,detail,is_default,city_id'));
    }

    /**
     * 设置默认地址
     *
     * @param Request $request
     * @return mixed
     */
    public function address_default_set(Request $request)
    {
        list($id) = $request->getMore([['id', 0]], true);
        if (!$id || !is_numeric($id)) return app('json')->fail('参数错误!');
        $uid = (int)$request->uid();
        $res = $this->services->setDefault($uid, (int)$id);
        if (!$res)
            return app('json')->fail('地址不存在!');
        else
            return app('json')->successful();
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
            return app('json')->successful('ok', $defaultAddress);
        }
        return app('json')->successful('empty', []);
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
        if (!isset($addressInfo['address']['province'])) return app('json')->fail('收货地址格式错误!');
        if (!isset($addressInfo['address']['city'])) return app('json')->fail('收货地址格式错误!');
        if (!isset($addressInfo['address']['district'])) return app('json')->fail('收货地址格式错误!');
        if (!isset($addressInfo['address']['city_id']) && $addressInfo['type'] == 0) {
            return app('json')->fail('收货地址格式错误!请重新选择!');
        }
        $uid = (int)$request->uid();
        $re = $this->services->editAddress($uid, $addressInfo);
        if ($re) {
            return app('json')->success($re['type'] == 'edit' ? $re['msg'] : $re['data']);
        } else {
            return app('json')->fail('处理失败');
        }

    }

    /**
     * 删除地址
     *
     * @param Request $request
     * @return mixed
     */
    public function address_del(Request $request)
    {
        list($id) = $request->postMore([['id', 0]], true);
        if (!$id || !is_numeric($id)) return app('json')->fail('参数错误!');
        $uid = (int)$request->uid();
        $re = $this->services->delAddress($uid, (int)$id);
        if ($re)
            return app('json')->successful();
        else
            return app('json')->fail('删除地址失败!');
    }
}
