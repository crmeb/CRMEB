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
declare (strict_types=1);

namespace app\services\system\attachment;

use app\services\BaseServices;
use app\dao\system\attachment\SystemAttachmentCategoryDao;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;

/**
 *
 * Class SystemAttachmentCategoryServices
 * @package app\services\attachment
 * @method get($id) 获取一条数据
 * @method count($where) 获取条件下数据总数
 */
class SystemAttachmentCategoryServices extends BaseServices
{

    /**
     * SystemAttachmentCategoryServices constructor.
     * @param SystemAttachmentCategoryDao $dao
     */
    public function __construct(SystemAttachmentCategoryDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取分类列表
     * @param array $where
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAll(array $where)
    {
        $list = $this->dao->getList($where);
        if ($where['all'] == 1) {
            $list = $this->tidyMenuTier($list);
        } else {
            foreach ($list as &$item) {
                $item['title'] = $item['name'];
                if ($where['name'] == '' && $this->dao->count(['pid' => $item['id']])) {
                    $item['loading'] = false;
                    $item['children'] = [];
                }
            }
        }
        return compact('list');
    }

    /**
     * 格式化列表
     * @param $menusList
     * @param int $pid
     * @param array $navList
     * @return array
     */
    public function tidyMenuTier($menusList, $pid = 0, $navList = [])
    {
        foreach ($menusList as $k => $menu) {
            $menu['title'] = $menu['name'];
            if ($menu['pid'] == $pid) {
                unset($menusList[$k]);
                $menu['children'] = $this->tidyMenuTier($menusList, $menu['id']);
                if (count($menu['children'])) {
                    $menu['expand'] = true;
                } else {
                    unset($menu['children']);
                }
                $navList[] = $menu;
            }
        }
        return $navList;
    }

    /**
     * 创建新增表单
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm($pid, $type)
    {
        return create_form('添加分类', $this->form(['pid' => $pid, 'type' => $type]), Url::buildUrl('/file/category'), 'POST');
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
        return create_form('编辑分类', $this->form($info), Url::buildUrl('/file/category/' . $id), 'PUT');
    }

    /**
     * 生成表单参数
     * @param array $info
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function form($info = [])
    {
        [$pidList, $data] = $this->getPidList((int)($info['pid'] ?? 0));
        return [
            Form::cascader('pid', '上级分类', $data)->options($pidList)->filterable(true)->props(['props' => ['multiple' => false, 'checkStrictly' => true, 'emitPath' => false]])->style(['width' => '100%']),
            Form::input('name', '分类名称', $info['name'] ?? '')->maxlength(30),
            Form::hidden('type', $info['type'] ?? 0),
        ];
    }

    /**
     * 获取分类
     * @param $value
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/9/12
     */
    public function getPidList($value)
    {
        $pidList = $this->dao->selectList([], 'id as value, pid, name as label')->toArray();
        if ($value) {
            $data = get_tree_value($pidList, $value);
        } else {
            $data = [0];
        }
        $pidList = get_tree_children($pidList, 'children', 'value');
        array_unshift($pidList, ['value' => 0, 'pid' => 0, 'label' => '顶级分类']);
        return [$pidList, array_reverse($data)];
    }

    /**
     * 获取分类列表（添加修改）
     * @param array $where
     * @return mixed
     */
    public function getCateList(array $where)
    {
        $list = $this->dao->getList($where);
        $options = [['value' => 0, 'label' => '所有分类']];
        foreach ($list as $id => $cateName) {
            $options[] = ['label' => $cateName['name'], 'value' => $cateName['id']];
        }
        return $options;
    }

    /**
     * 保存新建的资源
     * @param array $data
     */
    public function save(array $data)
    {
        if ($this->dao->getOne(['name' => $data['name']])) {
            throw new AdminException(400101);
        }
        $res = $this->dao->save($data);
        if (!$res) throw new AdminException(100022);
        return $res;
    }

    /**
     * 保存修改的资源
     * @param int $id
     * @param array $data
     */
    public function update(int $id, array $data)
    {
        $attachment = $this->dao->getOne(['name' => $data['name']]);
        if ($attachment && $attachment['id'] != $id) {
            throw new AdminException(400101);
        }
        $res = $this->dao->update($id, $data);
        if (!$res) throw new AdminException(100007);
    }

    /**
     * 删除分类
     * @param int $id
     */
    public function del(int $id)
    {
        $count = $this->dao->getCount(['pid' => $id]);
        if ($count) {
            throw new AdminException(400102);
        } else {
            $res = $this->dao->delete($id);
            if (!$res) throw new AdminException(400102);
        }
    }


    /**
     * 获取一条数据
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOne($where)
    {
        return $this->dao->getOne($where);
    }
}
