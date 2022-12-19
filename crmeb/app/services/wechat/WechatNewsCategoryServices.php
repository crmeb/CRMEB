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

namespace app\services\wechat;

use app\services\BaseServices;
use app\dao\wechat\WechatNewsCategoryDao;
use app\services\article\ArticleServices;

/**
 *
 * Class UserWechatuserServices
 * @package app\services\user
 * @method delete($id, ?string $key = null)  删除
 * @method update($id, array $data, ?string $key = null) 更新数据
 * @method save(array $data) 插入数据
 * @method get(int $id, ?array $field = []) 获取一条数据
 */
class WechatNewsCategoryServices extends BaseServices
{

    /**
     * UserWechatuserServices constructor.
     * @param WechatNewsCategoryDao $dao
     */
    public function __construct(WechatNewsCategoryDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取配置分类
     * @param array $where
     * @return array
     */
    public function getAll($where = array())
    {
        [$page, $limit] = $this->getPageValue();
        $model = $this->dao->getNewCtae($where);
        $count = $model->count();
        $list = $model->page($page, $limit)
            ->select()
            ->each(function ($item) {
                /** @var ArticleServices $services */
                $services = app()->make(ArticleServices::class);
                $new = $services->articleList($item['new_id']);
                if ($new) $new = $new->toArray();
                $item['new'] = $new;
            });
        return compact('count', 'list');
    }

    /**
     * 获取一条图文
     * @param int $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getWechatNewsItem($id = 0)
    {
        if (!$id) return [];
        $list = $this->dao->getOne(['id' => $id, 'status' => 1], 'cate_name as title,new_id');
        if ($list) {
            $list = $list->toArray();
            /** @var ArticleServices $services */
            $services = app()->make(ArticleServices::class);
            $new = $services->articleList($list['new_id']);
            if ($new) $new = $new->toArray();
            $list['new'] = $new;
        }
        return $list;

    }

    /**
     * 发送客服消息选择文章列表
     * @param $where
     * @return array
     */
    public function list($where)
    {
        $list = $this->dao->getNewCtae($where)
            ->page((int)$where['page'], (int)$where['limit'])
            ->select()
            ->each(function ($item) {
                /** @var ArticleServices $services */
                $services = app()->make(ArticleServices::class);
                $item['new'] = $services->articleList($item['new_id']);
            });
        return ['list' => $list];
    }

    /**整理图文资源
     * @param $wechatNews
     * @return bool
     */
    public function wechatPush($wechatNews)
    {
        /** @var WechatReplyServices $services */
        $services = app()->make(WechatReplyServices::class);
        return $services->tidyNews($wechatNews);
    }

    /**发送的用户
     * @param $user_ids
     * @param $column
     * @param $key
     * @return array
     */
    public function getWechatUser($user_ids, $column, $key)
    {
        /** @var WechatUserServices $services */
        $services = app()->make(WechatUserServices::class);
        return $services->getColumnUser($user_ids, $column, $key);
    }

    /**
     * 获取文章id
     * @return array
     */
    public function getNewIds()
    {
        return $this->dao->getColumn([], 'new_id');
    }
}
