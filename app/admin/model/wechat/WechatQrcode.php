<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/22
 */

namespace app\admin\model\wechat;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use crmeb\services\WechatService;

/**
 * 获取二维码
 * Class WechatQrcode
 * @package app\admin\model\wechat
 */
class WechatQrcode extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'wechat_qrcode';

    use ModelTrait;

    /**
     * 创建临时二维码    有效期 30天
     *
     * 修改时    要使用的主键id $qtcode_id
     * @param $id
     * @param $type
     * @param string $qtcode_id
     */
    public static function createTemporaryQrcode($id,$type,$qtcode_id=''){
        $qrcode = WechatService::qrcodeService();
        $data  = $qrcode->temporary($id,30*24*3600)->toArray();
        $data['qrcode_url'] = $data['url'];
        $data['expire_seconds'] = $data['expire_seconds']+time();
        $data['url'] = $qrcode->url($data['ticket']);
        $data['status'] = 1;
        $data['third_id'] = $id;
        $data['third_type'] = $type;
        if($qtcode_id){
            self::edit($data,$qtcode_id);
        }else{
            $data['add_time'] = time();
            self::create($data);
        }
    }

    /**
     * 创建永久二维码
     * @param $id
     * @param $type
     */
    public static function createForeverQrcode($id,$type){
        $qrcode = WechatService::qrcodeService();
        $data  = $qrcode->forever($id)->toArray();
        $data['qrcode_url'] = $data['url'];
        $data['url'] = $qrcode->url($data['ticket']);
        $data['expire_seconds'] = 0;
        $data['status'] = 1;
        $data['third_id'] = $id;
        $data['third_type'] = $type;
        $data['add_time'] = time();
        self::create($data);
    }

    /**
     * 获取临时二维码
     * @param $type
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public static function getTemporaryQrcode($type,$id){
          $res = self::where('third_id',$id)->where('third_type',$type)->find();
          if(empty($res)){
              self::createTemporaryQrcode($id,$type);
              $res = self::getTemporaryQrcode($type,$id);
          }else if(empty($res['expire_seconds']) || $res['expire_seconds'] < time()){
              self::createTemporaryQrcode($id,$type,$res['id']);
              $res = self::getTemporaryQrcode($type,$id);
          }
          if(!$res['ticket']) exception('临时二维码获取错误');
          return $res;
    }

    /**
     * 获取永久二维码
     * @param $type
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public static function getForeverQrcode($type,$id){
        $res = self::where('third_id',$id)->where('third_type',$type)->find();
        if(empty($res)) {
            self::createForeverQrcode($id, $type);
            $res = self::getForeverQrcode($type, $id);
        }
        if(!$res['ticket']) exception('临时二维码获取错误');
        return $res;
    }

    public static function getQrcode($id,$type = 'id')
    {
        return self::where($type,$id)->find();
    }

    public static function scanQrcode($id,$type = 'id')
    {
        return self::where($type,$id)->inc('scan')->update();
    }

}