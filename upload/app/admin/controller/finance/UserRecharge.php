<?php
namespace app\admin\controller\finance;
use app\admin\controller\AuthController;
use app\admin\model\user\User;
use app\admin\model\user\UserRecharge as UserRechargeModel;
use app\models\routine\RoutineTemplate;
use app\models\user\UserBill;
use crmeb\services\JsonService;
use crmeb\services\MiniProgramService;
use crmeb\services\UtilService;
use crmeb\services\WechatService;
use crmeb\services\UtilService as Util;
use crmeb\services\JsonService as Json;
use think\facade\Route as Url;
use crmeb\services\FormBuilder as Form;
use crmeb\services\WechatTemplateService;
use app\models\user\WechatUser as WechatUserWap;
/**
 * 微信充值记录
 * Class UserRecharge
 * @package app\admin\controller\user
 */
class UserRecharge extends AuthController
{
    /**
     * 显示操作记录
     */
    public function index(){
        $this->assign( 'year',getMonth());
        return $this->fetch();
    }

    public function get_user_recharge_list(){
        $where = UtilService::getMore([
            ['data',''],
            ['paid',''],
            ['page',1],
            ['limit',20],
            ['nickname',''],
            ['excel',''],
        ]);
        return JsonService::successlayui(UserRechargeModel::getUserRechargeList($where));
    }

    public function delect($id = 0){
        if(!$id) return JsonService::fail('缺少参数');
        $rechargInfo = UserRechargeModel::get($id);
        if($rechargInfo->paid) return JsonService::fail('已支付的订单记录无法删除');
        if(UserRechargeModel::del($id))
            return JsonService::successful('删除成功');
        else
            return JsonService::fail('删除失败');
    }

    public function get_badge(){
        $where = UtilService::getMore([
            ['data',''],
            ['paid',''],
            ['nickname',''],
        ]);
        return JsonService::successful(UserRechargeModel::getDataList($where));
    }
    /**退款
     * @param $id
     * @return mixed|void
     */
    public function edit($id){
        if(!$id) return $this->failed('数据不存在');
        $UserRecharge = UserRechargeModel::get($id);
        if(!$UserRecharge) return Json::fail('数据不存在!');
        if($UserRecharge['paid'] == 1){
            $f = array();
            $f[] = Form::input('order_id','退款单号',$UserRecharge->getData('order_id'))->disabled(1);
            $f[] = Form::number('refund_price','退款金额',$UserRecharge->getData('price'))->precision(2)->min(0)->max($UserRecharge->getData('price'));
            $jsContent = <<<SCRIPT
parent.SuccessFun();
parent.layer.close(parent.layer.getFrameIndex(window.name));
SCRIPT;
            $form = Form::make_post_form('编辑',$f,Url::buildUrl('updateRefundY',array('id'=>$id)),$jsContent);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }
        else return Json::fail('数据不存在!');
    }

    /**
     * 退款更新
     * @param $id
     */
    public function updateRefundY($id){
        $data = Util::postMore([
            'refund_price',
        ]);
        if(!$id) return $this->failed('数据不存在');
        $UserRecharge = UserRechargeModel::get($id);
        if(!$UserRecharge) return Json::fail('数据不存在!');
        if($UserRecharge['price'] == $UserRecharge['refund_price']) return Json::fail('已退完支付金额!不能再退款了');
        if(!$data['refund_price']) return Json::fail('请输入退款金额');
        $refund_price = $data['refund_price'];
        $data['refund_price'] = bcadd($data['refund_price'],$UserRecharge['refund_price'],2);
        $bj = bccomp((float)$UserRecharge['price'],(float)$data['refund_price'],2);
        if($bj < 0) return Json::fail('退款金额大于支付金额，请修改退款金额');
        $refund_data['pay_price'] = $UserRecharge['price'];
        $refund_data['refund_price'] = $refund_price;
//        $refund_data['refund_account']='REFUND_SOURCE_RECHARGE_FUNDS';
        try{
            $recharge_type = UserRechargeModel::where('order_id',$UserRecharge['order_id'])->value('recharge_type');
            if($recharge_type == 'weixin'){
                WechatService::payOrderRefund($UserRecharge['order_id'],$refund_data);
            }else{
                MiniProgramService::payOrderRefund($UserRecharge['order_id'],$refund_data);
            }
        }catch(\Exception $e){
            return Json::fail($e->getMessage());
        }
        UserRechargeModel::edit($data,$id);
        User::bcDec($UserRecharge['uid'],'now_money',$refund_price,'uid');
        switch (strtolower($UserRecharge['recharge_type'])){
            case 'weixin':
                WechatTemplateService::sendTemplate(WechatUserWap::where('uid',$UserRecharge['uid'])->value('openid'),WechatTemplateService::ORDER_REFUND_STATUS, [
                    'first'=>'亲，您充值的金额已退款,本次退款'.
                        $data['refund_price'].'金额',
                    'keyword1'=>$UserRecharge['order_id'],
                    'keyword2'=>$UserRecharge['price'],
                    'keyword3'=>date('Y-m-d H:i:s',$UserRecharge['add_time']),
                    'remark'=>'点击查看订单详情'
                ],Url::buildUrl('wap/My/balance','',true,true));
                break;
            case 'routine':
                RoutineTemplate::sendOut('ORDER_REFUND_SUCCESS',$UserRecharge['uid'],[
                    'keyword1'=>$UserRecharge['order_id'],
                    'keyword2'=>date('Y-m-d H:i:s',time()),
                    'keyword3'=>$UserRecharge['price'],
                    'keyword4'=>'余额充值退款',
                    'keyword5'=>'亲，您充值的金额已退款,本次退款'. $data['refund_price'].'金额',
                ]);
                break;
        }
        UserBill::expend('系统退款',$UserRecharge['uid'],'now_money','user_recharge_refund',$refund_price,$id,$UserRecharge['price'],'退款给用户'.$refund_price.'元');
        return Json::successful('退款成功!');
    }
}
