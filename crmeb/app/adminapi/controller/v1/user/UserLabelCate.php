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
declare (strict_types=1);

namespace app\adminapi\controller\v1\user;

use app\adminapi\controller\AuthController;
use app\services\user\UserLabelCateServices;
use app\adminapi\validate\user\UserLabeCateValidata;
use app\services\user\UserLabelServices;
use think\facade\App;
use app\Request;

/**
 * Class UserLabelCate
 * @package app\adminapi\controller\v1\user
 */
class UserLabelCate extends AuthController
{
    /**
     * UserLabelCate constructor.
     * @param App $app
     * @param UserLabelCateServices $services
     */
    public function __construct(App $app, UserLabelCateServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $where = $request->postMore([
            ['name', '']
        ]);
        $where['type'] = 0;
        return app('json')->success($this->services->getLabelList($where));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        return app('json')->success($this->services->createForm());
    }

    /**
     * 保存新建的资源
     *
     * @param Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = $request->postMore([
            ['name', ''],
            ['sort', 0]
        ]);

        $this->validate($data, UserLabeCateValidata::class);

        if ($this->services->count(['name' => $data['name']])) {
            return app('json')->fail(400101);
        }
        $data['type'] = 0;
        if ($this->services->save($data)) {
            $this->services->deleteCateCache();
            return app('json')->success(100000);
        } else {
            return app('json')->fail(100006);
        }
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        if (!$id) {
            return app('json')->fail(100100);
        }
        $info = $this->services->get($id);
        if (!$info) {
            return app('json')->fail(100026);
        }
        return app('json')->success($info->toArray());
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        return app('json')->success($this->services->updateForm((int)$id));
    }

    /**
     * 保存更新的资源
     *
     * @param Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->postMore([
            ['name', ''],
            ['sort', 0],
        ]);

        $this->validate($data, UserLabeCateValidata::class);

        if ($this->services->update($id, $data)) {
            $this->services->deleteCateCache();
            return app('json')->success(100001);
        } else {
            return app('json')->fail(100007);
        }
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id || !($info = $this->services->get($id))) {
            return app('json')->fail(100026);
        }
        /** @var $labelService $labelservice */
        $labelService = app()->make(UserLabelServices::class);
        $count = $labelService->getCount(['label_cate' => $id]);
        if($count) return app('json')->fail(400323);
        if ($info->delete()) {
            $this->services->deleteCateCache();
            return app('json')->success(100002);
        } else {
            return app('json')->fail(100008);
        }
    }

    /**
     * 获取用户标签分类全部
     * @return mixed
     */
    public function getAll()
    {
        return app('json')->success($this->services->getLabelCateAll());
    }
}
