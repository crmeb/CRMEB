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
namespace app\outapi\controller;

use think\facade\App;
use app\outapi\validate\StoreCategoryValidate;
use app\services\product\product\StoreCategoryServices;

/**
 * 商品分类控制器
 * Class StoreCategory
 * @package app\outapi\controller
 */
class StoreCategory extends AuthController
{
    /**
     * @var StoreCategoryServices
     */
    protected $services;

    /**
     * StoreCategory constructor.
     * @param App $app
     * @param StoreCategoryServices $services
     */
    public function __construct(App $app, StoreCategoryServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 分类列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['is_show', ''],
            ['pid', ''],
            ['cate_name', ''],
        ]);
        $where['pid'] = -2;
        $data = $this->services->getCategoryList($where);
        return app('json')->success($data);
    }

    /**
     * 新增分类
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['pid', 0],
            ['cate_name', ''],
            ['pic', ''],
            ['big_pic', ''],
            ['sort', 0],
            ['is_show', 0]
        ]);
        $this->validate($data, StoreCategoryValidate::class, 'save');
        $cateId = $this->services->createData($data);
        return app('json')->success(100000, ['id' => $cateId]);
    }

    /**
     * 更新分类
     * @param $id
     * @return mixed
     */
    public function update($id)
    {
        $data = $this->request->postMore([
            ['pid', 0],
            ['cate_name', ''],
            ['pic', ''],
            ['big_pic', ''],
            ['sort', 0],
            ['is_show', 0]
        ]);
        $this->validate($data, StoreCategoryValidate::class, 'save');
        $this->services->editData($id, $data);
        return app('json')->success(100001);
    }

    /**
     * 删除分类
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $this->services->del((int)$id);
        return app('json')->success(100002);
    }

    /**
     * 详情
     * @param $id
     * @return mixed
     */
    public function read($id)
    {
        $info = $this->services->getInfo((int)$id);
        return app('json')->success($info);
    }

    /**
     * 修改状态
     * @param string $id
     * @param string $is_show
     */
    public function set_show($id = '', $is_show = '')
    {
        if ( $id == '' || $is_show == '') return app('json')->fail(100100);
        $this->services->setShow((int)$id, (int)$is_show);
        return app('json')->success($is_show == 1 ? 100003 : 100004);
    }
}
