<?php

namespace app\services\system;

use app\dao\system\SystemCrudListDao;
use app\services\BaseServices;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;

class SystemCrudListServices extends BaseServices
{
    /**
     * SystemCrudListServices constructor.
     * @param SystemCrudListDao $dao
     */
    public function __construct(SystemCrudListDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 数据字典列表
     * @param $where
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    public function dataDictionaryList($where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->selectList($where, '*', $page, $limit, '', [], true)->toArray();
        $count = $this->dao->count($where);
        foreach ($list as &$item) {
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
        }
        return compact('list', 'count');
    }

    /**
     * 数据字典新增/编辑
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    public function dataDictionaryListCreate($id = 0)
    {
        $info = $this->dao->get($id);
        $field = [];
        $field[] = Form::input('name', '字典名称', $info['name'] ?? '')->required();
        $field[] = Form::input('mark', '字典标识', $info['mark'] ?? '')->required();
        $field[] = Form::radio('level', '层级', $info['level'] ?? 0)->options([['value' => 1, 'label' => '多级'], ['value' => 0, 'label' => '一级']]);
        $field[] = Form::radio('status', '状态', $info['status'] ?? 1)->options([['value' => 1, 'label' => '显示'], ['value' => 0, 'label' => '隐藏']]);
        return create_form($id ? '编辑' : '新增', $field, Url::buildUrl('/system/crud/data_dictionary_list/save/' . $id), 'POST');
    }

    /**
     * 数据字典保存
     * @param int $id
     * @param array $data
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    public function dataDictionaryListSave($id = 0, $data = [])
    {
        if ($id) {
            $this->dao->update($id, $data);
        } else {
            $data['add_time'] = time();
            $this->dao->save($data);
        }
        return true;
    }

    /**
     * 数据字典删除
     * @param $id
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    public function dataDictionaryListDel($id)
    {
        $res1 = $this->dao->delete($id);
        $res2 = app()->make(SystemCrudDataService::class)->delete(['cid' => $id]);
        return $res1 && $res2;
    }
}