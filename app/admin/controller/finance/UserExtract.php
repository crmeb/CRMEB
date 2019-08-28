<?php
/**
 * Created by PhpStorm.
 * User: lianghuan
 * Date: 2018-03-03
 * Time: 16:37
 */
namespace app\admin\controller\finance;

use app\admin\controller\AuthController;
use crmeb\services\FormBuilder as Form;
use app\admin\model\user\UserExtract as UserExtractModel;
use crmeb\services\JsonService;
use crmeb\services\UtilService as Util;
use think\facade\Route as Url;

/**
 * 用户提现管理
 * Class UserExtract
 * @package app\admin\controller\finance
 */
class UserExtract extends AuthController
{
   public function index(){
       $where = Util::getMore([
           ['status',''],
           ['nickname',''],
           ['extract_type',''],
           ['nireid',''],
           ['date',''],
       ],$this->request);
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
       $this->assign('where',$where);
       $this->assign('limitTimeList',$limitTimeList);
       $this->assign(UserExtractModel::extractStatistics());
       $this->assign(UserExtractModel::systemPage($where));
      return $this->fetch();
   }
    public function edit($id){
        if(!$id) return $this->failed('数据不存在');
        $UserExtract = UserExtractModel::get($id);
        if(!$UserExtract) return JsonService::fail('数据不存在!');
        $f = array();
        $f[] = Form::input('real_name','姓名',$UserExtract['real_name']);
        $f[] = Form::number('extract_price','提现金额',$UserExtract['extract_price'])->precision(2);
        if($UserExtract['extract_type']=='alipay'){
            $f[] = Form::input('alipay_code','支付宝账号',$UserExtract['alipay_code']);
        }else if($UserExtract['extract_type']=='weixin'){
            $f[] = Form::input('wechat','微信号',$UserExtract['wechat']);
        }else{
            $f[] = Form::input('bank_code','银行卡号',$UserExtract['bank_code']);
            $f[] = Form::input('bank_address','开户行',$UserExtract['bank_address']);
        }
        $f[] = Form::input('mark','备注',$UserExtract['mark'])->type('textarea');
        $form = Form::make_post_form('编辑',$f,Url::buildUrl('update',array('id'=>$id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');

    }
    public function update($id)
    {
        $UserExtract = UserExtractModel::get($id);
        if(!$UserExtract) return JsonService::fail('数据不存在!');
        if($UserExtract['extract_type']=='alipay'){
            $data = Util::postMore([
                'real_name',
                'mark',
                'extract_price',
                'alipay_code',
            ]);
            if(!$data['real_name']) return JsonService::fail('请输入姓名');
            if($data['extract_price']<=-1) return JsonService::fail('请输入提现金额');
            if(!$data['alipay_code']) return JsonService::fail('请输入支付宝账号');
        }else if($UserExtract['extract_type']=='weixin'){
            $data = Util::postMore([
                'real_name',
                'mark',
                'extract_price',
                'wechat',
            ]);
//            if(!$data['real_name']) return JsonService::fail('请输入姓名');
            if($data['extract_price']<=-1) return JsonService::fail('请输入提现金额');
            if(!$data['wechat']) return JsonService::fail('请输入微信账号');
        }else{
            $data = Util::postMore([
                'real_name',
                'extract_price',
                'mark',
                'bank_code',
                'bank_address',
            ]);
            if(!$data['real_name']) return JsonService::fail('请输入姓名');
            if($data['extract_price']<=-1) return JsonService::fail('请输入提现金额');
            if(!$data['bank_code']) return JsonService::fail('请输入银行卡号');
            if(!$data['bank_address']) return JsonService::fail('请输入开户行');
        }
        if(!UserExtractModel::edit($data,$id))
            return JsonService::fail(UserExtractModel::getErrorInfo('修改失败'));
        else
            return JsonService::successful('修改成功!');
    }
    public function fail($id)
    {
        if(!UserExtractModel::be(['id'=>$id,'status'=>0])) return JsonService::fail('操作记录不存在或状态错误!');
        $fail_msg =request()->post();
        $extract=UserExtractModel::get($id);
        if(!$extract)  return JsonService::fail('操作记录不存在!');
        if($extract->status==1)  return JsonService::fail('已经提现,错误操作');
        if($extract->status==-1)  return JsonService::fail('您的提现申请已被拒绝,请勿重复操作!');
        $res = UserExtractModel::changeFail($id,$fail_msg['message']);
        if($res){
            return JsonService::successful('操作成功!');
        }else{
            return JsonService::fail('操作失败!');
        }
    }
    public function succ($id)
    {
        if(!UserExtractModel::be(['id'=>$id,'status'=>0]))
            return JsonService::fail('操作记录不存在或状态错误!');
        UserExtractModel::beginTrans();
        $extract=UserExtractModel::get($id);
        if(!$extract)  return JsonService::fail('操作记录不存!');
        if($extract->status == 1)  return JsonService::fail('您已提现,请勿重复提现!');
        if($extract->status == -1)  return JsonService::fail('您的提现申请已被拒绝!');
        $res = UserExtractModel::changeSuccess($id);
        if($res){
            UserExtractModel::commitTrans();
            return JsonService::successful('操作成功!');
        }else{
            UserExtractModel::rollbackTrans();
            return JsonService::fail('操作失败!');
        }
    }
}