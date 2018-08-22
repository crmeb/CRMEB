<?php
namespace app\admin\controller\finance;
use app\admin\controller\AuthController;
use app\admin\model\user\UserRecharge as UserRechargeModel;
use app\wap\model\user\UserBill;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Url;
use service\FormBuilder as Form;
use think\Request;
use service\HookService;
use behavior\wechat\PaymentBehavior;
use service\WechatTemplateService;
use app\wap\model\user\WechatUser as WechatUserWap;
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
        $where = Util::getMore([
            ['order_id',''],
        ],$this->request);
        $this->assign('where',$where);
        $this->assign(UserRechargeModel::systemPage($where));
        return $this->fetch();
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
            $form = Form::make_post_form('编辑',$f,Url::build('updateRefundY',array('id'=>$id)));
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        }
        else return Json::fail('数据不存在!');
    }

    /**退款更新
     * @param Request $request
     * @param $id
     */
    public function updateRefundY(Request $request, $id){
        $data = Util::postMore([
            'refund_price',
        ],$request);
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
        try{
            HookService::listen('user_recharge_refund',$UserRecharge['order_id'],$refund_data,true,PaymentBehavior::class);
        }catch(\Exception $e){
            return Json::fail($e->getMessage());
        }
        UserRechargeModel::edit($data,$id);
        WechatTemplateService::sendTemplate(WechatUserWap::uidToOpenid($UserRecharge['uid']),WechatTemplateService::ORDER_REFUND_STATUS, [
            'first'=>'亲，您充值的金额已退款,本次退款'.
                $data['refund_price'].'金额',
            'keyword1'=>$UserRecharge['order_id'],
            'keyword2'=>$UserRecharge['price'],
            'keyword3'=>date('Y-m-d H:i:s',$UserRecharge['add_time']),
            'remark'=>'点击查看订单详情'
        ],Url::build('wap/My/balance','',true,true));
        UserBill::expend('系统退款',$UserRecharge['uid'],'now_money','user_recharge_refund',$refund_price,$id,$UserRecharge['price'],'退款给用户'.$refund_price.'元');
        return Json::successful('退款成功!');
    }
}
