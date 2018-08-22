<?php
/**
 * Created by PhpStorm.
 * User: lianghuan
 * Date: 2018-03-03
 * Time: 16:47
 */

namespace app\admin\model\user;

use app\admin\model\user\User;
use app\admin\model\user\UserBill;
use app\admin\model\wechat\WechatUser;
use think\Url;
use traits\ModelTrait;
use basic\ModelBasic;
use service\WechatTemplateService;
/**
 * 用户提现管理 model
 * Class User
 * @package app\admin\model\user
 */
class UserExtract extends ModelBasic
{
    use ModelTrait;
    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where)
    {
        $model = new self;
        if($where['status'] != '')  $model = $model->where('a.status',$where['status']);
        if($where['extract_type'] != '')  $model = $model->where('a.extract_type',$where['extract_type']);
        if($where['nireid'] != '') $model = $model->where('a.real_name|a.id|b.nickname|a.bank_code|a.alipay_code','like',"%$where[nireid]%");
        $model = $model->alias('a');
        $model = $model->field('a.*,b.nickname');
        $model = $model->join('__USER__ b','b.uid=a.uid','LEFT');
        $model = $model->order('a.id desc');
        return self::page($model);
    }

    public static function changeFail($id,$fail_msg)
    {
        $fail_time = time();
        $data =self::get($id);
        $extract_number=$data['extract_price'];
        $mark='提现失败,退回佣金'.$extract_number.'元';
        $uid=$data['uid'];
        $status = -1;
        $User= User::find(['uid'=>$uid])->toArray();
        UserBill::income('提现失败',$uid,'now_money','extract',$extract_number,$id,$User['now_money'],$mark);

        User::bcInc($uid,'now_money',$extract_number,'uid');
        WechatTemplateService::sendTemplate(WechatUser::uidToOpenid($uid),WechatTemplateService::USER_BALANCE_CHANGE,[
            'first'=> $mark,
            'keyword1'=>'佣金提现',
            'keyword2'=>date('Y-m-d H:i:s',time()),
            'keyword3'=>$extract_number,
            'remark'=>'错误原因:'.$fail_msg
        ],Url::build('wap/my/user_pro',[],true,true));
        return self::edit(compact('fail_time','fail_msg','status'),$id);
    }

    public static function changeSuccess($id)
    {
        $status = 1;
        $data =self::get($id);
        $extract_number=$data['extract_price'];
        $mark='成功提现佣金'.$extract_number.'元';
        $uid=$data['uid'];
        WechatTemplateService::sendTemplate(WechatUser::uidToOpenid($uid),WechatTemplateService::USER_BALANCE_CHANGE,[
            'first'=> $mark,
            'keyword1'=>'佣金提现',
            'keyword2'=>date('Y-m-d H:i:s',time()),
            'keyword3'=>$extract_number,
            'remark'=>'点击查看我的佣金明细'
        ],Url::build('wap/my/user_pro',[],true,true));
        return self::edit(compact('status'),$id);
    }
    //测试数据
    public static function test(){
        $uids=User::order('uid desc')->limit(2,20)->field(['uid','nickname'])->select()->toArray();
        $type=['bank','alipay','weixin'];
        foreach ($uids as $item){
            $data=[
                'uid'=>$item['uid'],
                'real_name'=>$item['nickname'],
                'extract_type'=>isset($type[rand(0,2)]) ? $type[rand(0,2)] :'alipay',
                'bank_code'=>rand(1000000,999999999),
                'bank_address'=>'中国',
                'alipay_code'=>rand(1000,9999999),
                'extract_price'=>rand(100,9999),
                'mark'=>'测试数据',
                'add_time'=>time(),
                'status'=>1,
                'wechat'=>rand(999,878788).$item['uid'],
            ];
            self::set($data);
        }
    }
    //获取头部提现信息
    public static function getExtractHead(){
        //本月提现人数
        $month=self::getModelTime(['data'=>'month'],self::where(['status'=>1]))->group('uid')->count();
        //本月提现笔数
        $new_month=self::getModelTime(['data'=>'month'],self::where(['status'=>1]))->distinct(true)->count();
        //上月提现人数
        $last_month=self::whereTime('add_time','last month')->where('status',1)->group('uid')->distinct(true)->count();
        //上月提现笔数
        $last_count=self::whereTime('add_time','last month')->where('status',1)->count();
        //本月提现金额
        $extract_price=self::getModelTime(['data'=>'month'],self::where(['status'=>1]))->sum('extract_price');
        //上月提现金额
        $last_extract_price=self::whereTime('add_time','last month')->where('status',1)->sum('extract_price');

        return [
            [
                'name'=>'总提现人数',
                'field'=>'个',
                'count'=>self::where(['status'=>1])->group('uid')->count(),
                'content'=>'',
                'background_color'=>'layui-bg-blue',
                'sum'=>'',
                'class'=>'fa fa-bar-chart',
            ],
            [
                'name'=>'总提现笔数',
                'field'=>'笔',
                'count'=>self::where(['status'=>1])->distinct(true)->count(),
                'content'=>'',
                'background_color'=>'layui-bg-cyan',
                'sum'=>'',
                'class'=>'fa fa-line-chart',
            ],
            [
                'name'=>'本月提现人数',
                'field'=>'人',
                'count'=>$month,
                'content'=>'',
                'background_color'=>'layui-bg-orange',
                'sum'=>'',
                'class'=>'fa fa-line-chart',
            ],
            [
                'name'=>'本月提现笔数',
                'field'=>'笔',
                'count'=>$new_month,
                'content'=>'',
                'background_color'=>'layui-bg-green',
                'sum'=>'',
                'class'=>'fa fa-line-chart',
            ],
            [
                'name'=>'本月提现金额',
                'field'=>'元',
                'count'=>$extract_price,
                'content'=>'提现总金额',
                'background_color'=>'layui-bg-cyan',
                'sum'=>self::where(['status'=>1])->sum('extract_price'),
                'class'=>'fa fa-line-chart',
            ],
            [
                'name'=>'上月提现人数',
                'field'=>'个',
                'count'=>$last_month,
                'content'=>'环比增幅',
                'background_color'=>'layui-bg-blue',
                'sum'=>$last_month==0 ? '100%' :bcdiv($month,$last_month,2)*100,
                'class'=>$last_month==0 ? 'fa fa-level-up':'fa fa-level-down',
            ],
            [
                'name'=>'上月提现笔数',
                'field'=>'笔',
                'count'=>$last_count,
                'content'=>'环比增幅',
                'background_color'=>'layui-bg-black',
                'sum'=>$last_count==0 ? '100%':bcdiv($new_month,$last_count,2)*100,
                'class'=>$last_count==0 ? 'fa fa-level-up':'fa fa-level-down',
            ],
            [
                'name'=>'上月提现金额',
                'field'=>'元',
                'count'=>$last_extract_price,
                'content'=>'环比增幅',
                'background_color'=>'layui-bg-gray',
                'sum'=>$last_extract_price==0 ? '100%':bcdiv($extract_price,$last_extract_price,2)*100,
                'class'=>$last_extract_price==0 ? 'fa fa-level-up':'fa fa-level-down',
            ],
        ];
    }
    //获取提现分布图和提现人数金额曲线图
    public static function getExtractList($where,$limit=15){
        $legdata=['提现人数','提现金额'];
        $list=self::getModelTime($where,self::where('status',1))
            ->field([
                'FROM_UNIXTIME(add_time,"%Y-%c-%d") as un_time',
                'count(uid) as count',
                'sum(extract_price) as sum_price',
            ])->group('un_time')->order('un_time asc')->select();
        if(count($list)) $list=$list->toArray();
        $xdata=[];
        $itemList=[0=>[],1=>[]];
        $chatrList=[];
        $zoom='';
        foreach ($list as $value){
            $xdata[]=$value['un_time'];
            $itemList[0][]=$value['count'];
            $itemList[1][]=$value['sum_price'];
        }
        foreach ($legdata as $key=>$name){
            $item['name']=$name;
            $item['type']='line';
            $item['data']=$itemList[$key];
            $chatrList[]=$item;
        }
        unset($item,$name,$key);
        if(count($xdata)>$limit) $zoom=$xdata[$limit-5];
        //饼状图
        $cake=['支付宝','银行卡','微信'];
        $fenbulist=self::getModelTime($where,self::where('status',1))
            ->field(['count(uid) as count','extract_type'])->group('extract_type')->order('count asc')->select();
        if(count($fenbulist)) $fenbulist=$fenbulist->toArray();
        $sum_count=self::getModelTime($where,self::where('status',1))->count();
        $color=['#FB7773','#81BCFE','#91F3FE'];
        $fenbudata=[];
        foreach ($fenbulist as $key=>$item){
            if($item['extract_type']=='bank'){
                $item_date['name']='银行卡';
            }else if($item['extract_type']=='alipay'){
                $item_date['name']='支付宝';
            }else if($item['extract_type']=='weixin'){
                $item_date['name']='微信';
            }
            $item_date['value']=bcdiv($item['count'],$sum_count,2)*100;
            $item_date['itemStyle']['color']=$color[$key];
            $fenbudata[]=$item_date;
        }
        return compact('xdata','chatrList','legdata','zoom','cake','fenbudata');
    }

    /**
     * 获取用户累计提现金额
     * @param int $uid
     * @return int|mixed
     */
    public static function getUserCountPrice($uid = 0){
        if(!$uid) return 0;
        $price = self::where('uid',$uid)->where('status',1)->field('sum(extract_price) as price')->find()['price'];
        return $price ? $price : 0;
    }

    /**
     * 获取用户累计提现次数
     * @param int $uid
     * @return int|string
     */
    public static function getUserCountNum($uid = 0){
        if(!$uid) return 0;
        return self::where('uid',$uid)->count();
    }
}