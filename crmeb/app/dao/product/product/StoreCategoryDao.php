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

namespace app\dao\product\product;

use app\dao\BaseDao;
use app\model\product\product\StoreCategory;

/**
 * Class StoreCategoryDao
 * @package app\dao\product\product
 */
class StoreCategoryDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreCategory::class;
    }

    /**
     * 获取分类列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where)
    {
        return $this->search($where)->with('children')->order('sort desc,id desc')->select()->toArray();
    }

    /**
     *
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTierList(array $where = [])
    {
        return $this->search($where)->order('sort desc,id desc')->select()->toArray();
    }

    /**
     * 添加修改选择上级分类列表
     * @param array $where
     * @return array
     */
    public function getMenus(array $where)
    {
        return $this->search($where)->column('cate_name,id');
    }

    /**
     * 根据id获取分类
     * @param string $cateIds
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCateArray(string $cateIds)
    {
        return $this->search(['id' => $cateIds])->field('cate_name,id')->select()->toArray();
    }

    /**
     * 前端分类页面分离列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCategory()
    {
        return $this->getModel()->with('children')->where('is_show', 1)->where('pid', 0)->order('sort desc,id desc')->hidden(['add_time', 'is_show', 'sort', 'children.sort', 'children.add_time', 'children.pid', 'children.is_show'])->select()->toArray();
    }

    /**
     * 根据分类id获取上级id
     * @param array $cateId
     * @return array
     */
    public function cateIdByPid(array $cateId)
    {
        return $this->getModel()->whereIn('id', $cateId)->column('pid');
    }

    /**
     * 获取首页展示的二级分类  排序默认降序
     * @param int $limit
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function byIndexList($limit = 4, $field = 'id,cate_name,pid,pic')
    {
        return $this->getModel()->where('pid', '>', 0)->where('is_show', 1)->field($field)->order('sort DESC')->limit($limit)->select()->toArray();
    }

    /**
     * 获取一级分类和二级分类组成的集合
     * @param $cateId
     * @return mixed
     */
    public function getCateParentAndChildName(string $cateId)
    {
        return $this->getModel()->alias('c')->join('StoreCategory b', 'b.id = c.pid')
            ->where('c.id', 'IN', $cateId)->field('c.cate_name as two,b.cate_name as one,c.id')
            ->select()->toArray();
    }

    /**
     * 按照个数获取一级分类下有商品的分类ID
     * @param $page
     * @param $limit
     * @return array
     */
    public function getCid($page, $limit)
    {
        return $this->getModel()
            ->where('is_show', 1)
            ->where('pid', 0)
            ->where('id', 'in', function ($query) {
                $query->name('store_product_cate')->where('status', 1)->group('cate_pid')->field('cate_pid')->select()->toArray();
            })
            ->page($page, $limit)
            ->order('sort DESC,id DESC')
            ->select()->toArray();
    }

    /**
     * 按照个数获取一级分类下有商品的分类个数
     * @param $page
     * @param $limit
     * @return int
     */
    public function getCidCount()
    {
        return $this->getModel()
            ->where('is_show', 1)
            ->where('pid', 0)
            ->where('id', 'in', function ($query) {
                $query->name('store_product_cate')->where('status', 1)->group('cate_pid')->field('cate_pid')->select()->toArray();
            })
            ->count();
    }

    /**
     * 通过分类id 获取（自己以及下级）的所有分类
     * @param int $id
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAllById(int $id, string $field = 'id')
    {
        return $this->getModel()->where(function ($query) use ($id) {
            $query->where('id', $id)->whereOr('pid', $id);
        })->where('is_show', 1)->field($field)->select()->toArray();
    }

    /**
     * 可以搜索的获取所有二级分类
     * @param array $where
     * @param string $field
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getALlByIndex(array $where, string $field = 'id,cate_name,pid,pic', $limit = 0)
    {
        $pid = $where['pid'] ?? -1;
        return $this->getModel()->where('is_show', 1)->field($field)
            ->when(in_array($pid, [0, -1]), function ($query) use ($pid) {
                switch ($pid) {
                    case -1://所有一级
                        $query->where('pid', 0);
                        break;
                    case 0://所有二级
                        $query->where('pid', '>', 0);
                }
            })->when((int)$pid > 0, function ($query) use ($pid) {
                $query->where('pid', $pid);
            })->when(isset($where['name']) && $where['name'], function ($query) use ($where) {
                $query->whereLike('id|cate_name', '%' . $where['name'] . '%');
            })->when($limit > 0, function ($query) use ($limit) {
                $query->limit($limit);
            })->order('sort DESC')->select()->toArray();
    }
}
