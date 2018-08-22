<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/18
 */

namespace app\wap\controller;


use app\wap\model\user\UserEnter;
use service\JsonService;
use service\UtilService;
use think\Cookie;
use think\Db;
use think\Request;
use think\Url;

class Merchant extends AuthController
{
    public function agreement()
    {
        return $this->fetch();
    }

    public function apply()
    {
        if(UserEnter::be($this->userInfo['uid'],'uid')){
            $enterInfo = UserEnter::where('uid',$this->userInfo['uid'])->find()->toArray();
            if($enterInfo['status'] == 0) return $this->failed('正在审核中,请耐心等待!');
            if($enterInfo['status'] == 1) return $this->failed('审核已通过');
        }else{
            if(!Cookie::get('_mer_check_auth')) return $this->failed('请先同意入驻协议!',Url::build('agreement'));
            $enterInfo = [];
        }
        $this->assign(compact('enterInfo'));
        return $this->fetch();
    }

    public function submit(Request $request)
    {
        $data = UtilService::postMore([
            ['province',''],
            ['city',''],
            ['district',''],
            ['address',''],
            ['company_name','','','merchant_name'],
            ['link_user',''],
            ['link_tel',''],
            ['charter',[]],
            ['id',0]
        ],$request);
        if($data['id'] == 0 && UserEnter::be(['uid'=>$this->userInfo['uid']]))
            return JsonService::fail('已提交商户信息,请勿重复申请!');

        if(($data['id'] >0 && UserEnter::editEvent($data,$this->userInfo['uid'])) || UserEnter::setEnter($data,$this->userInfo['uid']))
            return JsonService::successful();
        else
            return JsonService::fail('提交失败!');
    }


}