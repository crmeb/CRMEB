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
namespace app\dao\article;

use app\dao\BaseDao;
use app\model\article\Article;
use think\exception\ValidateException;

/**
 * 文章dao
 * Class ArticleDao
 * @package app\dao\article
 */
class ArticleDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return Article::class;
    }

    /**
     * 文章搜索
     * @param array $where
     * @param bool $search
     * @return \crmeb\basic\BaseModel
     * @throws \ReflectionException
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/20
     */
    public function search(array $where = [], bool $search = false)
    {
        return parent::search($where, $search)->when(isset($where['ids']) && count($where['ids']), function ($query) use ($where) {
            $query->whereNotIn('id', $where['ids']);
        });
    }

    /**
     * 获取文章列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, int $page, int $limit)
    {
        return $this->search($where)->with(['content', 'storeInfo', 'cateName'])->page($page, $limit)->order('sort desc,id desc')->select()->toArray();
    }

    /**
     * 获取一条数据
     * @param $id
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function read($id)
    {
        $data = $this->search()->with(['content', 'storeInfo', 'cateName'])->find($id);
        if (!$data) throw new ValidateException('文章不存在');
        $data['store_info'] = $data['storeInfo'];
        return $data;
    }

    /**
     * 新闻分类下的文章
     * @param $new_id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function articleLists($new_id)
    {
        return $this->getModel()->where('hide', 0)->where('id', 'in', $new_id)->select();
    }

    /**
     * 图文详情
     * @param $new_id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function articleContentList($new_id)
    {
        return $this->getModel()->where('hide', 0)->where('id', 'in', $new_id)->with('content')->select();
    }
}
