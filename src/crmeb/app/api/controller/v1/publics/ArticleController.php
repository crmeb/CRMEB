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
namespace app\api\controller\v1\publics;

use app\Request;
use app\services\article\ArticleServices;

/**
 * 文章类
 * Class ArticleController
 * @package app\api\controller\publics
 */
class ArticleController
{
    protected $services;

    public function __construct(ArticleServices $services)
    {
        $this->services = $services;
    }

    /**
     * 文章列表
     * @param Request $request
     * @param $cid
     * @return mixed
     */
    public function lst($cid)
    {
        [$page, $limit] = $this->services->getPageValue();
        $list = $this->services->cidByArticleList(['cid' => $cid], $page, $limit, "id,title,image_input,visit,from_unixtime(add_time,'%Y-%m-%d %H:%i') as add_time,synopsis,url");
        return app('json')->successful($list);
    }

    /**
     * 文章详情
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function details($id)
    {
        $info = $this->services->getInfo($id);
        return app('json')->successful($info);
    }

    /**
     * 获取热门文章
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function hot()
    {
        [$page, $limit] = $this->services->getPageValue();
        $list = $this->services->cidByArticleList(['is_hot' => 1], $page, $limit, "id,title,image_input,visit,from_unixtime(add_time,'%Y-%m-%d %H:%i') as add_time,synopsis,url");
        return app('json')->successful($list);
    }

    /**
     * 获取最新文章
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function new()
    {
        [$page, $limit] = $this->services->getPageValue();
        $list = $this->services->cidByArticleList([], $page, $limit, "id,title,image_input,visit,from_unixtime(add_time,'%Y-%m-%d %H:%i') as add_time,synopsis,url");
        return app('json')->successful($list);
    }

    /**
     * 获取顶部banner文章
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function banner()
    {
        [$page, $limit] = $this->services->getPageValue();
        $list = $this->services->cidByArticleList(['is_banner' => 1], $page, $limit, "id,title,image_input,visit,from_unixtime(add_time,'%Y-%m-%d %H:%i') as add_time,synopsis,url");
        return app('json')->successful($list);
    }
}
