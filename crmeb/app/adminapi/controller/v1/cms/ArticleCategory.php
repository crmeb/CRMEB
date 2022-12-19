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
namespace app\adminapi\controller\v1\cms;

use app\adminapi\controller\AuthController;
use app\services\article\ArticleCategoryServices;
use crmeb\services\CacheService;
use think\facade\App;

/**
 * 文章分类管理
 * Class ArticleCategory
 * @package app\adminapi\controller\v1\cms
 */
class ArticleCategory extends AuthController
{
    /**
     * @var ArticleCategoryServices
     */
    protected $service;

    /**
     * ArticleCategory constructor.
     * @param App $app
     * @param ArticleCategoryServices $service
     */
    public function __construct(App $app, ArticleCategoryServices $service)
    {
        parent::__construct($app);
        $this->service = $service;
    }

    /**
     * 获取分类列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['status', ''],
            ['title', ''],
            ['type', 0]
        ]);
        $type = $where['type'];
        unset($where['type']);
        $data = $this->service->getList($where);
        if ($type == 1) $data = $data['list'];
        return app('json')->success($data);
    }

    /**
     * 创建新增表单
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create()
    {
        return app('json')->success($this->service->createForm(0));
    }

    /**
     * 保存新建分类
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['title', ''],
            ['pid', 0],
            ['intr', ''],
            ['image', ''],
            ['sort', 0],
            ['status', 0]
        ]);
        if (!$data['title']) {
            return app('json')->fail(400100);
        }
        $data['add_time'] = time();
        $this->service->save($data);
        CacheService::delete('ARTICLE_CATEGORY');
        return app('json')->success(100021);
    }

    /**
     * 创建修改表单
     * @param int $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function edit($id = 0)
    {
        if (!$id) return app('json')->fail(100100);
        return app('json')->success($this->service->createForm($id));
    }

    /**
     * 保存修改分类
     * @param $id
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function update($id)
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['title', ''],
            ['pid', 0],
            ['intr', ''],
            ['image', ''],
            ['sort', 0],
            ['status', 0]
        ]);
        $this->service->update($data);
        CacheService::delete('ARTICLE_CATEGORY');
        return app('json')->success(100001);
    }

    /**
     * 删除文章分类
     * @param $id
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete($id)
    {
        if (!$id) return app('json')->fail(100100);
        $this->service->del($id);
        CacheService::delete('ARTICLE_CATEGORY');
        return app('json')->success(100002);
    }

    /**
     * 修改文章分类状态
     * @param int $id
     * @param int $status
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function set_status($id, $status)
    {
        if ($status == '' || $id == 0) return app('json')->fail(100100);
        $this->service->setStatus($id, $status);
        CacheService::delete('ARTICLE_CATEGORY');
        return app('json')->success(100014);
    }

    /**
     * 获取文章分类
     * @return mixed
     */
    public function categoryList()
    {
        return app('json')->success($this->service->getArticleTwoCategory());
    }

    /**
     * 树形列表
     * @return mixed
     */
    public function getTreeList()
    {
        $list = $this->service->getTreeList();
        return app('json')->success($list);
    }
}
