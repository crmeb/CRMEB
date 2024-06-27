<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\api\controller\v1\publics;

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
     * @param $cid
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lst($cid)
    {
        if ($cid == 0) {
            $where = ['is_hot' => 1];
        } else {
            $where = ['cid' => $cid];
        }
        [$page, $limit] = $this->services->getPageValue();
        $list = $this->services->getList($where, $page, $limit)['list'];
        foreach ($list as &$item){
            $item['add_time'] = date('Y-m-d H:i', $item['add_time']);
        }
        return app('json')->success($list);
    }

    /**
     * 文章详情
     * @param $id
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function details($id)
    {
        $info = $this->services->getInfo($id);
        return app('json')->success($info);
    }

    /**
     * 获取热门文章
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function hot()
    {
        [$page, $limit] = $this->services->getPageValue();
        $list = $this->services->getList(['is_hot' => 1], $page, $limit)['list'];
        foreach ($list as &$item){
            $item['add_time'] = date('Y-m-d H:i', $item['add_time']);
        }
        return app('json')->success($list);
    }

    /**
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function new()
    {
        [$page, $limit] = $this->services->getPageValue();
        $list = $this->services->getList([], $page, $limit)['list'];
        foreach ($list as &$item){
            $item['add_time'] = date('Y-m-d H:i', $item['add_time']);
        }
        return app('json')->success($list);
    }

    /**
     * 获取顶部banner文章
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function banner()
    {
        [$page, $limit] = $this->services->getPageValue();
        $list = $this->services->getList(['is_banner' => 1], $page, $limit)['list'];
        foreach ($list as &$item){
            $item['add_time'] = date('Y-m-d H:i', $item['add_time']);
        }
        return app('json')->success($list);
    }
}
