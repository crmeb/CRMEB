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
if(!function_exists('unThumb')){
    function unThumb($src){
        return str_replace('/s_','/',$src);
    }
}
/**
*判断拼团是否结束*/
function isPinkStatus($pink){
    if(!$pink) return false;
    return \app\wap\model\store\StorePink::isSetPinkOver($pink);
}

/**
 *  设置浏览信息
 * @param $uid
 * @param int $product_id
 * @param int $cate
 * @param string $type
 * @param string $content
 * @param int $min
 */
function setView($uid,$product_id=0,$cate=0,$type='',$content='',$min=20){
    $Db=think\Db::name('store_visit');
    $view=$Db->where(['uid'=>$uid,'product_id'=>$product_id])->field('count,add_time,id')->find();
    if($view && $type!='search'){
        $time=time();
        if(($view['add_time']+$min)<$time){
            $Db->where(['id'=>$view['id']])->update(['count'=>$view['count']+1,'add_time'=>time()]);
        }
    }else{
        $cate = explode(',',$cate)[0];
        $Db->insert([
            'add_time'=>time(),
            'count'=>1,
            'product_id'=>$product_id,
            'cate_id'=>$cate,
            'type'=>$type,
            'uid'=>$uid,
            'content'=>$content
        ]);
    }
}

/**
 * 创建海报图片
 * @param array $product
 * @return bool|string
 */
function createPoster($product = array()){
    header("Content-type: image/jpg");
    $filePath = 'public/uploads/poster/'.time().'.jpg';
    $dir = iconv("UTF-8", "GBK", "public/uploads/poster");
    if(!file_exists($dir)) mkdir($dir,0775,true);
    $config = array(
        'text'=>array(
            array(
                'text'=>substr(iconv_substr($product['store_name'],0,mb_strlen($product['store_name']),'utf-8'),0,24).'...',
                'left'=>50,
                'top'=>500,
                'fontPath'=>ROOT_PATH.'public/static/font/simsunb.ttf',     //字体文件
                'fontSize'=>16,             //字号
                'fontColor'=>'40,40,40',       //字体颜色
                'angle'=>0,
            ),
            array(
                'text'=>'￥'.$product['price'],
                'left'=>50,
                'top'=>580,
                'fontPath'=>ROOT_PATH.'public/static/font/simsunb.ttf',     //字体文件
                'fontSize'=>16,             //字号
                'fontColor'=>'40,40,40',       //字体颜色
                'angle'=>0,
            )
        ),
        'image'=>array(
            array(
                'url'=>$product['image'],
                'left'=>50,
                'top'=>70,
                'right'=>0,
                'stream'=>0,
                'bottom'=>0,
                'width'=>350,
                'height'=>350,
                'opacity'=>100,
            ),
            array(
                'url'=>$product['code_path'],
                'left'=>250,
                'top'=>480,
                'right'=>0,
                'stream'=>0,
                'bottom'=>0,
                'width'=>160,
                'height'=>180,
                'opacity'=>100,
            ),
        ),
        'background'=>ROOT_PATH.UPLOAD_PATH.'/poster/background.jpg'          //背景图
    );
    $imageDefault = array(
        'left'=>0,
        'top'=>0,
        'right'=>0,
        'bottom'=>0,
        'width'=>100,
        'height'=>100,
        'opacity'=>100
    );
    $textDefault = array(
        'text'=>'2222222222',
        'left'=>0,
        'top'=>0,
        'fontSize'=>32,       //字号
        'fontColor'=>'255,255,255', //字体颜色
        'angle'=>0,
    );
    $background = $config['background'];//海报最底层得背景
    //背景方法
    $backgroundInfo = getimagesize($background);
    $backgroundFun = 'imagecreatefrom'.image_type_to_extension($backgroundInfo[2], false);
    $background = $backgroundFun($background);
    // $backgroundWidth = imagesx($background);  //背景宽度
    // $backgroundHeight = imagesy($background);  //背景高度
    $backgroundWidth = 460;  //背景宽度
    $backgroundHeight = 700;  //背景高度
    $imageRes = imageCreatetruecolor($backgroundWidth,$backgroundHeight);
    $color = imagecolorallocate($imageRes, 0, 0, 0);
    imagefill($imageRes, 0, 0, $color);
    // imageColorTransparent($imageRes, $color);  //颜色透明
    imagecopyresampled($imageRes,$background,0,0,0,0,imagesx($background),imagesy($background),imagesx($background),imagesy($background));   //处理了图片
    if(!empty($config['image'])){
        foreach ($config['image'] as $key => $val) {
            $val = array_merge($imageDefault,$val);
            $info = getimagesize($val['url']);
            $function = 'imagecreatefrom'.image_type_to_extension($info[2], false);
            if($val['stream']){   //如果传的是字符串图像流
                $info = getimagesizefromstring($val['url']);
                $function = 'imagecreatefromstring';
            }
            $res = $function($val['url']);
            $resWidth = $info[0];
            $resHeight = $info[1];
            //建立画板 ，缩放图片至指定尺寸
            $canvas=imagecreatetruecolor($val['width'], $val['height']);
            imagefill($canvas, 0, 0, $color);
            //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
            imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'],$resWidth,$resHeight);
            $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']) - $val['width']:$val['left'];
            $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']) - $val['height']:$val['top'];
            //放置图像
            imagecopymerge($imageRes,$canvas, $val['left'],$val['top'],$val['right'],$val['bottom'],$val['width'],$val['height'],$val['opacity']);//左，上，右，下，宽度，高度，透明度
        }   }
    //处理文字
    if(!empty($config['text'])){
        foreach ($config['text'] as $key => $val) {
            $val = array_merge($textDefault,$val);
            list($R,$G,$B) = explode(',', $val['fontColor']);
            $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
            $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']):$val['left'];
            $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']):$val['top'];
            imagettftext($imageRes,$val['fontSize'],$val['angle'],$val['left'],$val['top'],$fontColor,$val['fontPath'],$val['text']);
        }
    }
    //生成图片
    $res = imagejpeg ($imageRes,$filePath,90); //保存到本地
    imagedestroy($imageRes);
    if(!$res) return false;
    return $filePath;
}