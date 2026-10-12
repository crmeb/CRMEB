<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\article;

use app\dao\article\ArticleDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;

/**
 * Class ArticleServices
 * @package app\services\article
 */
class ArticleServices extends BaseService
{
    /**
     * @var ArticleDao
     */
    protected $dao;

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
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, int $page = 0, int $limit = 0)
    {
        if (!$page && !$limit) {
            [$page, $limit] = $this->getPageValue();
        }
        //通过事件代理查询图文分类ID，避免 app 层直接引用 modules
        $_newsResults = event('ChannelServiceProxy', ['endpoint' => 'wechat_mp', 'service' => 'WechatNewsCategoryServices', 'action' => 'getNewIds', 'args' => []]);
        $where['ids'] = (is_array($_newsResults) && !empty($_newsResults) && $_newsResults[0] !== null) ? $_newsResults[0] : [];
        $list = $this->dao->getList($where, $page, $limit);
        foreach ($list as &$item) {
            $item['store_name'] = $item['storeInfo']['store_name'] ?? '';
            $item['copy_url'] = sys_config('site_url') . '/pages/extension/news_details/index?id=' . $item['id'];
            $item['copy_url_pc'] = sys_config('site_url') . '/news_detail?id=' . $item['id'];
            unset($item['content']);
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 新增编辑文章
     * @param array $data
     * @return mixed
     */
    public function save(array $data)
    {
        /** @var ArticleContentServices $articleContentService */
        $articleContentService = app()->make(ArticleContentServices::class);
        $content['content'] = htmlspecialchars($data['content']);
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
     * 获取文章详情
     * @param int $id
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function read(int $id)
    {
        // 模型属性赋值会按表字段类型回写（cid 为 varchar），(int) 强转必须放在 toArray 之后才生效，
        // 否则详情返回字符串 cid，与分类树的数字 value 不一致导致编辑页级联选择器无法回显
        $info = $this->dao->read($id)->toArray();
        $info['cid'] = (int)$info['cid'];
        // 封面在表单内是单值字符串，模型获取器已将其拆为数组，此处归一回字符串避免提交时数组写 varchar 报错
        $info['image_input'] = is_array($info['image_input']) ? ($info['image_input'][0] ?? '') : $info['image_input'];
        $info['content'] = htmlspecialchars_decode($info['content']);
        return compact('info');
    }

    /**
     * 删除文章
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
     * @param bool $search
     * @return int
     */
    public function count(array $where = [], bool $search = true): int
    {
        return $this->search($where, $search)->count();
    }

    /**
     * 获取一条数据
     * @param int $id
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getInfo(int $id)
    {
        $info = $this->dao->read($id);
        $info->visit = intval($info['visit']) + 1;
        if (!$info->save())
            throw new AdminException('请稍后查看');
        if ($info) {
            $info = $info->toArray();
            $info['visit'] = (int)$info['visit'];
            // 模型获取器已将 add_time 格式化为 Y-m-d H:i:s，此处仅截取日期部分
            $info['add_time'] = substr($info['add_time'], 0, 10);
            $info['content'] = htmlspecialchars_decode($info['content']);
        }
        return $info;
    }

    /**
     * 获取文章列表
     * @param $new_id
     * @return int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function articleList($new_id)
    {
        return $this->dao->articleLists($new_id);
    }

    /**
     * 图文详情
     * @param $new_id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function articlesList($new_id)
    {
        return $this->dao->articleContentList($new_id);
    }

    /**
     * 自定义组件-文章
     * @param $where
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/1/12
     */
    public function getThemeArticle($where)
    {
        $sort = $where['sort'] ? 'desc' : 'asc';
        switch ($where['order']) {
            case 0:
                $order = 'visit ' . $sort;
                break;
            case 1:
                $order = 'add_time ' . $sort;
                break;
            default:
                $order = 'sort desc';
                break;
        }
        if (!empty($where['ids'])) {
            $where['in_ids'] = explode(',', $where['ids']);
            $where['limit'] = 1000;
        } else {
            $where['in_ids'] = [];
        }
        $limit = (int)$where['limit'];
        unset($where['order'], $where['sort'], $where['limit'], $where['ids']);
        $list = $this->dao->getList($where, 1, $limit, $order);
        $data = [];
        foreach ($list as &$item) {
            $data[] = [
                'title' => $item['title'],
                'id' => $item['id'],
                'image' => $item['image_input'][0],
                'cid_name' => $item['catename'],
                'synopsis' => $item['synopsis'],
                'visit' => $item['visit'],
                // add_time 已由 Article 模型获取器统一格式化为 Y-m-d H:i:s 字符串
                'add_time' => $item['add_time'],
            ];
        }
        if (empty($where['in_ids'])) return $data;
        // 将$list转换为以id为键的数组
        $list = array_column($data, null, 'id');
        $data = [];
        // 遍历where中的ids，按顺序取出对应文章
        foreach ($where['in_ids'] as $id) {
            if (isset($list[$id])) {
                $data[] = $list[$id];
            }
        }
        return $data;
    }
}
