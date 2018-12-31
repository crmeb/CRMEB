<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\order;


use app\admin\model\wechat\WechatUser;
use app\admin\model\ump\StorePink;
use app\admin\model\order\StoreOrderCartInfo;
use app\admin\model\store\StoreProduct;
use app\admin\model\routine\RoutineFormId;
use app\admin\model\routine\RoutineTemplate;
use service\PHPExcelService;
use traits\ModelTrait;
use basic\ModelBasic;
use service\WechatTemplateService;
use service\RoutineTemplateService;
use think\Url;
use think\Db;
/**
 * 订单管理Model
 * Class StoreOrder
 * @package app\admin\model\store
 */
class StoreOrder extends ModelBasic
{
    use ModelTrait;

    public static function orderCount(){
        $data['wz']=self::statusByWhere(0,new self())->count();
        $data['wf']=self::statusByWhere(1,new self())->count();
        $data['ds']=self::statusByWhere(2,new self())->count();
        $data['dp']=self::statusByWhere(3,new self())->count();
        $data['jy']=self::statusByWhere(4,new self())->count();
        $data['tk']=self::statusByWhere(-1,new self())->count();
        $data['yt']=self::statusByWhere(-2,new self())->count();
        $data['general']=self::where(['pink_id'=>0,'combination_id'=>0,'seckill_id'=>0])->count();
        $data['pink']=self::where('pink_id|combination_id','neq',0)->count();
        $data['seckill']=self::where('seckill_id','neq',0)->count();
        return $data;
    }

