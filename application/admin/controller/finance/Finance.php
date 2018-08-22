<?php
/**
 * Created by PhpStorm.
 * User: xurongyao <763569752@qq.com>
 * Date: 2018/6/14 下午5:25
 */

namespace app\admin\controller\finance;

use app\admin\controller\AuthController;
use app\admin\model\user\UserBill;
use service\JsonService as Json;
use app\admin\model\finance\FinanceModel;
use service\UtilService as Util;
use service\FormBuilder as Form;
//use FormBuilder\Form;
use service\HookService;
use think\Url;
use app\admin\model\user\User;
use app\admin\model\user\UserExtract;

/**
 * 微信充值记录
 * Class UserRecharge
 * @package app\admin\controller\user
 */
class Finance extends AuthController

{

    /**
     * 显示操作记录
     */
    public function index(){

        //创建form
        $form = Form::create('/save.php',[
            Form::input('goods_name','商品名称')
            ,Form::input('goods_name1','password')->type('password')
            ,Form::input('goods_name2','textarea')->type('textarea')
            ,Form::input('goods_name3','email')->type('email')
            ,Form::input('goods_name4','date')->type('date')
            ,Form::cityArea('address','cityArea',[
                '陕西省','西安市'
            ])
            ,Form::dateRange('limit_time','dateRange',
                strtotime('- 10 day'),
                time()
            )
            ,Form::dateTime('add_time','dateTime')
            ,Form::color('color','color','#ff0000')
            ,Form::checkbox('checkbox','checkbox',[1])->options([['value'=>1,'label'=>'白色'],['value'=>2,'label'=>'红色'],['value'=>31,'label'=>'黑色']])
            ,Form::date('riqi','date','2018-03-1')
            ,Form::dateTimeRange('dateTimeRange','区间时间段')
            ,Form::year('year','year')
            ,Form::month('month','month')
            ,Form::frame('frame','frame','http://baidu.com')
            ,Form::frameInputs('month','frameInputs','http://baidu.com')
            ,Form::frameFiles('month1','frameFiles','http://baidu.com')
            ,Form::frameImages('month2','frameImages','http://baidu.com')
            ,Form::frameInputOne('month3','frameInputOne','http://baidu.com')
            ,Form::frameFileOne('month4','frameFileOne','http://baidu.com')
            ,Form::frameImageOne('month5','frameImageOne','http://baidu.com')
            ,Form::hidden('month6','hidden')
            ,Form::number('month7','number')
//            ,Form::input input输入框,其他type: text类型Form::text,password类型Form::password,textarea类型Form::textarea,url类型Form::url,email类型Form::email,date类型Form::idate
            ,Form::radio('month8','radio')->options([['value'=>1,'label'=>'白色'],['value'=>2,'label'=>'红色'],['value'=>31,'label'=>'黑色']])
            ,Form::rate('month9','rate')
            ,Form::select('month10','select')->options([['value'=>1,'label'=>'白色'],['value'=>2,'label'=>'红色'],['value'=>31,'label'=>'黑色']])
            ,Form::selectMultiple('month11','selectMultiple')
            ,Form::selectOne('month12','selectOne')
            ,Form::slider('month13','slider',2)
            ,Form::sliderRange('month23','sliderRange',2,13)
            ,Form::switches('month14','区间时间段')
            ,Form::timePicker('month15','区间时间段')
            ,Form::time('month16','区间时间段')
            ,Form::timeRange('month17','区间时间段')
//            ,Form::upload('month','区间时间段')
//            ,Form::uploadImages('month','区间时间段')
//            ,Form::uploadFiles('month','区间时间段')
//            ,Form::uploadImageOne('month','区间时间段')
//            ,Form::uploadFileOne('month','区间时间段')

        ]);
        $html = $form->setMethod('get')->setTitle('编辑商品')->view();
        echo $html;

    }
    /**
     * 显示资金记录
     */
    public function bill(){
        $list=UserBill::where('type','not in',['gain','system_sub','deduction','sign'])
            ->where('category','not in','integral')
            ->field(['title','type'])
            ->group('type')
            ->distinct(true)
            ->select()
            ->toArray();
        $this->assign('selectList',$list);
        return $this->fetch();
    }
    /**
     * 显示资金记录ajax列表
     */
    public function billlist(){
        $where = Util::getMore([
            ['start_time',''],
            ['end_time',''],
            ['nickname',''],
            ['limit',20],
            ['page',1],
            ['type',''],
        ]);
        return Json::successlayui(FinanceModel::getBillList($where));
    }
    /**
     *保存资金监控的excel表格
     */
    public function save_bell_export(){
        $where = Util::getMore([
            ['start_time',''],
            ['end_time',''],
            ['nickname',''],
            ['type',''],
        ]);
        FinanceModel::SaveExport($where);
    }
//    /**
//     * 显示佣金记录
//     */
//    public function commission_list(){
//
//        //创建form
//        $form = Form::create('/save.php',[
//            Form::input('goods_name','商品名称')
//            ,Form::input('goods_name1','password')->type('password')
//            ,Form::input('goods_name3','email')->type('email')
//            ,Form::input('goods_name4','date')->type('date')
//            ,Form::cityArea('address','cityArea',[
//                '陕西省','西安市'
//            ])
//            ,Form::dateRange('limit_time','dateRange',
//                strtotime('- 10 day'),
//                time()
//            )
//            ,Form::dateTime('add_time','dateTime')
//            ,Form::color('color','color','#ff0000')
//            ,Form::checkbox('checkbox','checkbox',[1])->options([['value'=>1,'label'=>'白色'],['value'=>2,'label'=>'红色'],['value'=>31,'label'=>'黑色']])
//            ,Form::date('riqi','date','2018-03-1')
//            ,Form::dateTimeRange('dateTimeRange','区间时间段')
//            ,Form::year('year','year')
//
//            ,Form::hidden('month6','hidden')
//            ,Form::number('month7','number')
//
//
//        ]);
//        $rule = $form->setMethod('post')->setTitle('编辑商品')->getRules();
//        $action = Url::build('save');
//        $this->assign(compact('form','rule','action'));
//        return $this->fetch();
//    }
    /**
     * 显示佣金记录
     */
    public function commission_list(){
        $this->assign('is_layui',true);
        return $this->fetch();
    }
    /**
     * 佣金记录异步获取
     */
    public function get_commission_list(){
        $get=Util::getMore([
            ['page',1],
            ['limit',20],
            ['nickname',''],
            ['price_max',''],
            ['price_min',''],
            ['order','']
        ]);
        return Json::successlayui(User::getCommissionList($get));
    }
    /**
     * 保存excel表格
     */
    public function save_export(){
        $get=Util::getMore([
            ['page',1],
            ['limit',20],
            ['nickname',''],
            ['price_max',''],
            ['price_min',''],
            ['order','']
        ]);
        User::setUserWhere($get,true);
    }
    /**
     * 显示操作记录
     */
    public function index3(){

    }
    /**
     * 佣金详情
     */
    public function content_info($uid=''){
        if($uid=='') return $this->failed('缺少参数');
        $this->assign('userinfo',User::getUserinfo($uid));
        $this->assign('uid',$uid);
        return $this->fetch();
    }
    /**
     * 佣金提现记录个人列表
     */
    public function get_extract_list($uid=''){
        if($uid=='') return Json::fail('缺少参数');
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
            ['start_time',''],
            ['end_time',''],
            ['nickname','']
        ]);
        return Json::successlayui(UserBill::getExtrctOneList($where,$uid));
    }

}

