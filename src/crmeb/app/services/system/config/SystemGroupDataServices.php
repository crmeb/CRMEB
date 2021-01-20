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

namespace app\services\system\config;

use app\dao\system\config\SystemGroupDataDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;

/**
 * 组合数据数据集
 * Class SystemGroupDataServices
 * @package app\services\system\config
 * @method delete($id, ?string $key = null) 删除数据
 * @method get(int $id, ?array $field = []) 获取一条数据
 * @method save(array $data) 保存数据
 * @method update($id, array $data, ?string $key = null) 修改数据
 */
class SystemGroupDataServices extends BaseServices
{
    /**
     * SystemGroupDataServices constructor.
     * @param SystemGroupDataDao $dao
     */
    public function __construct(SystemGroupDataDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取某个配置下的数据从新组合成新得数据返回
     * @param string $configName
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getConfigNameValue(string $configName, int $limit = 0)
    {
        /** @var SystemGroupServices $systemGroupServices */
        $systemGroupServices = app()->make(SystemGroupServices::class);
        $value = $this->dao->getGroupDate((int)$systemGroupServices->getConfigNameId($configName), $limit);
        $data = [];
        foreach ($value as $key => $item) {
            $data[$key]["id"] = $item["id"];
            if (isset($item['status'])) $data[$key]["status"] = $item["status"];
            $fields = json_decode($item["value"], true) ?: [];
            foreach ($fields as $index => $field) {
                $data[$key][$index] = $field["value"];
            }
        }
        return $data;
    }

    /**
     * 获取组合数据列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGroupDataList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getGroupDataList($where, $page, $limit);
        $count = $this->dao->count($where);
        $type = '';
        $gid = (int)$where['gid'];
        /** @var SystemGroupServices $services */
        $services = app()->make(SystemGroupServices::class);
        $group = $services->getOne(['id' => $gid], 'id,config_name,fields');

        $header = json_decode($group['fields'], true) ?? [];
        $title = [];
        $param = [];
        foreach ($header as $item) {
            if ($group['config_name'] == 'order_details_images' && $item['title'] == 'order_status') {
                $status = str_replace("\r\n", "\n", $item["param"]);//防止不兼容
                $status = explode("\n", $status);
                if (is_array($status) && !empty($status)) {
                    foreach ($status as $index => $v) {
                        $vl = explode('=>', $v);
                        if (isset($vl[0]) && isset($vl[1])) {
                            $param[$vl[0]] = $vl[1];
                        }
                    }
                }
            }
            if ($item['type'] == 'upload' || $item['type'] == 'uploads') {
                $title[$item['title']] = [];
                $type = $item['title'];
            } else {
                $title[$item['title']] = '';
            }
        }
        foreach ($list as $key => $value) {
            $list[$key] = array_merge($value, $title);
            $infos = json_decode($value['value'], true) ?: [];
            foreach ($infos as $index => $info) {
                if ($group['config_name'] == 'order_details_images' && $index == 'order_status') {
                    $list[$key][$index] = ($param[$info['value']] ?? '') . '/' . $info['value'];
                } else {
                    if ($info['type'] == 'upload') {
                        $list[$key][$index] = [$info['value']];
                    } elseif ($info['type'] == 'checkbox') {
                        $list[$key][$index] = implode(",", $info["value"]);
                    } else {
                        $list[$key][$index] = $info['value'];
                    }
                }
            }
            unset($list[$key]['value']);
        }
        return compact('list', 'count', 'type');
    }

    /**
     * 根据gid判断出是否能再次添加组合数据
     * @param int $gid
     * @param int $count
     * @param string $key
     * @return bool
     */
    public function isGroupGidSave(int $gid, int $count, string $key): bool
    {
        /** @var SystemGroupServices $services */
        $services = app()->make(SystemGroupServices::class);
        $configName = $services->value(['id' => $gid], 'config_name');
        if ($configName == $key) {
            return $this->dao->count(['gid' => $gid]) >= $count;
        } else {
            return false;
        }
    }

