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

namespace app\services\article;

use app\dao\article\ArticleDao;
use app\services\BaseServices;
use app\services\wechat\WechatNewsCategoryServices;
use crmeb\exceptions\AdminException;

/**
 * Class ArticleServices
 * @package app\services\article
 * @method cidByArticleList(array $where, int $page, int $limit, string $field)
 */
class ArticleServices extends BaseServices
{
    /**
     * ArticleServices constructor.
     * @param ArticleDao $dao
     */
    public function __construct(ArticleDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取列表
     * @param array $where
     * @return array
     */
    public function getList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        /** @var WechatNewsCategoryServices $services */
        $services = app()->make(WechatNewsCategoryServices::class);
        $where['ids'] = $services->getNewIds();
        $list = $this->dao->getList($where, $page, $limit);
        foreach ($list as &$item) {
            $item['store_name'] = $item['storeInfo']['store_name'];
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 新增编辑文章
     * @param array $data
     */
    public function save(array $data)
    {
        /** @var ArticleContentServices $articleContentService */
        $articleContentService = app()->make(ArticleContentServices::class);
        $content['content'] = $data['content'];
        $id = $data['id'];
        unset($data['content'], $data['id']);
        $info = $this->transaction(function () use ($id, $data, $articleContentService, $content) {
            if ($id) {
                $info = $this->dao->update($id, $data);
                $content['nid'] = $id;
                $res = $info && $articleContentService->update($id, $content, 'nid');
            } else {
                unset($data['id']);
                $data['add_time'] = time();
                $info = $this->dao->save($data);
                $content['nid'] = $info->id;
                $res = $info && $articleContentService->save($content);
            }
            if (!$res) {
                throw new AdminException('保存失败');
            } else {
                return $info;
            }
        });
        return $info;
    }

    /**
     * 获取商品详情
     * @param $id
     * @return array
     */
    public function read(int $id)
    {
        $info = $this->dao->read($id);
        $info['cid'] = (int)$info['cid'];
        return compact('info');
    }

    /**
     * 删除商品
     * @param int $id
     */
    public function del(int $id)
    {
        /** @var ArticleContentServices $articleContentService */
        $articleContentService = app()->make(ArticleContentServices::class);
        $this->transaction(function () use ($id, $articleContentService) {
            $res = $this->dao->delete($id);
            $res = $res && $articleContentService->del($id);
            if (!$res) {
                throw new AdminException('删除失败');
            }
        });
    }

    /**
     * 文章关联商品
     * @param int $id
     * @param int $product_id
     * @return mixed
     */
    public function bindProduct(int $id, int $product_id = 0)
    {
        return $this->dao->update($id, ['product_id' => $product_id]);
    }

    /**
     * 获取数量
     * @param array $where
     * @return int
     */
    public function count(array $where)
    {
        return $this->dao->count($where);
    }

    /**获取一条数据
     * @param int $id
     * @return mixed
     */
    public function getInfo(int $id)
    {
        $info = $this->dao->read($id);
        $info->visit = $info['visit'] + 1;
        if (!$info->save())
            throw new AdminException('请稍后查看');
        if ($info) {
            $info = $info->toArray();
            $info['visit'] = (int)$info['visit'];
            $info['add_time'] = date('Y-m-d', $info['add_time']);
        }
        return $info;
    }

    /**
     * 获取文章列表
     * @param $new_id
     * @return int
     */
    public function articleList($new_id)
    {
        return $this->dao->articleLists($new_id);
    }

    /**图文详情
     * @param $new_id
     * @return mixed
     */
    public function articlesList($new_id)
    {
        return $this->dao->articleContentList($new_id);
    }
}
