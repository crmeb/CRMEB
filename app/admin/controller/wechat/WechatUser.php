<?php

namespace app\admin\controller\wechat;

use app\admin\controller\AuthController;
use crmeb\services\FormBuilder as Form;
use app\admin\model\user\User;
use app\admin\model\wechat\WechatUser as UserModel;
use app\models\user\UserBill;
use crmeb\services\JsonService;
use crmeb\services\UtilService as Util;
use crmeb\services\WechatService;
use think\Collection;
use think\facade\Route as Url;

/**
 * 管理员操作记录表控制器
 * Class WechatUser
 * @package app\admin\controller\wechat
 */
class WechatUser extends AuthController

{

    /**
     * 显示操作记录
     */
    public function index(){
        $where = Util::getMore([
            ['nickname',''],
            ['data',''],
            ['tagid_list',''],
            ['groupid','-1'],
            ['sex',''],
            ['export',''],
            ['stair',''],
            ['second',''],
            ['order_stair',''],
            ['order_second',''],
            ['subscribe',''],
            ['now_money',''],
            ['is_promoter',''],
        ],$this->request);
        $tagidList = explode(',',$where['tagid_list']);
        foreach ($tagidList as $k=>$v){
            if(!$v){
                unset($tagidList[$k]);
            }
        }
        $tagidList = array_unique($tagidList);
        $where['tagid_list'] = implode(',',$tagidList);
        try{
            $groupList=UserModel::getUserGroup();
            $tagList=UserModel::getUserTag();
        }catch (\Exception $e){
            $groupList=[];
            $tagList=[];
        }
        $this->assign([
            'where'=>$where,
            'groupList'=>$groupList,
            'tagList'=>$tagList
        ]);
        $limitTimeList = [
            'today'=>implode(' - ',[date('Y/m/d'),date('Y/m/d',strtotime('+1 day'))]),
            'week'=>implode(' - ',[
                date('Y/m/d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)),
                date('Y-m-d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600))
            ]),
            'month'=>implode(' - ',[date('Y/m').'/01',date('Y/m').'/'.date('t')]),
            'quarter'=>implode(' - ',[
                date('Y').'/'.(ceil((date('n'))/3)*3-3+1).'/01',
                date('Y').'/'.(ceil((date('n'))/3)*3).'/'.date('t',mktime(0,0,0,(ceil((date('n'))/3)*3),1,date('Y')))
            ]),
            'year'=>implode(' - ',[
                date('Y').'/01/01',date('Y/m/d',strtotime(date('Y').'/01/01 + 1year -1 day'))
            ])
        ];
        $uidAll = UserModel::getAll($where);
        $this->assign(compact('limitTimeList','uidAll'));
        $this->assign(UserModel::systemPage($where));
        return $this->fetch();
    }

    public function edit_user_tag($openid)
    {
        if(!$openid) return JsonService::fail('参数错误!');
        $list = Collection::make(UserModel::getUserTag())->each(function($item){
            return ['value'=>$item['id'],'label'=>$item['name']];
        });
        $tagList = UserModel::where('openid',$openid)->value('tagid_list');

        $tagList = explode(',',$tagList)?:[];
        $f = [Form::select('tag_id','用户标签',$tagList)->setOptions($list->toArray())->multiple(1)];
        $form = Form::make_post_form('标签名称',$f,Url::buildUrl('update_user_tag',compact('openid')));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function update_user_tag($openid)
    {
        if(!$openid) return JsonService::fail('参数错误!');
        $tagId = request()->post('tag_id/a',[]);
        if(!$tagId) return JsonService::fail('请选择用户标签!');
        $tagList = explode(',',UserModel::where('openid',$openid)->value('tagid_list'))?:[];
        UserModel::edit(['tagid_list'=>$tagId],$openid,'openid');
        if(!$tagId[0])unset($tagId[0]);
        UserModel::edit(['tagid_list'=>$tagId],$openid,'openid');
        try{
            foreach ($tagList as $tag){
                if($tag) WechatService::userTagService()->batchUntagUsers([$openid],$tag);
            }
            foreach ($tagId as $tag){
                WechatService::userTagService()->batchTagUsers([$openid],$tag);
            }
        }catch (\Exception $e){
            UserModel::rollbackTrans();
            return JsonService::fail($e->getMessage());
        }
        UserModel::commitTrans();
        return JsonService::successful('修改成功!');
    }

    public function edit_user_group($openid)
    {
        if(!$openid) return JsonService::fail('参数错误!');
        $list = Collection::make(UserModel::getUserGroup())->each(function($item){
            return ['value'=>$item['id'],'label'=>$item['name']];
        });
        $groupId = UserModel::where('openid',$openid)->value('groupid');
        $f = [Form::select('group_id','用户分组',(string)$groupId)->setOptions($list->toArray())];
        $form = Form::make_post_form('用户分组',$f,Url::buildUrl('update_user_group',compact('openid')));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function update_user_group($openid)
    {
        if(!$openid) return JsonService::fail('参数错误!');
        $groupId = request()->post('group_id');
//        if(!$groupId) return JsonService::fail('请选择用户分组!');
        UserModel::beginTrans();
        UserModel::edit(['groupid'=>$groupId],$openid,'openid');
        try{
            WechatService::userGroupService()->moveUser($openid,$groupId);
        }catch (\Exception $e){
            UserModel::rollbackTrans();
            return JsonService::fail($e->getMessage());
        }
        UserModel::commitTrans();
        return JsonService::successful('修改成功!');
    }

    /**
     * 用户标签列表
     */
    public function tag($refresh = 0)
    {
        $list=[];
        if($refresh == 1) {
            UserModel::clearUserTag();
            $this->redirect(Url::buildUrl('tag'));
        }
        try{
            $list = UserModel::getUserTag();
        }catch (\Exception $e){}
        $this->assign(compact('list'));
        return $this->fetch();
    }

    /**
     * 添加标签
     * @return mixed
     */
    public function create_tag()
    {
        $f = [Form::input('name','标签名称')];
        $form = Form::make_post_form('标签名称',$f,Url::buildUrl('save_tag'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 添加
     */
    public function save_tag()
    {
        $tagName = request()->post('name');
        if(!$tagName) return JsonService::fail('请输入标签名称!');
        try{
            WechatService::userTagService()->create($tagName);
        }catch (\Exception $e){
            return JsonService::fail($e->getMessage());
        }
        UserModel::clearUserTag();
        return JsonService::successful('添加标签成功!');
    }

    /**
     * 修改标签
     * @param $id
     * @return mixed
     */
    public function edit_tag($id)
    {
        $f = [Form::input('name','标签名称')];
        $form = Form::make_post_form('标签名称',$f,Url::buildUrl('update_tag',['id'=>$id]));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 修改标签
     * @param $id
     */
    public function update_tag($id)
    {
        $tagName = request()->post('name');
        if(!$tagName) return JsonService::fail('请输入标签名称!');
        try{
            WechatService::userTagService()->update($id,$tagName);
        }catch (\Exception $e){
            return JsonService::fail($e->getMessage());
        }
        UserModel::clearUserTag();
        return JsonService::successful('修改标签成功!');
    }

    /**
     * 删除标签
     * @param $id
     * @return \think\response\Json
     */
    public function delete_tag($id)
    {
        try{
            WechatService::userTagService()->delete($id);
        }catch (\Exception $e){
            return JsonService::fail($e->getMessage());
        }
        UserModel::clearUserTag();
        return JsonService::successful('删除标签成功!');
    }

    /**
     * 用户分组列表
     */

    public function group($refresh = 0)
    {
        $list=[];
        if($refresh == 1) {
            UserModel::clearUserGroup();
            $this->redirect(Url::buildUrl('group'));
        }
        try{
            $list = UserModel::getUserGroup();
        }catch (\Exception $e){}
        $this->assign(compact('list'));
        return $this->fetch();
    }

    /**
     * 添加分组
     * @return mixed
     */
    public function create_group()
    {
        $f = [Form::input('name','分组名称')];
        $form = Form::make_post_form('标签名称',$f,Url::buildUrl('save_group'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 添加
     */
    public function save_group()
    {
        $tagName = request()->post('name');
        if(!$tagName) return JsonService::fail('请输入分组名称!');
        try{
            WechatService::userGroupService()->create($tagName);
        }catch (\Exception $e){
            return JsonService::fail($e->getMessage());
        }
        UserModel::clearUserGroup();
        return JsonService::successful('添加分组成功!');
    }

    /**
     * 修改分组
     * @param $id
     * @return mixed
     */
    public function edit_group($id)
    {
        $f = [Form::input('name','分组名称')];
        $form = Form::make_post_form('标签名称',$f,Url::buildUrl('update_group',compact('id')));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 修改分组
     * @param $id
     */
    public function update_group($id)
    {
        $tagName = request()->post('name');
        if(!$tagName) return JsonService::fail('请输入分组名称!');
        try{
            WechatService::userGroupService()->update($id,$tagName);
        }catch (\Exception $e){
            return JsonService::fail($e->getMessage());
        }
        UserModel::clearUserGroup();
        return JsonService::successful('修改分组成功!');
    }

    /**
     * 删除分组
     * @param $id
     * @return \think\response\Json
     */
    public function delete_group($id)
    {
        try{
            WechatService::userTagService()->delete($id);
        }catch (\Exception $e){
            return JsonService::fail($e->getMessage());
        }
        UserModel::clearUserGroup();
        return JsonService::successful('删除分组成功!');
    }

    public function synchro_tag($openid){
        if(!$openid) return JsonService::fail('参数错误!');
        $data = array();
        if(UserModel::be($openid,'openid')){
            try{
                $tag = WechatService::userTagService()->userTags($openid)->toArray();
            }catch (\Exception $e) {
                return JsonService::fail($e->getMessage());
            }
            if($tag['tagid_list']) $data['tagid_list'] = implode(',',$tag['tagid_list']);
            else $data['tagid_list'] = '';
            $res = UserModel::edit($data,$openid,'openid');
            if($res) return JsonService::successful('同步成功');
            else return JsonService::fail('同步失败!');
        }else  return JsonService::fail('参数错误!');
    }

    /**
     * 一级推荐人页面
     * @return mixed
     */
    public function stair($uid = ''){
        if($uid == '') return $this->failed('参数错误');
        $list = User::alias('u')
            ->where('u.spread_uid',$uid)
            ->field('u.avatar,u.nickname,u.now_money,u.add_time,u.uid')
            ->where('u.status',1)
            ->order('u.add_time DESC')
            ->select()
            ->toArray();
        foreach ($list as $key=>$value) $list[$key]['orderCount'] = StoreOrder::getOrderCount($value['uid']);
        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 个人资金详情页面
     * @return mixed
     */
    public function now_money($uid = ''){
        if($uid == '') return $this->failed('参数错误');
        $list = UserBill::where('uid',$uid)->where('category','now_money')
            ->field('mark,pm,number,add_time')
            ->where('status',1)->order('add_time DESC')->select()->toArray();
        foreach ($list as &$v){
            $v['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
        }
        $this->assign('list',$list);
        return $this->fetch();
    }

}

