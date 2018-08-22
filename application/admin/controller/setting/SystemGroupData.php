<?php

namespace app\admin\controller\setting;
use app\admin\common\Error;
use service\FormBuilder as Form;
use service\JsonService as Json;
use service\UploadService as Upload;
use service\UtilService as Util;
use think\Request;
use think\Url;
use app\admin\model\system\SystemGroup as GroupModel;
use app\admin\model\system\SystemGroupData as GroupDataModel;
use app\admin\controller\AuthController;
use app\admin\model\system\SystemAttachment;
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
    public function index($gid)
    {
        $where = Util::getMore([
            ['status','']
        ],$this->request);
        $this->assign('where',$where);
        $this->assign(compact("gid"));
        $this->assign(GroupModel::getField($gid));
        $where['gid'] = $gid;
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
            if($value["type"] == "input")
                $f[] = Form::input($value["title"],$value["name"]);
            else if($value["type"] == "textarea")
                $f[] = Form::input($value["title"],$value["name"])->type('textarea')->placeholder($value['param']);
            else if($value["type"] == "radio") {
                $params = explode("-", $value["param"]);
                foreach ($params as $index => $param) {
                    $info[$index]["value"] = $param;
                    $info[$index]["label"] = $param;
                }
                $f[] = Form::radio($value["title"],$value["name"],$info[0]["value"])->options($info);
            }else if($value["type"] == "checkbox"){
                $params = explode("-",$value["param"]);
                foreach ($params as $index => $param) {
                    $info[$index]["value"] = $param;
                    $info[$index]["label"] = $param;
                }
                $f[] = Form::checkbox($value["title"],$value["name"],$info[0])->options($info);
            }else if($value["type"] == "upload")
                $f[] = Form::frameImageOne($value["title"],$value["name"],Url::build('admin/widget.images/index',array('fodder'=>$value["title"])))->icon('image');
            else if($value['type'] == 'uploads')
                $f[] = Form::frameImages($value["title"],$value["name"],Url::build('admin/widget.images/index',array('fodder'=>$value["title"])))->maxLength(5)->icon('images')->width('100%')->height('550px')->spin(0);
        }
        $f[] = Form::number('sort','排序',1);
        $f[] = Form::radio('status','状态',1)->options([['value'=>1,'label'=>'显示'],['value'=>2,'label'=>'隐藏']]);
        $form = Form::make_post_form('添加数据',$f,Url::build('save',compact('gid')));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request,$gid)
    {
        $Fields = GroupModel::getField($gid);
        $params = $request->post();
        foreach ($params as $key => $param) {
            foreach ($Fields['fields'] as $index => $field) {
                if($key == $field["title"]){
                    if($param == "" || count($param) == 0)
                        return Json::fail($field["name"]."不能为空！");
                    else{
                        $value[$key]["type"] = $field["type"];
                        $value[$key]["value"] = $param;
                    }
                }
            }
        }

        $data = array("gid"=>$gid,"add_time"=>time(),"value"=>json_encode($value),"sort"=>$params["sort"],"status"=>$params["status"]);
        GroupDataModel::set($data);
        return Json::successful('添加数据成功!');
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($gid,$id)
    {
        $GroupData = GroupDataModel::get($id);
        $GroupDataValue = json_decode($GroupData["value"],true);
        $Fields = GroupModel::getField($gid);
        $f = array();
        foreach ($Fields["fields"] as $key => $value) {
            if($value["type"] == "input") $f[] = Form::input($value["title"],$value["name"],$GroupDataValue[$value["title"]]["value"]);
            if($value["type"] == "textarea") $f[] = Form::input($value["title"],$value["name"],$GroupDataValue[$value["title"]]["value"])->type('textarea');
            if($value["type"] == "radio"){
                $params = explode("-",$value["param"]);
                foreach ($params as $index => $param) {
                    $info[$index]["value"] = $param;
                    $info[$index]["label"] = $param;
                }
                $f[] = Form::radio($value["title"],$value["name"],$GroupDataValue[$value["title"]]["value"])->options($info);
            }
            if($value["type"] == "checkbox"){
                $params = explode("-",$value["param"]);
                foreach ($params as $index => $param) {
                    $info[$index]["value"] = $param;
                    $info[$index]["label"] = $param;
                }
                $f[] = Form::checkbox($value["title"],$value["name"],$GroupDataValue[$value["title"]]["value"])->options($info);
            }
            if($value["type"] == "upload"){
               $image = is_string($GroupDataValue[$value["title"]]["value"])?$GroupDataValue[$value["title"]]["value"]:$GroupDataValue[$value["title"]]["value"][0];
                $f[] = Form::frameImageOne($value["title"],$value["name"],Url::build('admin/widget.images/index',array('fodder'=>$value["title"])),$image)->icon('image');
            }
            else if($value['type'] == 'uploads') {
                $f[] = Form::frameImages($value["title"], $value["name"], Url::build('admin/widget.images/index', array('fodder' => $value["title"])), $GroupDataValue[$value["title"]]["value"])->maxLength(5)->icon('images')->width('100%')->height('550px')->spin(0);
            }
        }
        $f[] = Form::input('sort','排序',$GroupData["sort"]);
        $f[] = Form::radio('status','状态',$GroupData["status"])->options([['value'=>1,'label'=>'显示'],['value'=>2,'label'=>'隐藏']]);
        $form = Form::make_post_form('添加用户通知',$f,Url::build('update',compact('id')));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $GroupData = GroupDataModel::get($id);
        $Fields = GroupModel::getField($GroupData["gid"]);
        $params = $request->post();
        foreach ($params as $key => $param) {
            foreach ($Fields['fields'] as $index => $field) {
                if($key == $field["title"]){
                    if($param == "" || count($param) == 0)
                        return Json::fail($field["name"]."不能为空！");
                    else{
                        $value[$key]["type"] = $field["type"];
                        $value[$key]["value"] = $param;
                    }
                }
            }
        }
        $data = array("value"=>json_encode($value),"sort"=>$params["sort"],"status"=>$params["status"]);
        GroupDataModel::edit($data,$id);
        return Json::successful('修改成功!');
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if(!GroupDataModel::del($id))
            return Json::fail(GroupDataModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

    public function upload()
    {
        $res = Upload::image('file','common');
        $thumbPath = Upload::thumb($res->dir);
        //产品图片上传记录
        $fileInfo = $res->fileInfo->getinfo();
        SystemAttachment::attachmentAdd($res->fileInfo->getSaveName(),$fileInfo['size'],$fileInfo['type'],$res->dir,$thumbPath,6);

        if($res->status == 200)
            return Json::successful('图片上传成功!',['name'=>$res->fileInfo->getSaveName(),'url'=>Upload::pathToUrl($thumbPath)]);
        else
            return Json::fail($res->error);
    }
}
