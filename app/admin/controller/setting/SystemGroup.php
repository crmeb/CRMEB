<?php

namespace app\admin\controller\setting;

use crmeb\services\UtilService as Util;
use crmeb\services\JsonService as Json;
use think\facade\Route as Url;
use app\admin\model\system\SystemGroup as GroupModel;
use app\admin\model\system\SystemGroupData as GroupDataModel;
use app\admin\controller\AuthController;

/**
 * 组合数据控制器
 * Class SystemGroup
 * @package app\admin\controller\system
 */
class SystemGroup extends AuthController
{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $this->assign(GroupModel::page());
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $this->assign(['title'=>'添加数据组','save'=>Url::buildUrl('save')]);
        return $this->fetch();
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save()
    {
        $params = Util::postMore([
            ['id',''],
            ['name',''],
            ['config_name',''],
            ['info',''],
            ['typelist',[]],
        ],$this->request);

        //数据组名称判断
        if(!$params['name'])return Json::fail('请输入数据组名称！');
        if(!$params['config_name'])return Json::fail('请输入配置名称！');
        //判断ID是否存在，存在就是编辑，不存在就是添加
        if(!$params['id']){
            if(GroupModel::be($params['config_name'],'config_name')) return Json::fail('数据关键字已存在！');
        }
        $data["name"] = $params['name'];
        $data["config_name"] = $params['config_name'];
        $data["info"] = $params['info'];
        //字段信息判断
        if(!count($params['typelist']))
            return Json::fail('字段至少存在一个！');
        else{
            $validate = ["name","type","title","description"];
            foreach ($params["typelist"] as $key => $value) {
                foreach ($value as $name => $field) {
                    if(empty($field["value"]) && in_array($name,$validate))
                        return Json::fail("字段".($key + 1)."：".$field["placeholder"]."不能为空！");
                    else
                        $data["fields"][$key][$name] = $field["value"];
                }
            }
        }
        $data["fields"] = json_encode($data["fields"]);
        //判断ID是否存在，存在就是编辑，不存在就是添加
        if(!$params['id']) {
            GroupModel::create($data);
            return Json::successful('添加数据组成功!');
        }else{
            GroupModel::edit($data,$params['id']);
            return Json::successful('编辑数据组成功!');
        }
    }

    /**编辑数组
     * @param $id
     */
    public function edit($id)
    {
        $Groupinfo = GroupModel::get($id);
        $fields = json_decode($Groupinfo['fields'],true);
        $typelist = [];
        foreach ($fields as $key => $v){
            $typelist[$key]['name']['value'] = $v['name'];
            $typelist[$key]['title']['value'] = $v['title'];
            $typelist[$key]['type']['value'] = $v['type'];
            $typelist[$key]['param']['value'] = $v['param'];
        }
        $Groupinfo['fields'] = json_encode($typelist);
        $this->assign(compact('Groupinfo'));
        $this->assign(['title'=>'添加数据组','save'=>Url::buildUrl('save')]);
        return $this->fetch();
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if(!GroupModel::del($id))
            return Json::fail(GroupModel::getErrorInfo('删除失败,请稍候再试!'));
        else{
            GroupDataModel::del(["gid"=>$id]);
            return Json::successful('删除成功!');
        }
    }
}
