<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------
 */

namespace app\services\system;


use app\dao\system\SystemCrudDataDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;

/**
 * Class SystemCrudDataService
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/7/28
 * @package app\services\system
 */
class SystemCrudDataService extends BaseServices
{

    /**
     * SystemCrudDataService constructor.
     * @param SystemCrudDataDao $dao
     */
    public function __construct(SystemCrudDataDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取全部数据
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/1
     */
    public function getlistAll(string $name = '')
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->selectList(['name' => $name], '*', $page, $limit, '', [], true)->toArray();
        $count = $this->dao->count(['name' => $name]);
        if ($page && $limit) {
            return compact('list', 'count');
        } else {
            return $list;
        }
    }

    /**
     * 获取数据字典列表
     * @param $cid
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    public function dataDictionaryInfoList($cid)
    {
        $level = app()->make(SystemCrudListServices::class)->value($cid, 'level');
        if ($level == 0) {
            [$page, $limit] = $this->getPageValue();
            $list = $this->dao->selectList(['cid' => $cid], '*', $page, $limit, 'sort desc')->toArray();
            foreach ($list as &$item) {
                $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            }
        } else {
            $list = $this->fullListTree($this->dao->selectList(['cid' => $cid], '*', 0, 0, 'sort desc')->toArray());
        }
        $count = $this->dao->count(['cid' => $cid]);
        return compact('list', 'count');
    }

    /**
     * 格式化获取数据字典列表
     * @param $data
     * @param int $pid
     * @param array $navList
     * @return array|mixed
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    function fullListTree($data, $pid = 0, $navList = [])
    {
        foreach ($data as $k => $item) {
            if ($item['pid'] == $pid) {
                unset($item['pid']);
                unset($data[$k]);
                $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
                $item['children'] = $this->fullListTree($data, $item['id']);
                if (!count($item['children'])) unset($item['children']);
                $navList[] = $item;
            }
        }
        return $navList;
    }

    /**
     * 数据字典内容添加修改表单
     * @param $cid
     * @param int $id
     * @param int $pid
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    public function dataDictionaryInfoCreate($cid, int $id = 0, int $pid = 0)
    {
        $info = $this->dao->get($id);
        $field = [];
        $level = app()->make(SystemCrudListServices::class)->value(['id' => $cid], 'level');
        if ($level == 1) {
            $dataList = $this->dao->selectList(['cid' => $cid], 'id as value,name as label,pid')->toArray();
            if (isset($info['pid']) && $info['pid']) {
                $data = get_tree_value($dataList, $info['pid']);
            } else {
                $data = [0];
            }
            if ($pid) {
                $data = get_tree_value($dataList, $pid);
            }
            $dataList = get_tree_children($dataList, 'children', 'value');
            array_unshift($dataList, ['value' => 0, 'pid' => 0, 'label' => '顶级']);
            $field[] = Form::cascader('pid', '上级', array_reverse($data))->options($dataList)->filterable(true)->props(['props' => ['multiple' => false, 'checkStrictly' => true, 'emitPath' => true]]);
        } else {
            $field[] = Form::hidden('pid', 0);
        }
        $field[] = Form::input('name', '名称', $info['name'] ?? '')->required();
        $count = $this->dao->count(['cid' => $cid]);
        $field[] = Form::input('value', '值', $info['value'] ?? $count)->required();
        $field[] = Form::input('sort', '排序', $info['sort'] ?? 0)->required();
        return create_form($id ? '编辑' : '新增', $field, Url::buildUrl('/system/crud/data_dictionary/info_save/' . $cid . '/' . $id), 'POST');
    }

    /**
     * 数据字典内容添加修改
     * @param $cid
     * @param $id
     * @param $data
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    public function dataDictionaryInfoSave($cid, $id, $data)
    {
        if (is_array($data['pid'])) $data['pid'] = end($data['pid']);
        if ($id) {
            $this->dao->update($id, $data);
        } else {
            $data['cid'] = $cid;
            $data['add_time'] = time();
            $this->dao->save($data);
        }
        return true;
    }

    /**
     * 数据字典内容删除
     * @param $id
     * @return bool
     * @throws \ReflectionException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    public function dataDictionaryInfoDel($id)
    {
        $count = $this->dao->count(['pid' => $id]);
        if ($count) {
            throw new AdminException('请先删除子级');
        }
        $this->dao->delete($id);
        return true;
    }
}
