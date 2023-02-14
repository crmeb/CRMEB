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

namespace app\services\product\product;


use app\dao\product\product\StoreCategoryDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\FormBuilder as Form;
use crmeb\utils\Arr;
use think\facade\Route as Url;

/**
 * Class StoreCategoryService
 * @package app\services\product\product
 * @method cateIdByPid(array $cateId) 根据分类id获取上级id
 * @method byIndexList(int $limit, ?string $field) 根据分类id获取上级id
 * @method getCateParentAndChildName(string $cateIds) 获取一级分类和二级分类组成的集合
 * @method value(array $where, string $field) 获取某个字段的值
 * @method getColumn(array $where, string $field, string $key = '') 获取某个字段数组
 */
class StoreCategoryServices extends BaseServices
{
    public function __construct(StoreCategoryDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取分类列表
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList($where)
    {
        $list = $this->dao->getTierList($where);
        if (!empty($list) && ($where['cate_name'] !== '' || $where['pid'] !== '')) {
            $pids = Arr::getUniqueKey($list, 'pid');
            $parentList = $this->dao->getTierList(['id' => $pids]);
            $list = array_merge($list, $parentList);
            foreach ($list as $key => $item) {
                $arr = $item;
                unset($list[$key]);
                if (!in_array($arr, $list)) {
                    $list[] = $arr;
                }
            }
        }
        $list = get_tree_children($list);
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 商品分类搜索下拉
     * @param string $show
     * @param string $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTierList($show = '', $type = 0)
    {
        $where = [];
        if ($show !== '') $where['is_show'] = $show;
        if (!$type) $where['pid'] = 0;
        return sort_list_tier($this->dao->getTierList($where));
    }

    /**
     * 获取分类cascader
     * @param string $show
     * @param int $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function cascaderList($show = '', $type = 0)
    {
        $where = [];
        if ($show !== '') $where['is_show'] = $show;
        if (!$type) $where['pid'] = 0;
        $data = get_tree_children($this->dao->getTierList($where, ['id as value', 'cate_name as label', 'pid']), 'children', 'value');
        foreach ($data as &$item) {
            if (!isset($item['children'])) {
                $item['disabled'] = true;
            }
        }
        return $data;
    }

    /**
     * 设置分类状态
     * @param $id
     * @param $is_show
     */
    public function setShow(int $id, int $is_show)
    {
        $res = $this->dao->update($id, ['is_show' => $is_show]);
        $res = $res && $this->dao->update($id, ['is_show' => $is_show], 'pid');
        if (!$res) {
            throw new AdminException(100005);
        } else {
            $this->cacheDriver()->clear();
            $this->cacheDriver()->set('category_version', uniqid());
        }
    }

    /**
     * 创建新增表单
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm()
    {
        return create_form('添加分类', $this->form(), Url::buildUrl('/product/category'), 'POST');
    }

    /**
     * 创建编辑表单
     * @param $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function editForm(int $id)
    {
        $info = $this->dao->get($id);
        return create_form('编辑分类', $this->form($info), $this->url('/product/category/' . $id), 'PUT');
    }

    /**
     * 生成表单参数
     * @param array $info
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function form($info = [])
    {
        if (isset($info['pid'])) {
            $f[] = Form::select('pid', '上级分类', (int)($info['pid'] ?? ''))->setOptions($this->menus($info['pid']))->filterable(1);
        } else {
            $f[] = Form::select('pid', '上级分类', (int)($info['pid'] ?? ''))->setOptions($this->menus())->filterable(1);
        }
        $f[] = Form::input('cate_name', '分类名称', $info['cate_name'] ?? '')->maxlength(8)->required();
        $f[] = Form::frameImage('pic', '分类图标(180*180)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'pic')), $info['pic'] ?? '')->icon('ios-add')->width('950px')->height('505px')->modal(['footer-hide' => true]);
        $f[] = Form::frameImage('big_pic', '分类大图(468*340)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'big_pic')), $info['big_pic'] ?? '')->icon('ios-add')->width('950px')->height('505px')->modal(['footer-hide' => true]);
        $f[] = Form::number('sort', '排序', (int)($info['sort'] ?? 0))->min(0)->precision(0);
        $f[] = Form::radio('is_show', '状态', $info['is_show'] ?? 1)->options([['label' => '显示', 'value' => 1], ['label' => '隐藏', 'value' => 0]]);
        return $f;
    }

    /**
     * 获取一级分类组合数据
     * @param string $pid
     * @return array[]
     */
    public function menus($pid = '')
    {
        $list = $this->dao->getMenus(['pid' => 0]);
        $menus = [['value' => 0, 'label' => '顶级菜单']];
        if ($pid === 0) return $menus;
        if ($pid != '') $menus = [];
        foreach ($list as $menu) {
            $menus[] = ['value' => $menu['id'], 'label' => $menu['cate_name']];
        }
        return $menus;
    }

    /**
     * 保存新增数据
     * @param $data
     */
    public function createData($data)
    {
        if (!$data['cate_name']) {
            throw new AdminException(400100);
        }

        if ($this->dao->getOne(['cate_name' => $data['cate_name'], 'pid' => $data['pid']])) {
            throw new AdminException(400101);
        }

        $parent = $this->dao->getOne(['id' => $data['pid']]);
        if ($data['pid'] && (!$parent || $parent['pid'] > 0)) {
            throw new AdminException(400740);
        }

        $data['add_time'] = time();
        $res = $this->dao->save($data);
        if (!$res) throw new AdminException(100006);

        $this->cacheDriver()->clear();
        $this->cacheDriver()->set('category_version', uniqid());

        return (int)$res->id;
    }

    /**
     * 保存修改数据
     * @param $id
     * @param $data
     */
    public function editData($id, $data)
    {
        if (!$data['cate_name']) {
            throw new AdminException(400100);
        }

        $parent = $this->dao->getOne(['id' => $data['pid']]);
        if ($parent && $parent['pid'] > 0) {
            throw new AdminException(400740);
        }

        $cate = $this->dao->getOne(['cate_name' => $data['cate_name'], 'pid' => $data['pid']]);
        if ($cate && $cate['id'] != $id) {
            throw new AdminException(400101);
        }
        $this->transaction(function () use ($id, $data) {
            $res = $this->dao->update($id, $data);
            /** @var StoreProductCateServices $productCate */
            $productCate = app()->make(StoreProductCateServices::class);
            $res = $res && $productCate->update(['cate_id' => $id], ['cate_pid' => $data['pid']]);
            if (!$res) throw new AdminException(100007);
        });

        $this->cacheDriver()->clear();
        $this->cacheDriver()->set('category_version', uniqid());
    }

    /**
     * 删除数据
     * @param int $id
     */
    public function del(int $id)
    {
        if ($this->dao->count(['pid' => $id])) {
            throw new AdminException(400102);
        }
        $res = $this->dao->delete($id);
        if (!$res) throw new AdminException(100008);

        $this->cacheDriver()->clear();
        $this->cacheDriver()->set('category_version', uniqid());
    }

    /**
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/2/8
     */
    public function getCategoryVersion()
    {
        return $this->cacheDriver()->remember('category_version', function () {
            return uniqid();
        });
    }

    /**
     * 获取指定id下的分类,一=以数组形式返回
     * @param string $cateIds
     * @return array
     */
    public function getCateArray(string $cateIds)
    {
        return $this->dao->getCateArray($cateIds);
    }

    /**
     * 前台分类列表
     * @return bool|mixed|null
     */
    public function getCategory(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        if ($limit) {
            return $this->dao->getALlByIndex($where, 'id,cate_name,pid,pic', $limit);
        } else {
            return $this->cacheDriver()->remember('CATEGORY', function () {
                return $this->dao->getCategory();
            });
        }
    }

    /**
     * 分类详情
     * @param int $id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getInfo(int $id)
    {
        $info = $this->dao->get($id, ['id', 'cate_name', 'pid', 'pic', 'big_pic', 'sort', 'is_show']);
        if ($info) {
            $info = $info->toArray();
        }
        return $info;
    }

    /**
     * 分类列表
     * @return mixed
     */
    public function getCategoryList(array $where)
    {
        return $this->cacheDriver()->remember('CATEGORY_LIST', function () use ($where) {
            return $this->dao->getALlByIndex($where, 'id, cate_name, pid, pic, big_pic, sort, is_show, add_time');
        }, 86400);
    }
}
