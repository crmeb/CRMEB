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

/**
 * 格式化月份
 * @param string $time
 * @param int $ceil
 * @return array
 */
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
/**删除目录下所有文件
 * @param $path 目录或者文件路径
 * @param string $ext
 * @return bool
 */
function clearfile($path,$ext = '*.log')
{
    $files = (array) glob($path.DS.'*');
    foreach ($files as $path) {
        if (is_dir($path)) {
            $matches = glob($path . '/'.$ext);
            if (is_array($matches)) {
                array_map('unlink', $matches);
            }
            rmdir($path);
        } else {
            unlink($path);
        }
    }
    return true;
}
