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
use app\services\article\ArticleServices;
use think\facade\App;

/**
 * 文章管理
 * Class Article
 * @package app\adminapi\controller\v1\cms
 */
class Article extends AuthController
{
    /**
     * @var ArticleServices
     */
    protected $service;

    /**
     * Article constructor.
     * @param App $app
     * @param ArticleServices $service
     */
    public function __construct(App $app, ArticleServices $service)
    {
        parent::__construct($app);
        $this->service = $service;
    }

    /**
     * 获取列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['title', ''],
            ['pid', 0, '', 'cid'],
        ]);
        $data = $this->service->getList($where);
        return app('json')->success($data);
    }

    /**
     * 保存文章数据
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['cid', ''],
            ['title', ''],
            ['author', ''],
            ['image_input', ''],
            ['content', ''],
            ['synopsis', 0],
            ['share_title', ''],
            ['share_synopsis', ''],
            ['sort', 0],
            ['url', ''],
            ['is_banner', 0],
            ['is_hot', 0],
            ['status', 1]
        ]);
        $this->service->save($data);
        return app('json')->success(100021);
    }

    /**
     * 获取单个文章数据
     * @param int $id
     * @return mixed
     */
    public function read($id = 0)
    {
        if (!$id) return app('json')->fail(100100);
        $info = $this->service->read($id);
        return app('json')->success($info);
    }

    /**
     * 删除文章
     * @param int $id
     * @return mixed
     */
    public function delete($id = 0)
    {
        if (!$id) return app('json')->fail(100100);
        $this->service->del($id);
        return app('json')->success(100002);
    }

    /**
     * 文章关联商品
     * @param int $id
     * @return mixed
     */
    public function relation($id)
    {
        if (!$id) return app('json')->fail(100100);
        list($product_id) = $this->request->postMore([
            ['product_id', 0]
        ], true);
        $res = $this->service->bindProduct($id, $product_id);
        if ($res) {
            return app('json')->success(400300);
        } else {
            return app('json')->fail(400301);
        }
    }

    /**
     * 取消商品关联
     * @param int $id
     * @return mixed
     */
    public function unrelation($id)
    {
        if (!$id) return app('json')->fail(100100);
        $res = $this->service->bindProduct($id);
        if ($res) {
            return app('json')->success(100019);
        } else {
            return app('json')->fail(100020);
        }
    }
}
