<?php


namespace app\admin\controller\setting;

use app\admin\model\system\SystemMenus;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Request;
use think\Url;
use app\admin\model\system\SystemRole as RoleModel;
use app\admin\controller\AuthController;

/*
 * 身份管理  控制器
 * */
class SystemRole extends AuthController
{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = Util::getMore([
            ['status',''],
            ['role_name',''],
        ],$this->request);
        $where['level'] = $this->adminInfo['level'];
        $this->assign('where',$where);
        $this->assign(RoleModel::systemPage($where));
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {

//        if(0 == 0){
//        }else{
//            dump($this->adminInfo['level']);
//        }
        $menus = $this->adminInfo['level'] == 0 ? SystemMenus::ruleList() : SystemMenus::rolesByRuleList($this->adminInfo['roles']);
        $this->assign(['menus'=>json($menus)->getContent(),'saveUrl'=>Url::build('save')]);
        return $this->fetch();
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            'role_name',
            ['status',0],
            ['checked_menus',[],'','rules']
        ],$request);
        if(!$data['role_name']) return Json::fail('请输入身份名称');
        if(!is_array($data['rules']) || !count($data['rules']) )
            return Json::fail('请选择最少一个权限');
        foreach ($data['rules'] as $v){
            $pid = SystemMenus::where('id',$v)->value('pid');
            if(!in_array($pid,$data['rules']))  $data['rules'][] = $pid;
        }
        $data['rules'] = implode(',',$data['rules']);
        $data['level'] = $this->adminInfo['level']+1;
        RoleModel::set($data);
        return Json::successful('添加身份成功!');
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
    public function edit($id)
    {
        //
        $role = RoleModel::get($id);
        $menus = $this->adminInfo['level'] == 0 ? SystemMenus::ruleList() : SystemMenus::rolesByRuleList($this->adminInfo['roles']);
        $this->assign(['role'=>$role->toJson(),'menus'=>json($menus)->getContent(),'updateUrl'=>Url::build('update',array('id'=>$id))]);
        return $this->fetch();
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
        $data = Util::postMore([
            'role_name',
            ['status',0],
            ['checked_menus',[],'','rules']
        ],$request);
        if(!$data['role_name']) return Json::fail('请输入身份名称');
        if(!is_array($data['rules']) || !count($data['rules']) )
            return Json::fail('请选择最少一个权限');
        foreach ($data['rules'] as $v){
            $pid = SystemMenus::where('id',$v)->value('pid');
            if(!in_array($pid,$data['rules']))  $data['rules'][] = $pid;
        }
        $data['rules'] = implode(',',$data['rules']);
        RoleModel::edit($data,$id);
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
        if(!RoleModel::del($id))
            return Json::fail(RoleModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }
}