    /**
     * 创建表单
     * @param int $gid
     * @param array $groupData
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createGroupForm(int $gid, array $groupData = [])
    {
        $groupDataValue = isset($groupData["value"]) ? json_decode($groupData["value"], true) : [];
        /** @var SystemGroupServices $services */
        $services = app()->make(SystemGroupServices::class);
        $fields = $services->getValueFields($gid);
        $f[] = Form::hidden('gid', $gid);
        foreach ($fields as $key => $value) {
            $info = [];
            if (isset($value["param"])) {
                $value["param"] = str_replace("\r\n", "\n", $value["param"]);//防止不兼容
                $params = explode("\n", $value["param"]);
                if (is_array($params) && !empty($params)) {
                    foreach ($params as $index => $v) {
                        $vl = explode('=>', $v);
                        if (isset($vl[0]) && isset($vl[1])) {
                            $info[$index]["value"] = $vl[0];
                            $info[$index]["label"] = $vl[1];
                        }
                    }
                }
            }
            $fvalue = isset($groupDataValue[$value['title']]['value']) ? $groupDataValue[$value['title']]['value'] : '';
            switch ($value["type"]) {
                case 'input':
                    $f[] = Form::input($value["title"], $value["name"], $fvalue);
                    break;
                case 'textarea':
                    $f[] = Form::input($value["title"], $value["name"], $fvalue)->type('textarea')->placeholder($value['param']);
                    break;
                case 'radio':
                    $f[] = Form::radio($value["title"], $value["name"], $fvalue ?: (int)$info[0]["value"] ?? '')->options($info);
                    break;
                case 'checkbox':
                    $f[] = Form::checkbox($value["title"], $value["name"], $fvalue ?: $info[0] ?? '')->options($info);
                    break;
                case 'select':
                    $f[] = Form::select($value["title"], $value["name"], $fvalue ?: $info[0] ?? '')->options($info)->multiple(false);
                    break;
                case 'upload':
                    if (!empty($fvalue)) {
                        $image = is_string($fvalue) ? $fvalue : $fvalue[0];
                    } else {
                        $image = '';
                    }
                    $f[] = Form::frameImage($value["title"], $value["name"], $this->url('admin/widget.images/index', ['fodder' => $value["title"], 'big' => 1], true), $image)->icon('ios-image')->width('60%')->height('435px');
                    break;
                case 'uploads':
                    if ($fvalue) {
                        if (is_string($fvalue)) $fvalue = [$fvalue];
                        $images = !empty($fvalue) ? $fvalue : [];
                    } else {
                        $images = [];
                    }
                    $f[] = Form::frameImages($value["title"], $value["name"], $this->url('admin/widget.images/index', ['fodder' => $value["title"], 'big' => 1, 'type' => 'many', 'maxLength' => 5], true), $images)->maxLength(5)->icon('ios-images')->width('60%')->height('435px')->spin(0);
                    break;
                default:
                    $f[] = Form::input($value["title"], $value["name"], $fvalue);
                    break;

            }
        }
        $f[] = Form::number('sort', '排序', (int)($groupData["sort"] ?? 1));
        $f[] = Form::radio('status', '状态', (int)($groupData["status"] ?? 1))->options([['value' => 1, 'label' => '显示'], ['value' => 0, 'label' => '隐藏']]);
        return $f;
    }

    /**
     * 获取添加组合数据表单
     * @param int $gid
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm(int $gid)
    {
        return create_form('添加数据', $this->createGroupForm($gid), $this->url('/setting/group_data'));
    }

    /**
     * 获取修改组合数据表单
     * @param int $gid
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function updateForm(int $gid, int $id)
    {
        $groupData = $this->dao->get($id);
        if (!$groupData) {
            throw new AdminException('修改失败未查到数据!');
        }
        return create_form('编辑数据', $this->createGroupForm($gid, $groupData->toArray()), $this->url('/setting/group_data/' . $id), 'PUT');
    }

    /**
     * 根据id获取当前记录中的数据
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDateValue($id)
    {
        $value = $this->dao->get($id);
        $data["id"] = $value["id"];
        $data["status"] = $value["status"];
        $fields = json_decode($value["value"], true);
        foreach ($fields as $index => $field) {
            $data[$index] = $field["value"];
        }
        return $data;
    }

    /**
     * 根据id获取数据
     * @param array $ids
     * @param string $field
     */
    public function getGroupDataColumn(array $ids)
    {
        $systemGroup = [];
        if (!empty($ids)) {
            $systemGroupData = $this->dao->idByGroupList($ids);
            if (!empty($systemGroupData))
                $systemGroup = array_combine(array_column($systemGroupData, 'id'), $systemGroupData);
        }
        return $systemGroup;
    }
}
