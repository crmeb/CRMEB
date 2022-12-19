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
namespace app\adminapi\controller\v1\marketing\live;

use app\adminapi\controller\AuthController;
use app\services\activity\live\LiveAnchorServices;
use think\facade\App;

/**
 * 直播间主播
 * Class LiveAnchor
 * @package app\controller\admin\store
 */
class LiveAnchor extends AuthController
{
    /**
     * LiveAnchor constructor.
     * @param App $app
     * @param LiveAnchorServices $services
     */
    public function __construct(App $app, LiveAnchorServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 列表
     * @return mixed
     */
    public function list()
    {
        $where = $this->request->postMore([
            ['kerword', ''],
        ]);
        return app('json')->success($this->services->getList($where));
    }

    /**
     * 添加修改表单
     * @return mixed
     */
    public function add()
    {
        list($id) = $this->request->getMore([
            ['id', 0],
        ], true);
        return app('json')->success($this->services->add((int)$id));
    }

    /**
     * 保存标签表单数据
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['name', ''],
            ['wechat', ''],
            ['phone', ''],
            ['cover_img', '']
        ]);
        $this->validate($data, \app\adminapi\validate\marketing\LiveAnchorValidate::class, 'save');
        $res = $this->services->save((int)$data['id'], $data);
        if ($res === true) {
            return app('json')->success(100000, ['auth' => false]);
        }else{
            return app('json')->fail(100006);
        }
    }

    /**
     * 删除
     * @return mixed
     * @throws \Exception
     */
    public function delete()
    {
        list($id) = $this->request->getMore([
            ['id', 0],
        ], true);
        if (!$id) return app('json')->fail(100100);
        $this->services->delAnchor((int)$id);
        return app('json')->success(100002);
    }

    /**
     * 设置会员等级显示|隐藏
     * @param string $id
     * @param string $is_show
     * @return mixed
     */
    public function setShow($id = '', $is_show = '')
    {
        if ($is_show == '' || $id == '') return app('json')->fail(100100);
        $this->services->setShow((int)$id, (int)$is_show);
        return app('json')->success(100014);
    }

    /**
     * 同步主播
     * @return mixed
     */
    public function syncAnchor()
    {
        $this->services->syncAnchor();
        return app('json')->success(100038);
    }
}