    public static function OrderList($where){
        $model = self::getOrderWhere($where,self::alias('a')->join('user r','r.uid=a.uid','LEFT'),'a.','r')->field('a.*,r.nickname');
        if($where['order']!=''){
            $model = $model->order(self::setOrder($where['order']));
        }else{
            $model = $model->order('a.id desc');
        }
        if(isset($where['excel']) && $where['excel']==1){
            $data=($data=$model->select()) && count($data) ? $data->toArray() : [];
        }else{
            $data=($data=$model->page((int)$where['page'],(int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        }
//        $data=($data=$model->page((int)$where['page'],(int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            $_info = Db::name('store_order_cart_info')->where('oid',$item['id'])->field('cart_info')->select();
            foreach ($_info as $k=>$v){
                $_info[$k]['cart_info'] = json_decode($v['cart_info'],true);
            }
            $item['_info'] = $_info;
            $item['add_time'] = date('Y-m-d H:i:s',$item['add_time']);
            if($item['pink_id'] && $item['combination_id']){
                $pinkStatus = StorePink::where('order_id_key',$item['id'])->value('status');
                switch ($pinkStatus){
                    case 1:
                        $item['pink_name'] = '[拼团订单]正在进行中';
                        $item['color'] = '#f00';
                        break;
                    case 2:
                        $item['pink_name'] = '[拼团订单]已完成';
                        $item['color'] = '#00f';
                        break;
                    case 3:
                        $item['pink_name'] = '[拼团订单]未完成';
                        $item['color'] = '#f0f';
                        break;
                    default:
                        $item['pink_name'] = '[拼团订单]历史订单';
                        $item['color'] = '#457856';
                        break;
                }
            }else{
                if($item['seckill_id']){
                    $item['pink_name'] = '[秒杀订单]';
                    $item['color'] = '#32c5e9';
                }else{
                    $item['pink_name'] = '[普通订单]';
                    $item['color'] = '#895612';
                }
            }
            if($item['paid']==1){
                switch ($item['pay_type']){
                    case 'weixin':
                        $item['pay_type_name']='微信支付';
                        break;
                    case 'yue':
                        $item['pay_type_name']='余额支付';
                        break;
                    case 'offline':
                        $item['pay_type_name']='线下支付';
                        break;
                    default:
                        $item['pay_type_name']='其他支付';
                        break;
                }
            }else{
                switch ($item['pay_type']){
                    default:
                        $item['pay_type_name']='未支付';
                        break;
                    case 'offline':
                        $item['pay_type_name']='线下支付';
                        $item['pay_type_info']=1;
                        break;
                }
            }
            if($item['paid']==0 && $item['status']==0){
                $item['status_name']='未支付';
            }else if($item['paid']==1 && $item['status']==0 && $item['refund_status']==0){
                $item['status_name']='未发货';
            }else if($item['paid']==1 && $item['status']==1 && $item['refund_status']==0){
                $item['status_name']='待收货';
            }else if($item['paid']==1 && $item['status']==2 && $item['refund_status']==0){
                $item['status_name']='待评价';
            }else if($item['paid']==1 && $item['status']==3 && $item['refund_status']==0){
                $item['status_name']='已完成';
            }else if($item['paid']==1 && $item['refund_status']==1){
                $item['status_name']=<<<HTML
<b style="color:#f124c7">申请退款</b><br/>
<span>退款原因：{$item['refund_reason_wap']}</span>
HTML;
            }else if($item['paid']==1 && $item['refund_status']==2){
                $item['status_name']='已退款';
            }
            if($item['paid']==0 && $item['status']==0 && $item['refund_status']==0){
                $item['_status']=1;
            }else if($item['paid']==1 && $item['status']==0 && $item['refund_status']==0){
                $item['_status']=2;
            }else if($item['paid']==1 && $item['refund_status']==1){
                $item['_status']=3;
            }else if($item['paid']==1 && $item['status']==1 && $item['refund_status']==0){
                $item['_status']=4;
            }else if($item['paid']==1 && $item['status']==2 && $item['refund_status']==0){
                $item['_status']=5;
            }else if($item['paid']==1 && $item['status']==3 && $item['refund_status']==0){
                $item['_status']=6;
            }else if($item['paid']==1 && $item['refund_status']==2){
                $item['_status']=7;
            }
        }
        if(isset($where['excel']) && $where['excel']==1){
            self::SaveExcel($data);
        }
        $count=self::getOrderWhere($where,self::alias('a')->join('user r','r.uid=a.uid','LEFT'),'a.','r')->count();
        return compact('count','data');
    }
    /*
     * 保存并下载excel
     * $list array
     * return
     */
    public static function SaveExcel($list){
        $export = [];
        foreach ($list as $index=>$item){
            $_info = Db::name('store_order_cart_info')->where('oid',$item['id'])->column('cart_info');
            $goodsName = [];
            foreach ($_info as $k=>$v){
                $v = json_decode($v,true);
                $goodsName[] = implode(
                    [$v['productInfo']['store_name'],
                        isset($v['productInfo']['attrInfo']) ? '('.$v['productInfo']['attrInfo']['suk'].')' : '',
                        "[{$v['cart_num']} * {$v['truePrice']}]"
                    ],' ');
            }
            $item['cartInfo'] = $_info;
            $export[] = [
                $item['order_id'],$item['pay_type_name'],
                $item['total_num'],$item['total_price'],$item['total_postage'],$item['pay_price'],$item['refund_price'],
                $item['mark'],$item['remark'],
                [$item['real_name'],$item['user_phone'],$item['user_address']],
                $goodsName,
                [$item['paid'] == 1? '已支付':'未支付','支付时间: '.($item['pay_time'] > 0 ? date('Y/md H:i',$item['pay_time']) : '暂无')]
            ];
        }
        PHPExcelService::setExcelHeader(['订单号','支付方式','商品总数','商品总价','邮费','支付金额','退款金额','用户备注','管理员备注','收货人信息','商品信息','支付状态'])
            ->setExcelTile('订单导出','订单信息'.time(),' 生成时间：'.date('Y-m-d H:i:s',time()))
            ->setExcelContent($export)
            ->ExcelSave();
    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where,$userid=false){
        $model = self::getOrderWhere($where,self::alias('a')->join('user r','r.uid=a.uid','LEFT'),'a.','r')->field('a.*,r.nickname');
        if($where['order']){
            $model = $model->order('a.'.$where['order']);
        }else{
            $model = $model->order('a.id desc');
        }
        if($where['export'] == 1){
            $list = $model->select()->toArray();
            $export = [];
            foreach ($list as $index=>$item){

                if ($item['pay_type'] == 'weixin'){
                    $payType = '微信支付';
                }elseif($item['pay_type'] == 'yue'){
                    $payType = '余额支付';
                }elseif($item['pay_type'] == 'offline'){
                    $payType = '线下支付';
                }else{
                    $payType = '其他支付';
                }

                $_info =  Db::name('store_order_cart_info')->where('oid',$item['id'])->column('cart_info');
                $goodsName = [];
                foreach ($_info as $k=>$v){
                    $v = json_decode($v,true);
                    $goodsName[] = implode(
                        [$v['productInfo']['store_name'],
                            isset($v['productInfo']['attrInfo']) ? '('.$v['productInfo']['attrInfo']['suk'].')' : '',
                            "[{$v['cart_num']} * {$v['truePrice']}]"
                        ],' ');
                }
                $item['cartInfo'] = $_info;
                $export[] = [
                    $item['order_id'],$payType,
                    $item['total_num'],$item['total_price'],$item['total_postage'],$item['pay_price'],$item['refund_price'],
                    $item['mark'],$item['remark'],
                    [$item['real_name'],$item['user_phone'],$item['user_address']],
                    $goodsName,
                    [$item['paid'] == 1? '已支付':'未支付','支付时间: '.($item['pay_time'] > 0 ? date('Y/md H:i',$item['pay_time']) : '暂无')]

                ];
                $list[$index] = $item;
            }
            PHPExcelService::setExcelHeader(['订单号','支付方式','商品总数','商品总价','邮费','支付金额','退款金额','用户备注','管理员备注','收货人信息','商品信息','支付状态'])
                ->setExcelTile('订单导出','订单信息'.time(),' 生成时间：'.date('Y-m-d H:i:s',time()))
                ->setExcelContent($export)
                ->ExcelSave();
        }
        return self::page($model,function ($item){
            $_info =  Db::name('store_order_cart_info')->where('oid',$item['id'])->field('cart_info')->select();
            foreach ($_info as $k=>$v){
                $_info[$k]['cart_info'] = json_decode($v['cart_info'],true);
            }
            $item['_info'] = $_info;
            if($item['pink_id'] && $item['combination_id']){
                $pinkStatus = StorePink::where('order_id_key',$item['id'])->value('status');
                switch ($pinkStatus){
                    case 1:
                        $item['pink_name'] = '[拼团订单]正在进行中';
                        $item['color'] = '#f00';
                        break;
                    case 2:
                        $item['pink_name'] = '[拼团订单]已完成';
                        $item['color'] = '#00f';
                        break;
                    case 3:
                        $item['pink_name'] = '[拼团订单]未完成';
                        $item['color'] = '#f0f';
                        break;
                    default:
                        $item['pink_name'] = '[拼团订单]历史订单';
                        $item['color'] = '#457856';
                        break;
                }
            }else{
               if($item['seckill_id']){
                   $item['pink_name'] = '[秒杀订单]';
                   $item['color'] = '#32c5e9';
               }else{
                   $item['pink_name'] = '[普通订单]';
                   $item['color'] = '#895612';
               }
            }
        },$where);
    }

    public static function statusByWhere($status,$model = null,$alert='')
    {
        if($model == null) $model = new self;
        if('' === $status)
            return $model;
        else if($status == 0)//未支付
            return $model->where($alert.'paid',0)->where($alert.'status',0)->where($alert.'refund_status',0);
        else if($status == 1)//已支付 未发货
            return $model->where($alert.'paid',1)->where($alert.'status',0)->where($alert.'refund_status',0);
        else if($status == 2)//已支付  待收货
            return $model->where($alert.'paid',1)->where($alert.'status',1)->where($alert.'refund_status',0);
        else if($status == 3)// 已支付  已收货  待评价
            return $model->where($alert.'paid',1)->where($alert.'status',2)->where($alert.'refund_status',0);
        else if($status == 4)// 交易完成
            return $model->where($alert.'paid',1)->where($alert.'status',3)->where($alert.'refund_status',0);
        else if($status == -1)//退款中
            return $model->where($alert.'paid',1)->where($alert.'refund_status',1);
        else if($status == -2)//已退款
            return $model->where($alert.'paid',1)->where($alert.'refund_status',2);
        else if($status == -3)//退款
            return $model->where($alert.'paid',1)->where($alert.'refund_status','in','1,2');
        else
            return $model;
    }

    public static function timeQuantumWhere($startTime = null,$endTime = null,$model = null)
    {
        if($model === null) $model = new self;
        if($startTime != null && $endTime != null)
            $model = $model->where('add_time','>',strtotime($startTime))->where('add_time','<',strtotime($endTime));
        return $model;
    }

    public static function changeOrderId($orderId)
    {
        $ymd = substr($orderId,2,8);
        $key = substr($orderId,16);
        return 'wx'.$ymd.date('His').$key;
    }

    /**
     * 线下付款
     * @param $id
     * @return $this
     */
    public static function updateOffline($id){
        $orderId = self::where('id',$id)->value('order_id');
        $res = self::where('order_id',$orderId)->update(['paid'=>1,'pay_time'=>time()]);
        return $res;
    }

    /**
     * 退款发送模板消息
     * @param $oid
     * $oid 订单id  key
     */
    public static function refundTemplate($data,$oid)
    {
        $order = self::where('id',$oid)->find();
        WechatTemplateService::sendTemplate(WechatUser::uidToOpenid($order['uid']),WechatTemplateService::ORDER_REFUND_STATUS, [
            'first'=>'亲，您购买的商品已退款,本次退款'.$data['refund_price'].'金额',
            'keyword1'=>$order['order_id'],
            'keyword2'=>$order['pay_price'],
            'keyword3'=>date('Y-m-d H:i:s',$order['add_time']),
            'remark'=>'点击查看订单详情'
        ],Url::build('wap/My/order',['uni'=>$order['order_id']],true,true));
    }

    /**
     * 处理where条件
     * @param $where
     * @param $model
     * @return mixed
     */
    public static function getOrderWhere($where,$model,$aler='',$join=''){
//        $model = $model->where('combination_id',0);
        if($where['status'] != '') $model =  self::statusByWhere($where['status'],$model,$aler);
        if($where['is_del'] != '' && $where['is_del'] != -1) $model = $model->where($aler.'is_del',$where['is_del']);
        if(isset($where['combination_id'])){
            if($where['combination_id'] =='普通订单'){
                $model = $model->where($aler.'combination_id',0)->where($aler.'seckill_id',0);
            }
            if($where['combination_id'] =='拼团订单'){
                $model = $model->where($aler.'combination_id',">",0)->where($aler.'pink_id',">",0);
            }
            if($where['combination_id'] =='秒杀订单'){
                $model = $model->where($aler.'seckill_id',">",0);
            }
        }
        if(isset($where['type'])){
            switch ($where['type']){
                case 1:
                    $model = $model->where($aler.'combination_id',0)->where($aler.'seckill_id',0);
                    break;
                case 2:
                    $model = $model->where($aler.'combination_id',">",0)->where($aler.'pink_id',">",0);
                    break;
                case 3:
                    $model = $model->where($aler.'seckill_id',">",0);
                    break;
            }
        }

        if($where['real_name'] != ''){
            $model = $model->where($aler.'order_id|'.$aler.'real_name|'.$aler.'user_phone'.($join ? '|'.$join.'.nickname|'.$join.'.uid':''),'LIKE',"%$where[real_name]%");
        }
        if($where['data'] !== ''){
            $model = self::getModelTime($where,$model,$aler.'add_time');
        }
        return $model;
    }
    public static function getBadge($where){
        $price=self::getOrderPrice($where);
        return [
            [
                'name'=>'订单数量',
                'field'=>'件',
                'count'=>$price['total_num'],
                'background_color'=>'layui-bg-blue',
                'col'=>2
            ],
            [
                'name'=>'售出商品',
                'field'=>'件',
                'count'=>$price['total_num'],
                'background_color'=>'layui-bg-blue',
                'col'=>2
            ],
            [
                'name'=>'订单金额',
                'field'=>'元',
                'count'=>$price['pay_price'],
                'background_color'=>'layui-bg-blue',
                'col'=>2
            ],
            [
                'name'=>'退款金额',
                'field'=>'元',
                'count'=>$price['refund_price'],
                'background_color'=>'layui-bg-blue',
                'col'=>2
            ],
            [
                'name'=>'微信支付金额',
                'field'=>'元',
                'count'=>$price['pay_price_wx'],
                'background_color'=>'layui-bg-blue',
                'col'=>2
            ],
            [
                'name'=>'余额支付金额',
                'field'=>'元',
                'count'=>$price['pay_price_yue'],
                'background_color'=>'layui-bg-blue',
                'col'=>2
            ],
            [
                'name'=>'线下支付金额',
                'field'=>'元',
                'count'=>$price['pay_price_offline'],
                'background_color'=>'layui-bg-blue',
                'col'=>2
            ],
            [
                'name'=>'积分抵扣',
                'field'=>'分',
                'count'=>$price['use_integral'].'(抵扣金额:￥'.$price['deduction_price'].')',
                'background_color'=>'layui-bg-blue',
                'col'=>2
            ],
            [
                'name'=>'退回积分',
                'field'=>'元',
                'count'=>$price['back_integral'],
                'background_color'=>'layui-bg-blue',
                'col'=>2
            ]
        ];
    }
    /**
     * 处理订单金额
     * @param $where
     * @return array
     */
    public static function getOrderPrice($where){
        $where['is_del'] = 0;//删除订单不统计
        $model = new self;
        $price = array();
        $price['pay_price'] = 0;//支付金额
        $price['refund_price'] = 0;//退款金额
        $price['pay_price_wx'] = 0;//微信支付金额
        $price['pay_price_yue'] = 0;//余额支付金额
        $price['pay_price_offline'] = 0;//线下支付金额
        $price['pay_price_other'] = 0;//其他支付金额
        $price['use_integral'] = 0;//用户使用积分
        $price['back_integral'] = 0;//退积分总数
        $price['deduction_price'] = 0;//抵扣金额
        $price['total_num'] = 0; //商品总数
        $model = self::getOrderWhere($where,$model);
        $list = $model->where('is_del',0)->select()->toArray();
        foreach ($list as $v){
            $price['total_num'] = bcadd($price['total_num'],$v['total_num'],0);
            $price['pay_price'] = bcadd($price['pay_price'],$v['pay_price'],2);
            $price['refund_price'] = bcadd($price['refund_price'],$v['refund_price'],2);
            $price['use_integral'] = bcadd($price['use_integral'],$v['use_integral'],2);
            $price['back_integral'] = bcadd($price['back_integral'],$v['back_integral'],2);
            $price['deduction_price'] = bcadd($price['deduction_price'],$v['deduction_price'],2);
            if ($v['pay_type'] == 'weixin'){
                $price['pay_price_wx'] = bcadd($price['pay_price_wx'],$v['pay_price'],2);
            }elseif($v['pay_type'] == 'yue'){
                $price['pay_price_yue'] = bcadd($price['pay_price_yue'],$v['pay_price'],2);
            }elseif($v['pay_type'] == 'offline'){
                $price['pay_price_offline'] = bcadd($price['pay_price_offline'],$v['pay_price'],2);
            }else{
                $price['pay_price_other'] = bcadd($price['pay_price_other'],$v['pay_price'],2);
            }
        }
        return $price;
    }

    public static function systemPagePink($where){
        $model = new self;
        $model = self::getOrderWherePink($where,$model);
        $model = $model->order('id desc');

        if($where['export'] == 1){
            $list = $model->select()->toArray();
            $export = [];
            foreach ($list as $index=>$item){

                if ($item['pay_type'] == 'weixin'){
                    $payType = '微信支付';
                }elseif($item['pay_type'] == 'yue'){
                    $payType = '余额支付';
                }elseif($item['pay_type'] == 'offline'){
                    $payType = '线下支付';
                }else{
                    $payType = '其他支付';
                }

                $_info =  Db::name('store_order_cart_info')->where('oid',$item['id'])->column('cart_info');
                $goodsName = [];
                foreach ($_info as $k=>$v){
                    $v = json_decode($v,true);
                    $goodsName[] = implode(
                        [$v['productInfo']['store_name'],
                            isset($v['productInfo']['attrInfo']) ? '('.$v['productInfo']['attrInfo']['suk'].')' : '',
                            "[{$v['cart_num']} * {$v['truePrice']}]"
                        ],' ');
                }
                $item['cartInfo'] = $_info;
                $export[] = [
                    $item['order_id'],$payType,
                    $item['total_num'],$item['total_price'],$item['total_postage'],$item['pay_price'],$item['refund_price'],
                    $item['mark'],$item['remark'],
                    [$item['real_name'],$item['user_phone'],$item['user_address']],
                    $goodsName,
                    [$item['paid'] == 1? '已支付':'未支付','支付时间: '.($item['pay_time'] > 0 ? date('Y/md H:i',$item['pay_time']) : '暂无')]

                ];
                $list[$index] = $item;
            }
            ExportService::exportCsv($export,'订单导出'.time(),['订单号','支付方式','商品总数','商品总价','邮费','支付金额','退款金额','用户备注','管理员备注','收货人信息','商品信息','支付状态']);
        }

        return self::page($model,function ($item){
            $item['nickname'] = WechatUser::where('uid',$item['uid'])->value('nickname');
            $_info =  Db::name('store_order_cart_info')->where('oid',$item['id'])->field('cart_info')->select();
            foreach ($_info as $k=>$v){
                $_info[$k]['cart_info'] = json_decode($v['cart_info'],true);
            }
            $item['_info'] = $_info;
        },$where);
    }

    /**
     * 处理where条件
     * @param $where
     * @param $model
     * @return mixed
     */
    public static function getOrderWherePink($where,$model){
        $model = $model->where('combination_id','GT',0);
        if($where['status'] != '') $model =  $model::statusByWhere($where['status']);
//        if($where['is_del'] != '' && $where['is_del'] != -1) $model = $model->where('is_del',$where['is_del']);
        if($where['real_name'] != ''){
            $model = $model->where('order_id|real_name|user_phone','LIKE',"%$where[real_name]%");
        }
        if($where['data'] !== ''){
            list($startTime,$endTime) = explode(' - ',$where['data']);
            $model = $model->where('add_time','>',strtotime($startTime));
            $model = $model->where('add_time','<',strtotime($endTime));
        }
        return $model;
    }

    /**
     * 处理订单金额
     * @param $where
     * @return array
     */
    public static function getOrderPricePink($where){
        $model = new self;
        $price = array();
        $price['pay_price'] = 0;//支付金额
        $price['refund_price'] = 0;//退款金额
        $price['pay_price_wx'] = 0;//微信支付金额
        $price['pay_price_yue'] = 0;//余额支付金额
        $price['pay_price_offline'] = 0;//线下支付金额
        $price['pay_price_other'] = 0;//其他支付金额
        $price['use_integral'] = 0;//用户使用积分
        $price['back_integral'] = 0;//退积分总数
        $price['deduction_price'] = 0;//抵扣金额
        $price['total_num'] = 0; //商品总数
        $model = self::getOrderWherePink($where,$model);
        $list = $model->select()->toArray();
        foreach ($list as $v){
            $price['total_num'] = bcadd($price['total_num'],$v['total_num'],0);
            $price['pay_price'] = bcadd($price['pay_price'],$v['pay_price'],2);
            $price['refund_price'] = bcadd($price['refund_price'],$v['refund_price'],2);
            $price['use_integral'] = bcadd($price['use_integral'],$v['use_integral'],2);
            $price['back_integral'] = bcadd($price['back_integral'],$v['back_integral'],2);
            $price['deduction_price'] = bcadd($price['deduction_price'],$v['deduction_price'],2);
            if ($v['pay_type'] == 'weixin'){
                $price['pay_price_wx'] = bcadd($price['pay_price_wx'],$v['pay_price'],2);
            }elseif($v['pay_type'] == 'yue'){
                $price['pay_price_yue'] = bcadd($price['pay_price_yue'],$v['pay_price'],2);
            }elseif($v['pay_type'] == 'offline'){
                $price['pay_price_offline'] = bcadd($price['pay_price_offline'],$v['pay_price'],2);
            }else{
                $price['pay_price_other'] = bcadd($price['pay_price_other'],$v['pay_price'],2);
            }
        }
        return $price;
    }

    /**
     * 获取昨天的订单   首页在使用
     * @param int $preDay
     * @param int $day
     * @return $this|StoreOrder
     */
    public static function isMainYesterdayCount($preDay = 0,$day = 0){
        $model = new self();
        $model = $model->where('add_time','gt',$preDay);
        $model = $model->where('add_time','lt',$day);
        return $model;
    }

    /**
     * 获取用户购买次数
     * @param int $uid
     * @return int|string
     */
    public static function getUserCountPay($uid = 0){
        if(!$uid) return 0;
        return self::where('uid',$uid)->where('paid',1)->count();
    }

    /**
     * 获取单个用户购买列表
     * @param array $where
     * @return array
     */
    public static function getOneorderList($where){
        return self::where(['uid'=>$where['uid']])
            ->order('add_time desc')
            ->page((int)$where['page'],(int)$where['limit'])
            ->field(['order_id','real_name','total_num','total_price','pay_price',
                'FROM_UNIXTIME(pay_time,"%Y-%m-%d") as pay_time','paid','pay_type',
                'pink_id','seckill_id','bargain_id'
            ])->select()
            ->toArray();
    }
    /*
     * 设置订单统计图搜索
     * $where array 条件
     * return object
     */
    public static function setEchatWhere($where,$status=null,$time=null){
        $model=self::statusByWhere($where['status']);
        if($status!==null) $where['type']=$status;
        if($time===true) $where['data']='';
        switch ($where['type']){
            case 1:
                //普通商品
                $model=$model->where('combination_id',0)->where('seckill_id',0);
                break;
            case 2:
                //拼团商品
                $model=$model->where('combination_id',">",0)->where('pink_id',">",0);
                break;
            case 3:
                //秒杀商品
                $model=$model->where('seckill_id',">",0);
                break;
            case 4:
                //砍价商品
                $model=$model->where('bargain_id','>',0);
                break;
        }
        return self::getModelTime($where,$model);
    }
    /*
     * 获取订单数据统计图
     * $where array
     * $limit int
     * return array
     */
    public static function getEchartsOrder($where,$limit=20){
        $orderlist=self::setEchatWhere($where)->field([
            'FROM_UNIXTIME(add_time,"%Y-%m-%d") as _add_time',
            'sum(total_num) total_num',
            'count(*) count',
            'sum(total_price) total_price',
            'sum(refund_price) refund_price',
            'group_concat(cart_id SEPARATOR "|") cart_ids'
        ])->group('_add_time')->order('_add_time asc')->select();
        count($orderlist) && $orderlist=$orderlist->toArray();
        $legend=['商品数量','订单数量','订单金额','退款金额'];
        $seriesdata=[
            [
                'name'=>$legend[0],
                'type'=>'line',
                'data'=>[],
            ],
            [
                'name'=>$legend[1],
                'type'=>'line',
                'data'=>[]
            ],
            [
                'name'=>$legend[2],
                'type'=>'line',
                'data'=>[]
            ],
            [
                'name'=>$legend[3],
                'type'=>'line',
                'data'=>[]
            ]
        ];
        $xdata=[];
        $zoom='';
        foreach ($orderlist as $item){
            $xdata[]=$item['_add_time'];
            $seriesdata[0]['data'][]=$item['total_num'];
            $seriesdata[1]['data'][]=$item['count'];
            $seriesdata[2]['data'][]=$item['total_price'];
            $seriesdata[3]['data'][]=$item['refund_price'];
        }
        count($xdata) > $limit && $zoom=$xdata[$limit-5];
        $badge=self::getOrderBadge($where);
        $bingpaytype=self::setEchatWhere($where)->group('pay_type')->field(['count(*) as count','pay_type'])->select();
        count($bingpaytype) && $bingpaytype=$bingpaytype->toArray();
        $bing_xdata=['微信支付','余额支付','其他支付'];
        $color=['#ffcccc','#99cc00','#fd99cc','#669966'];
        $bing_data=[];
        foreach ($bingpaytype as $key=>$item){
            if($item['pay_type']=='weixin'){
                $value['name']=$bing_xdata[0];
            }else if($item['pay_type']=='yue'){
                $value['name']=$bing_xdata[1];
            }else{
                $value['name']=$bing_xdata[2];
            }
            $value['value']=$item['count'];
            $value['itemStyle']['color']=isset($color[$key]) ? $color[$key]:$color[0];
            $bing_data[]=$value;
        }
        return compact('zoom','xdata','seriesdata','badge','legend','bing_data','bing_xdata');
    }

    public static function getOrderBadge($where){
        return [
            [
                'name'=>'拼团订单数量',
                'field'=>'个',
                'count'=>self::setEchatWhere($where,2)->count(),
                'content'=>'拼团总订单数量',
                'background_color'=>'layui-bg-cyan',
                'sum'=>self::setEchatWhere($where,2,true)->count(),
                'class'=>'fa fa-line-chart',
                'col'=>2
            ],
            [
                'name'=>'砍价订单数量',
                'field'=>'个',
                'count'=>self::setEchatWhere($where,4)->count(),
                'content'=>'砍价总订单数量',
                'background_color'=>'layui-bg-cyan',
                'sum'=>self::setEchatWhere($where,4,true)->count(),
                'class'=>'fa fa-line-chart',
                'col'=>2
            ],
            [
                'name'=>'秒杀订单数量',
                'field'=>'个',
                'count'=>self::setEchatWhere($where,3)->count(),
                'content'=>'秒杀总订单数量',
                'background_color'=>'layui-bg-cyan',
                'sum'=>self::setEchatWhere($where,3,true)->count(),
                'class'=>'fa fa-line-chart',
                'col'=>2
            ],
            [
                'name'=>'普通订单数量',
                'field'=>'个',
                'count'=>self::setEchatWhere($where,1)->count(),
                'content'=>'普通总订单数量',
                'background_color'=>'layui-bg-cyan',
                'sum'=>self::setEchatWhere($where,1,true)->count(),
                'class'=>'fa fa-line-chart',
                'col'=>2,
            ],
            [
                'name'=>'使用优惠卷金额',
                'field'=>'元',
                'count'=>self::setEchatWhere($where)->sum('coupon_price'),
                'content'=>'普通总订单数量',
                'background_color'=>'layui-bg-cyan',
                'sum'=>self::setEchatWhere($where,null,true)->sum('coupon_price'),
                'class'=>'fa fa-line-chart',
                'col'=>2
            ],
            [
                'name'=>'积分消耗数',
                'field'=>'个',
                'count'=>self::setEchatWhere($where)->sum('use_integral'),
                'content'=>'积分消耗总数',
                'background_color'=>'layui-bg-cyan',
                'sum'=>self::setEchatWhere($where,null,true)->sum('use_integral'),
                'class'=>'fa fa-line-chart',
                'col'=>2
            ],
            [
                'name'=>'积分抵扣金额',
                'field'=>'个',
                'count'=>self::setEchatWhere($where)->sum('deduction_price'),
                'content'=>'积分抵扣总金额',
                'background_color'=>'layui-bg-cyan',
                'sum'=>self::setEchatWhere($where,null,true)->sum('deduction_price'),
                'class'=>'fa fa-money',
                'col'=>2
            ],
            [
                'name'=>'在线支付金额',
                'field'=>'元',
                'count'=>self::setEchatWhere($where)->where('pay_type','weixin')->sum('pay_price'),
                'content'=>'在线支付总金额',
                'background_color'=>'layui-bg-cyan',
                'sum'=>self::setEchatWhere($where,null,true)->where('pay_type','weixin')->sum('pay_price'),
                'class'=>'fa fa-weixin',
                'col'=>2
            ],
            [
                'name'=>'余额支付金额',
                'field'=>'元',
                'count'=>self::setEchatWhere($where)->where('pay_type','yue')->sum('pay_price'),
                'content'=>'余额支付总金额',
                'background_color'=>'layui-bg-cyan',
                'sum'=>self::setEchatWhere($where,null,true)->where('pay_type','yue')->sum('pay_price'),
                'class'=>'fa  fa-balance-scale',
                'col'=>2
            ],
            [
                'name'=>'赚取积分',
                'field'=>'分',
                'count'=>self::setEchatWhere($where)->sum('gain_integral'),
                'content'=>'赚取总积分',
                'background_color'=>'layui-bg-cyan',
                'sum'=>self::setEchatWhere($where,null,true)->sum('gain_integral'),
                'class'=>'fa fa-gg-circle',
                'col'=>2
            ],
            [
                'name'=>'交易额',
                'field'=>'元',
                'count'=>self::setEchatWhere($where)->sum('pay_price'),
                'content'=>'总交易额',
                'background_color'=>'layui-bg-cyan',
                'sum'=>self::setEchatWhere($where,null,true)->sum('pay_price'),
                'class'=>'fa fa-jpy',
                'col'=>2
            ],
            [
                'name'=>'订单商品数量',
                'field'=>'元',
                'count'=>self::setEchatWhere($where)->sum('total_num'),
                'content'=>'订单商品总数量',
                'background_color'=>'layui-bg-cyan',
                'sum'=>self::setEchatWhere($where,null,true)->sum('total_num'),
                'class'=>'fa fa-cube',
                'col'=>2
            ]
        ];
    }

    /**微信 订单发货
     * @param $oid
     * @param array $postageData
     */
    public static function orderPostageAfter($oid,$postageData = [])
    {
        $order = self::where('id',$oid)->find();
        $openid = WechatUser::uidToOpenid($order['uid']);
        $url = Url::build('wap/My/order',['uni'=>$order['order_id']],true,true);
        $group = [
            'first'=>'亲,您的订单已发货,请注意查收',
            'remark'=>'点击查看订单详情'
        ];
        if($postageData['delivery_type'] == 'send'){//送货
            $goodsName = StoreOrderCartInfo::getProductNameList($order['id']);
            $group = array_merge($group,[
                'keyword1'=>$goodsName,
                'keyword2'=>$order['pay_type'] == 'offline' ? '线下支付' : date('Y/m/d H:i',$order['pay_time']),
                'keyword3'=>$order['user_address'],
                'keyword4'=>$postageData['delivery_name'],
                'keyword5'=>$postageData['delivery_id']
            ]);
            WechatTemplateService::sendTemplate($openid,WechatTemplateService::ORDER_DELIVER_SUCCESS,$group,$url);

        }else if($postageData['delivery_type'] == 'express'){//发货
            $group = array_merge($group,[
                'keyword1'=>$order['order_id'],
                'keyword2'=>$postageData['delivery_name'],
                'keyword3'=>$postageData['delivery_id']
            ]);
            WechatTemplateService::sendTemplate($openid,WechatTemplateService::ORDER_POSTAGE_SUCCESS,$group,$url);
        }
    }
    /**
     * 小程序 订单发货提醒
     * @param int $oid
     * @param array $postageData
     * @return bool
     */
    public static function sendOrderGoods($oid = 0,$postageData=array()){
        if(!$oid || !$postageData) return true;
        $order = self::where('id',$oid)->find();
        $routine_openid = WechatUser::uidToRoutineOpenid($order['uid']);
        if(!$routine_openid) return true;
        if($postageData['delivery_type'] == 'send'){//送货
            $data['keyword1']['value'] =  $order['order_id'];
            $data['keyword2']['value'] =  $order['delivery_name'];
            $data['keyword3']['value'] =  $order['delivery_id'];
            $data['keyword4']['value'] =  date('Y-m-d H:i:s',time());
            $data['keyword5']['value'] =  '您的商品已经发货请注意查收';
            $formId = RoutineFormId::getFormIdOne($order['uid']);
            if($formId){
                RoutineFormId::delFormIdOne($formId);
                RoutineTemplateService::sendTemplate($routine_openid,
                    RoutineTemplateService::setTemplateId(RoutineTemplateService::ORDER_DELIVER_SUCCESS),
                    '',
                    $data,
                    $formId);
            }
        }else if($postageData['delivery_type'] == 'express'){//发货
            $data['keyword1']['value'] =  $order['order_id'];
            $data['keyword2']['value'] =  $order['delivery_name'];
            $data['keyword3']['value'] =  $order['delivery_id'];
            $data['keyword4']['value'] =  date('Y-m-d H:i:s',time());
            $data['keyword5']['value'] =  '您的商品已经发货请注意查收';
            $formId = RoutineFormId::getFormIdOne($order['uid']);
            if($formId){
                RoutineFormId::delFormIdOne($formId);
                RoutineTemplateService::sendTemplate($routine_openid,
                    RoutineTemplateService::setTemplateId(RoutineTemplateService::ORDER_POSTAGE_SUCCESS),
                    '',
                    $data,
                    $formId);
            }
        }
    }


    /**
     * 获取订单总数
     * @param int $uid
     * @return int|string
     */
      public static function getOrderCount($uid = 0){
          if(!$uid) return 0;
          return self::where('uid',$uid)->where('paid',1)->where('refund_status',0)->where('status',2)->count();
      }
    /**
     * 获取已支付的订单
     * @param int $is_promoter
     * @return int|string
     */
    public static function getOrderPayCount($is_promoter = 0){
        return self::where('o.paid',1)->alias('o')->join('User u','u.uid=o.uid')->where('u.is_promoter',$is_promoter)->count();
    }

    /**
     * 获取最后一个月已支付的订单
     * @param int $is_promoter
     * @return int|string
     */
    public static function getOrderPayMonthCount($is_promoter = 0){
        return self::where('o.paid',1)->alias('o')->whereTime('o.pay_time','last month')->join('User u','u.uid=o.uid')->where('u.is_promoter',$is_promoter)->count();
    }
}