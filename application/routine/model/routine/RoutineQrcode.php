<?php
namespace  app\routine\model\routine;


use basic\ModelBasic;
use traits\ModelTrait;

/**
 * 小程序二维码Model
 * Class RoutineQrcode
 * @package app\routine\model\routine
 */
class RoutineQrcode extends ModelBasic {

    use ModelTrait;

    /**
     * 添加二维码记录
     * @param string $thirdType
     * @param int $thirdId
     * @return object
     */
    public static function setRoutineQrcodeForever($thirdId = 0,$thirdType = 'spread',$page = '',$imgUrl = ''){
       $data['third_type'] = $thirdType;
       $data['third_id'] = $thirdId;
       $data['status'] = 0;
       $data['add_time'] = time();
       $data['page'] = $page;
       $data['url_time'] = '';
       $data['qrcode_url'] = $imgUrl;
       return self::set($data);
    }

    /**
     * 修改二维码地址
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function setRoutineQrcodeFind($id = 0,$data = array()){
        if(!$id) return false;
        $count = self::getRoutineQrcodeFind($id);
        if(!$count) return false;
        return self::edit($data,$id,'id');
    }

    /**
     * 获取二维码是否存在
     * @param int $id
     * @return int|string
     */
    public static function getRoutineQrcodeFind($id = 0){
        if(!$id) return 0;
        return self::where('id',$id)->count();
    }

    /**
     * 获取小程序二维码信息
     * @param int $id
     * @param string $field
     * @return array|bool|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getRoutineQrcodeFindType($id = 0,$field = 'third_type,third_id,page'){
        if(!$id) return false;
        $count = self::getRoutineQrcodeFind($id);
        if(!$count) return false;
        return self::where('id',$id)->where('status',1)->field($field)->find();
    }


}