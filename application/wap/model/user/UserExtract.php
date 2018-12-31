<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/3/3
 */

namespace app\wap\model\user;


use basic\ModelBasic;
use service\SystemConfigService;
use service\WechatTemplateService;
use think\Url;
use traits\ModelTrait;

class UserExtract extends ModelBasic
{
    use ModelTrait;

    //审核中
    const AUDIT_STATUS = 0;
    //未通过
    const FAIL_STATUS = -1;
    //已提现
    const SUCCESS_STATUS = 1;

    protected static $extractType = ['alipay','bank'];

    protected static $extractTypeMsg = ['alipay'=>'支付宝','bank'=>'银行卡'];

    protected static $status = array(
        -1=>'未通过',
        0 =>'审核中',
        1 =>'已提现'
    );

    public static function userExtract($userInfo,$data){
        if(!in_array($data['extract_type'],self::$extractType))
            return self::setErrorInfo('提现方式不存在');
        if($userInfo['now_money'] < $data['extract_price'])
            return self::setErrorInfo('余额不足');
        if(!$data['real_name'])
            return self::setErrorInfo('输入姓名有误');
        $extractMinPrice = floatval(SystemConfigService::get('user_extract_min_price'))?:0;
        if($data['extract_price'] < $extractMinPrice)
            return self::setErrorInfo('提现金额不能小于'.$extractMinPrice);
        $balance = bcsub($userInfo['now_money'],$data['extract_price']);
        $insertData = [
            'uid'=>$userInfo['uid'],
            'real_name'=>$data['real_name'],
            'extract_type'=>$data['extract_type'],
            'extract_price'=>($data['extract_price']),
            'add_time'=>time(),
            'balance'=>$balance,
            'status'=>self::AUDIT_STATUS
        ];
        if($data['extract_type'] == 'alipay'){
            if(!$data['alipay_code']) return self::setErrorInfo('请输入支付宝账号');
            $insertData['alipay_code'] = $data['alipay_code'];
            $mark = '使用支付宝提现'.$insertData['extract_price'].'元';
        }else{
            if(!$data['bank_code']) return self::setErrorInfo('请输入银行卡账号');
            if(!$data['bank_address']) return self::setErrorInfo('请输入开户行信息');
            $insertData['bank_code'] = $data['bank_code'];
            $insertData['bank_address'] = $data['bank_address'];
            $mark = '使用银联卡'.$insertData['bank_code'].'提现'.$insertData['extract_price'].'元';
        }
        self::beginTrans();
        $res1 = self::set($insertData);
        if(!$res1) return self::setErrorInfo('提现失败');
        $res2 = User::edit(['now_money'=>$balance],$userInfo['uid'],'uid');
        $res3 = UserBill::expend('余额提现',$userInfo['uid'],'now_money','extract',$data['extract_price'],$res1['id'],$balance,$mark);
        $res = $res2 && $res3;
        WechatTemplateService::sendTemplate(
            WechatUser::uidToOpenid($userInfo['uid']),
            WechatTemplateService::USER_BALANCE_CHANGE,
            [
                'first'=>'你好,申请余额提现成功!',
                'keyword1'=>'余额提现',
                'keyword2'=>date('Y-m-d'),
                'keyword3'=>$data['extract_price'],
                'remark'=>'点击查看我的余额明细'
            ],
            Url::build('wap/My/balance',[],true,true)
            );
        if($res)
            return true;
        else
            return self::setErrorInfo('提现失败!');
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