<?php

namespace app\admin\controller\setting;

use app\admin\controller\AuthController;
use app\admin\model\ump\StoreSeckill;
use crmeb\services\{
    CacheService,
    FormBuilder as Form,
    JsonService as Json,
    UtilService as Util
};
use think\facade\Route as Url;
use app\admin\model\system\{
    SystemAttachment, SystemGroup as GroupModel, SystemGroupData as GroupDataModel
};

/**
 * 数据列表控制器  在组合数据中
 * Class SystemGroupData
 * @package app\admin\controller\system
 */
class SystemGroupData extends AuthController
{

    /**
     * 显示资源列表
     * @return \think\Response
     */
    public function index($gid = 0)
    {
        $where = Util::getMore([
            ['gid', 0],
            ['status', ''],
        ], $this->request);
        if ($gid) $where['gid'] = $gid;
        $this->assign('where', $where);
        $this->assign(compact("gid"));
        $this->assign(GroupModel::getField($gid));
        $this->assign(GroupDataModel::getList($where));
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     * @return \think\Response
     */
    public function create($gid)
    {
        $Fields = GroupModel::getField($gid);
        $f = array();
        foreach ($Fields["fields"] as $key => $value) {
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

            switch ($value["type"]) {
                case 'input':
                    $f[] = Form::input($value["title"], $value["name"]);
                    break;
                case 'textarea':
                    $f[] = Form::input($value["title"], $value["name"])->type('textarea')->placeholder($value['param']);
                    break;
                case 'radio':
                    $f[] = Form::radio($value["title"], $value["name"], $info[0]["value"] ?? '')->options($info);
                    break;
                case 'checkbox':
                    $f[] = Form::checkbox($value["title"], $value["name"], $info[0] ?? '')->options($info);
                    break;
                case 'select':
                    $f[] = Form::select($value["title"], $value["name"], $info[0] ?? '')->options($info)->multiple(false);
                    break;
                case 'upload':
                    $f[] = Form::frameImageOne($value["title"], $value["name"], Url::buildUrl('admin/widget.images/index', array('fodder' => $value["title"], 'big' => 1)))->icon('image')->width('100%')->height('500px');
                    break;
                case 'uploads':
                    $f[] = Form::frameImages($value["title"], $value["name"], Url::buildUrl('admin/widget.images/index', array('fodder' => $value["title"], 'big' => 1)))->maxLength(5)->icon('images')->width('100%')->height('500px')->spin(0);
                    break;
                case 'number':
                    $f[] = Form::number($value["title"], $value["name"])->precision('int');
                    break;
                default:
                    $f[] = Form::input($value["title"], $value["name"]);
                    break;

            }
        }
        $f[] = Form::number('sort', '排序', 1);
        $f[] = Form::radio('status', '状态', 1)->options([['value' => 1, 'label' => '显示'], ['value' => 2, 'label' => '隐藏']]);
        $form = Form::make_post_form('添加数据', $f, Url::buildUrl('save', compact('gid')), 2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save($gid)
    {
        $Fields = GroupModel::getField($gid);
        $params = request()->post();
        foreach ($params as $key => $param) {
            foreach ($Fields['fields'] as $index => $field) {
                if ($key == $field["title"]) {
//                    if($param == "" || count($param) == 0)
                    if ($param == "")
                        return Json::fail($field["name"] . "不能为空！");
                    else {
                        $value[$key]["type"] = $field["type"];
                        $value[$key]["value"] = $param;
                    }
                }
            }
        }

        $data = array("gid" => $gid, "add_time" => time(), "value" => htmlspecialchars_decode(json_encode($value)), "sort" => $params["sort"], "status" => $params["status"]);
        GroupDataModel::create($data);
        CacheService::clear();
        return Json::successful('添加数据成功!');
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($gid, $id)
    {
        $GroupData = GroupDataModel::get($id);
        $GroupDataValue = json_decode($GroupData["value"], true);
        $Fields = GroupModel::getField($gid);
        $f = array();
        if (!isset($Fields['fields'])) return $this->failed('数据解析失败！');
        foreach ($Fields['fields'] as $key => $value) {
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
            $fvalue = isset($GroupDataValue[$value['title']]['value']) ? $GroupDataValue[$value['title']]['value'] : '';
            switch ($value['type']) {
                case 'input':
                    $f[] = Form::input($value['title'], $value['name'], $fvalue);
                    break;
                case 'textarea':
                    $f[] = Form::input($value['title'], $value['name'], $fvalue)->type('textarea');
                    break;
                case 'radio':

                    $f[] = Form::radio($value['title'], $value['name'], $fvalue)->options($info);
                    break;
                case 'checkbox':
                    $f[] = Form::checkbox($value['title'], $value['name'], $fvalue)->options($info);
                    break;
                case 'upload':
                    if (!empty($fvalue)) {
                        $image = is_string($fvalue) ? $fvalue : $fvalue[0];
                    } else {
                        $image = '';
                    }
                    $f[] = Form::frameImageOne($value['title'], $value['name'], Url::buildUrl('admin/widget.images/index', array('fodder' => $value['title'], 'big' => 1)), $image)->icon('image')->width('100%')->height('500px');
                    break;
                case 'uploads':
                    $images = !empty($fvalue) ? $fvalue : [];
                    $f[] = Form::frameImages($value['title'], $value['name'], Url::buildUrl('admin/widget.images/index', array('fodder' => $value['title'], 'big' => 1)), $images)->maxLength(5)->icon('images')->width('100%')->height('500px')->spin(0);
                    break;
                case 'select':
                    $f[] = Form::select($value['title'], $value['name'], $fvalue)->setOptions($info);
                    break;
                case 'number':
                    $f[] = Form::number($value["title"], $value["name"])->precision('int');
                    break;
                default:
                    $f[] = Form::input($value['title'], $value['name'], $fvalue);
                    break;

            }
        }
        $f[] = Form::number('sort', '排序', $GroupData["sort"]);
        $f[] = Form::radio('status', '状态', $GroupData["status"])->options([['value' => 1, 'label' => '显示'], ['value' => 2, 'label' => '隐藏']]);
        $form = Form::make_post_form('添加用户通知', $f, Url::buildUrl('update', compact('id')), 2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存更新的资源
     *
     * @param $id
     */
    public function update($id)
    {
        $GroupData = GroupDataModel::get($id);
        $Fields = GroupModel::getField($GroupData["gid"]);
        $params = request()->post();
        foreach ($params as $key => $param) {
            foreach ($Fields['fields'] as $index => $field) {
                if ($key == $field["title"]) {
                    if (trim($param) == '')
                        return Json::fail($field["name"] . "不能为空！");
                    else {
                        $value[$key]["type"] = $field["type"];
                        $value[$key]["value"] = $param;
                    }
                }
            }
        }
        $data = array("value" => htmlspecialchars_decode(json_encode($value)), "sort" => $params["sort"], "status" => $params["status"]);
        GroupDataModel::edit($data, $id);
        CacheService::clear();
        return Json::successful('修改成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $gid = GroupDataModel::where('id', $id)->value('gid');
        $config_name = GroupModel::where('id', $gid)->value('config_name');
        if ($config_name == 'routine_seckill_time') {
            if (!StoreSeckill::where('is_del', 0)->find()) {
                if (!GroupDataModel::del($id))
                    return Json::fail(GroupDataModel::getErrorInfo('删除失败,请稍候再试!'));
                else {
                    CacheService::clear();
                    return Json::successful('删除成功!');
                }
            } else {
                return Json::fail('有秒杀活动，不能删除秒杀时段，请先删除活动');
            }
        } else {
            if (!GroupDataModel::del($id))
                return Json::fail(GroupDataModel::getErrorInfo('删除失败,请稍候再试!'));
            else {
                CacheService::clear();
                return Json::successful('删除成功!');
            }
        }
    }
}
