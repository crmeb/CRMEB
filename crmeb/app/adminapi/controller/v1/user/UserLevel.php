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
namespace app\adminapi\controller\v1\user;

use app\adminapi\controller\AuthController;
use app\services\user\UserLevelServices;
use think\facade\App;

/**
 * 会员设置
 * Class UserLevel
 * @package app\adminapi\controller\v1\user
 */
class UserLevel extends AuthController
{

    /**
     * user constructor.
     * @param App $app
     * @param UserLevelServices $services
     */
    public function __construct(App $app, UserLevelServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /*
     * 获取添加资源表单
     * */
    public function create()
    {
        $where = $this->request->getMore(
            ['id', 0]
        );
        return app('json')->success($this->services->edit((int)$where['id']));
    }

    /*
     * 会员等级添加或者修改
     * @param $id 修改的等级id
     * @return json
     * */
    public function save()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['name', ''],
            ['is_forever', 0],
            ['money', 0],
            ['is_pay', 0],
            ['valid_date', 0],
            ['grade', 0],
            ['discount', 0],
            ['icon', ''],
            ['image', ''],
            ['is_show', ''],
            ['exp_num', 0]
        ]);
        if ($data['valid_date'] == 0) $data['is_forever'] = 1;//有效时间为0的时候就是永久
        if (!$data['name']) return app('json')->fail(400324);
        if (!$data['grade']) return app('json')->fail(400325);
        if (!$data['icon']) return app('json')->fail(400327);
        if (!$data['image']) return app('json')->fail(400328);
        if (!$data['exp_num']) return app('json')->fail(400329);
        $this->services->save((int)$data['id'], $data);
        return app('json')->success(100000);
    }

    /*
     * 获取系统设置的vip列表
     * @param int page
     * @param int limit
     * */
    public function get_system_vip_list()
    {
        $where = $this->request->getMore([
            ['page', 0],
            ['limit', 10],
            ['title', ''],
            ['is_show', ''],
        ]);
        return app('json')->success($this->services->getSytemList($where));
    }

    /*
     * 删除会员等级
     * @param int $id
     * */
    public function delete($id)
    {
        return app('json')->success($this->services->delLevel((int)$id));
    }

    /**
     * 设置会员等级显示|隐藏
     *
     * @return json
     */
    public function set_show($is_show = '', $id = '')
    {
        if ($is_show == '' || $id == '') return app('json')->fail(100100);
        return app('json')->success($this->services->setShow((int)$id, (int)$is_show));
    }

    /**
     * 等级列表快速编辑
     * field:value name:钻石会员/grade:8/discount:92.00
     * @return json
     */
    public function set_value($id)
    {
        $data = $this->request->postMore([
            ['field', ''],
            ['value', '']
        ]);
        if ($data['field'] == '' || $data['value'] == '') return app('json')->fail(100100);
        $this->services->setValue((int)$id, $data);
        return app('json')->success(100000);
    }


}
