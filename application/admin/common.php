<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 获取用户名称
 * @param $uid
 * @return mixed
 */
function getUserNickname($uid){
    return \app\admin\model\user\User::where('uid',$uid)->value('nickname');
}

/**
 * 获取产品名称
 * @param $id
 * @return mixed
 */
function getProductName($id){
    return \app\admin\model\store\StoreProduct::where('id',$id)->value('store_name');
}

/**
 * 获取拼团名称
 * @param $id
 * @return mixed
 */
function getCombinationTitle($id){
    return \app\admin\model\store\StoreCombination::where('id',$id)->value('title');
}

/**
 * 获取订单编号
 * @param $id
 */
function getOrderId($id){
    return \app\admin\model\order\StoreOrder::where('id',$id)->value('order_id');
}


/**
 * 根据用户uid获取订单数
 * @param $uid
 * @return int|string
 */
function getOrderCount($uid){
    return \app\admin\model\order\StoreOrder::where('uid',$uid)->where('paid',1)->where('refund_status',0)->where('status',2)->count();
}
/**
 * 格式化属性
 * @param $arr
 * @return array
 */
function attrFormat($arr){
    $data = [];
    $res = [];
    if(count($arr) > 1){
        for ($i=0; $i < count($arr)-1; $i++) {
            if($i == 0) $data = $arr[$i]['detail'];
            //替代变量1
            $rep1 = [];
            foreach ($data as $v) {
                foreach ($arr[$i+1]['detail'] as $g) {
                    //替代变量2
                    $rep2 = ($i!=0?'':$arr[$i]['value']."_").$v."-".$arr[$i+1]['value']."_".$g;
                    $tmp[] = $rep2;
                    if($i==count($arr)-2){
                        foreach (explode('-', $rep2) as $k => $h) {
                            //替代变量3
                            $rep3 = explode('_', $h);
                            //替代变量4
                            $rep4['detail'][$rep3[0]] = $rep3[1];
                        }
                        $res[] = $rep4;
                    }
                }
            }
            $data = $tmp;
        }
    }else{
        $dataArr = [];
        foreach ($arr as $k=>$v){
            foreach ($v['detail'] as $kk=>$vv){
                $dataArr[$kk] = $v['value'].'_'.$vv;
                $res[$kk]['detail'][$v['value']] = $vv;
            }
        }
        $data[] = implode('-',$dataArr);
    }
    return [$data,$res];
}

function getMonth($time='',$ceil=0){
    if(empty($time)){
        $firstday = date("Y-m-01",time());
        $lastday = date("Y-m-d",strtotime("$firstday +1 month -1 day"));
    }else if($time=='n'){
        if($ceil!=0)
            $season = ceil(date('n') /3)-$ceil;
        else
            $season = ceil(date('n') /3);
        $firstday=date('Y-m-01',mktime(0,0,0,($season - 1) *3 +1,1,date('Y')));
        $lastday=date('Y-m-t',mktime(0,0,0,$season * 3,1,date('Y')));
    }else if($time=='y'){
        $firstday=date('Y-01-01');
        $lastday=date('Y-12-31');
    }else if($time=='h'){
        $firstday = date('Y-m-d', strtotime('this week +'.$ceil.' day')) . ' 00:00:00';
        $lastday = date('Y-m-d', strtotime('this week +'.($ceil+1).' day')) . ' 23:59:59';
    }
    return array($firstday,$lastday);
}
