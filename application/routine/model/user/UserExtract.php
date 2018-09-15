<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/3/3
 */

namespace app\routine\model\user;


use basic\ModelBasic;
use service\SystemConfigService;
use service\WechatTemplateService;
use think\Url;
use traits\ModelTrait;
use app\routine\model\user\WechatUser;
class UserExtract extends ModelBasic
{
    use ModelTrait;

    //审核中
    const AUDIT_STATUS = 0;
    //未通过
    const FAIL_STATUS = -1;
    //已提现
    const SUCCESS_STATUS = 1;

    protected static $extractType = ['alipay','bank','weixin'];

    protected static $extractTypeMsg = ['alipay'=>'支付宝','bank'=>'银行卡','weixin'=>'微信'];

    protected static $status = array(
        -1=>'未通过',
        0 =>'审核中',
        1 =>'已提现'
    );

    public static function userExtract($userInfo,$data){
        if(!in_array($data['extract_type'],self::$extractType))
            return self::setErrorInfo('提现方式不存在');
        $userInfo = User::get($userInfo['uid']);
        if($data['money'] > $userInfo['now_money']) return self::setErrorInfo('余额不足');;
        $balance = bcsub($userInfo['now_money'],$data['money'],2);
        $insertData = [
            'uid'=>$userInfo['uid'],
            'extract_type'=>$data['extract_type'],
            'extract_price'=>(int)$data['money'],
            'add_time'=>time(),
            'balance'=>$balance,
            'status'=>self::AUDIT_STATUS
        ];
        if(isset($data['$name'])){
            $insertData['real_name']=$data['$name'];
        }else{
            $insertData['real_name']='';
        }
        if(isset($data['cardnum'])){
            $insertData['bank_code']=$data['cardnum'];
        }else{
            $insertData['bank_code']='';
        }
        if(isset($data['bankname'])){
            $insertData['bank_address']=$data['bankname'];
        }else{
            $insertData['bank_address']='';
        }
        if(isset($data['weixin'])){
            $insertData['wechat']=$data['weixin'];
        }else{
            $insertData['wechat']='';
        }
        if($data['extract_type'] == 'alipay'){
            if(!$data['alipay_code']) return self::setErrorInfo('请输入支付宝账号');
            $insertData['alipay_code'] = $data['alipay_code'];
            $mark = '使用支付宝提现'.$insertData['extract_price'].'元';
        }elseif($data['extract_type'] == 'bank'){
            if(!$data['cardnum']) return self::setErrorInfo('请输入银行卡账号');
            if(!$data['bankname']) return self::setErrorInfo('请输入开户行信息');
            $mark = '使用银联卡'.$insertData['bank_code'].'提现'.$insertData['extract_price'].'元';
        }else{
            if(!$data['weixin']) return self::setErrorInfo('请输入微信号');
            $mark = '使用微信提现'.$insertData['extract_price'].'元';
        }
        self::beginTrans();
        $res1 = self::set($insertData);
        if(!$res1) return self::setErrorInfo('提现失败');
        $res2 = User::edit(['now_money'=>$balance],$userInfo['uid'],'uid');
        $res3 = UserBill::expend('余额提现',$userInfo['uid'],'now_money','extract',$data['money'],$res1['id'],$balance,$mark);
        $res = $res2 && $res3;
        self::checkTrans($res);
        if($res){
            //发送模板消息
            return true;
        }
        else return self::setErrorInfo('提现失败!');
    }

    /**
     * 获得用户最后一次提现信息
     * @param $openid
     * @return mixed
     */
    public static function userLastInfo($uid)
    {
        return self::where(compact('uid'))->order('add_time DESC')->find();
    }

    /**
     * 获得用户提现总金额
     * @param $uid
     * @return mixed
     */
    public static function userExtractTotalPrice($uid)
    {
        return self::where('uid',$uid)->where('status',self::SUCCESS_STATUS)->value('SUM(extract_price)')?:0;
    }

}