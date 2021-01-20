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
                $arr = $list[$key];
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
     * 设置分类状态
     * @param $id
     * @param $is_show
     */
    public function setShow(int $id, int $is_show)
    {
        $res = $this->dao->update($id, ['is_show' => $is_show]);
        $res = $res && $this->dao->update($id, ['is_show' => $is_show], 'pid');
        if (!$res) {
            throw new AdminException('设置失败');
        } else {
            CacheService::delete('CATEGORY');
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
            $f[] = Form::select('pid', '父级', (string)($info['pid'] ?? ''))->setOptions($this->menus())->filterable(1)->disabled(true);
        } else {
            $f[] = Form::select('pid', '父级', (string)($info['pid'] ?? ''))->setOptions($this->menus())->filterable(1);
        }
        $f[] = Form::input('cate_name', '分类名称', $info['cate_name'] ?? '')->maxlength(30);
        $f[] = Form::frameImage('pic', '分类图标(180*180)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'pic')), $info['pic'] ?? '')->icon('ios-add')->width('60%')->height('435px');
        $f[] = Form::frameImage('big_pic', '分类大图(468*340)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'big_pic')), $info['big_pic'] ?? '')->icon('ios-add')->width('60%')->height('435px');
        $f[] = Form::number('sort', '排序', (int)($info['sort'] ?? 0))->min(0);
        $f[] = Form::radio('is_show', '状态', $info['is_show'] ?? 1)->options([['label' => '显示', 'value' => 1], ['label' => '隐藏', 'value' => 0]]);
        return $f;
    }

    /**
     * 获取一级分类组合数据
     * @return array[]
     */
    public function menus()
    {
        $list = $this->dao->getMenus(['pid' => 0]);
        $menus = [['value' => 0, 'label' => '顶级菜单']];
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
        if ($this->dao->getOne(['cate_name' => $data['cate_name']])) {
            throw new AdminException('该分类已经存在');
        }
        $res = $this->dao->save($data);
        if (!$res) throw new AdminException('添加失败');
        CacheService::delete('CATEGORY');
    }

    /**
     * 保存修改数据
     * @param $id
     * @param $data
     */
    public function editData($id, $data)
    {
        $cate = $this->dao->getOne(['cate_name' => $data['cate_name']]);
        if ($cate && $cate['id'] != $id) {
            throw new AdminException('该分类不存在');
        }
        $res = $this->dao->update($id, $data);
        if (!$res) throw new AdminException('修改失败');
        CacheService::delete('CATEGORY');
    }

    /**
     * 删除数据
     * @param int $id
     */
    public function del(int $id)
    {
        if ($this->dao->count(['pid' => $id])) {
            throw new AdminException('请先删除子分类!');
        }
        $res = $this->dao->delete($id);
        if (!$res) throw new AdminException('删除失败');
        CacheService::delete('CATEGORY');
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
        if($limit){
            return $this->dao->getALlByIndex($where, 'id,cate_name,pid,pic', $limit);
        }else{
            return CacheService::get('CATEGORY', function () {
                return $this->dao->getCategory();
            });
        }
    }
}
