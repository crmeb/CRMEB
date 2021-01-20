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
namespace app\adminapi\controller\v1\order;

use app\adminapi\controller\AuthController;
use app\services\order\DeliveryServiceServices;
use app\services\user\UserServices;
use app\services\user\UserWechatuserServices;
use crmeb\exceptions\AdminException;
use think\facade\App;

/**
 * 客服管理
 * Class StoreService
 * @package app\admin\controller\store
 */
class DeliveryService extends AuthController
{
    /**
     * DeliveryService constructor.
     * @param App $app
     * @param DeliveryServiceServices $services
     */
    public function __construct(App $app, DeliveryServiceServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return app('json')->success($this->services->getServiceList([]));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create(UserWechatuserServices $services)
    {
        $where = $this->request->getMore([
            ['nickname', ''],
            ['data', '', '', 'time'],
            ['type', '', '', 'user_type'],
        ]);
        [$list, $count] = $services->getWhereUserList($where, 'u.nickname,u.uid,u.avatar as headimgurl,w.subscribe,w.province,w.country,w.city,w.sex');
        return app('json')->success(compact('list', 'count'));
    }

    /**
     * 添加客服表单
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function add()
    {
        return app('json')->success($this->services->create());
    }

    /*
     * 保存新建的资源
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['image', ''],
            ['uid', 0],
            ['avatar', ''],
            ['phone', ''],
            ['nickname', ''],
            ['status', 1],
        ]);
        if ($data['image'] == '') return app('json')->fail('请选择用户');
        $data['uid'] = $data['image']['uid'];
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        $userInfo = $userService->get($data['uid']);
        if ($data['phone'] == '') {
            if (!$userInfo['phone']) {
                throw new AdminException('该用户没有绑定手机号，请手动填写');
            } else {
                $data['phone'] = $userInfo['phone'];
            }
        } else {
            if (!check_phone($data['phone'])) {
                return app('json')->fail('请输入正确的手机号!');
            }
        }
        if ($data['nickname'] == '') $data['nickname'] = $userInfo['nickname'];
        $data['avatar'] = $data['image']['image'];
        if ($this->services->count(['uid' => $data['uid']])) {
            return app('json')->fail('配送员已存在!');
        }
        if ($this->services->count(['phone' => $data['phone']])) {
            return app('json')->fail('同一个手机号的配送员只能添加一个!');
        }
        unset($data['image']);
        $data['add_time'] = time();
        $res = $this->services->save($data);
        if ($res) {
            return app('json')->success('配送员添加成功');
        } else {
            return app('json')->fail('配送员添加失败，请稍后再试');
        }
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        return app('json')->success($this->services->edit((int)$id));
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function update($id)
    {
        $data = $this->request->postMore([
            ['avatar', ''],
            ['nickname', ''],
            ['phone', ''],
            ['status', 1],
        ]);
        $delivery = $this->services->get((int)$id);
        if (!$delivery) {
            return app('json')->fail('数据不存在');
        }
        if ($data["nickname"] == '') {
            return app('json')->fail("配送员名称不能为空！");
        }
        if (!$data['phone']) {
            return app('json')->fail("手机号不能为空！");
        }
        if (!check_phone($data['phone'])) {
            return app('json')->fail('请输入正确的手机号!');
        }
        if ($delivery['phone'] != $data['phone'] && $this->services->count(['phone' => $data['phone']])) {
            return app('json')->fail('同一个手机号的配送员只能添加一个!');
        }
        $this->services->update($id, $data);
        return app('json')->success('修改成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$this->services->delete($id))
            return app('json')->fail('删除失败,请稍候再试!');
        else
            return app('json')->success('删除成功!');
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        if ($status == '' || $id == 0) return app('json')->fail('参数错误');
        $this->services->update($id, ['status' => $status]);
        return app('json')->success($status == 0 ? '隐藏成功' : '显示成功');
    }

    /**
     *获取所有配送员列表
     */
    public function get_delivery_list()
    {
        $data = $this->services->getDeliveryList();
        return app('json')->success($data);
    }

}
