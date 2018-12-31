<?php
/**
 * Created by PhpStorm.
 * User: liying
 * Date: 2018/5/24
 * Time: 10:58
 */

namespace app\admin\controller\system;


use app\admin\controller\AuthController;
use app\admin\model\user\User;
use app\admin\model\wechat\WechatUser;
use service\JsonService as Json;
use think\db;
use think\Config;
/**
 * 清除默认数据理控制器
 * Class SystemCleardata
 * @package app\admin\controller\system
 *
 */
class SystemCleardata  extends AuthController
{
    public function index(){

      return $this->fetch();
    }
    //清除用户数据
    public function UserRelevant(){
        SystemCleardata::ClearData('user_recharge',1);
        SystemCleardata::ClearData('user_address',1);
        SystemCleardata::ClearData('user_bill',1);
        SystemCleardata::ClearData('user_enter',1);
        SystemCleardata::ClearData('user_extract',1);
        SystemCleardata::ClearData('user_notice',1);
        SystemCleardata::ClearData('user_notice_see',1);
        SystemCleardata::ClearData('wechat_qrcode',1);
        SystemCleardata::ClearData('wechat_message',1);
        SystemCleardata::ClearData('store_coupon_user',1);
        SystemCleardata::ClearData('store_coupon_issue_user',1);
        SystemCleardata::ClearData('store_bargain_user',1);
        SystemCleardata::ClearData('store_bargain_user_help',1);
        SystemCleardata::ClearData('store_product_reply',1);
        $this->delDirAndFile('./public/uploads/store/comment');
        SystemCleardata::ClearData('store_product_relation',1);
        return Json::successful('清除数据成功!');
    }
    //清除商城数据
    public function  storedata(){
        SystemCleardata::ClearData('store_coupon',1);
        SystemCleardata::ClearData('store_coupon_issue',1);
        SystemCleardata::ClearData('store_bargain',1);
        SystemCleardata::ClearData('store_combination',1);
        SystemCleardata::ClearData('store_product_attr',1);
        SystemCleardata::ClearData('store_product_attr_result',1);
        SystemCleardata::ClearData('store_product_attr_value',1);
        SystemCleardata::ClearData('store_seckill',1);
        SystemCleardata::ClearData('store_product',1);
        $this->delDirAndFile('./public/uploads/store/product');

        return Json::successful('清除数据成功!');
    }
    //清除产品分类
    public function categorydata(){
        SystemCleardata::ClearData('store_category',1);
        $this->delDirAndFile('./public/uploads/store/product');
        return Json::successful('清除数据成功!');
    }
    //清除订单数据
    public function orderdata(){
        SystemCleardata::ClearData('store_order',1);
        SystemCleardata::ClearData('store_order_cart_info',1);
        SystemCleardata::ClearData('store_order_copy',1);
        SystemCleardata::ClearData('store_order_status',1);
        SystemCleardata::ClearData('store_pink',1);
        SystemCleardata::ClearData('store_cart',1);
        return Json::successful('清除数据成功!');
    }
    //清除客服数据
    public function kefudata(){
        SystemCleardata::ClearData('store_service',1);
        $this->delDirAndFile('./public/uploads/store/service');
        SystemCleardata::ClearData('store_service_log',1);
        return Json::successful('清除数据成功!');
    }

    //清除微信管理数据
    public function wechatdata(){
        SystemCleardata::ClearData('wechat_media',1);
        SystemCleardata::ClearData('wechat_reply',1);
       $this->delDirAndFile('./public/uploads/wechat');
        return Json::successful('清除数据成功!');
    }
    //清除所有附件
    public function uploaddata(){
        $this->delDirAndFile('./public/uploads');
        return Json::successful('清除上传文件成功!');
    }
    //清除微信用户
    public function  wechatuserdata(){
        SystemCleardata::ClearData('wechat_user',1);
        SystemCleardata::ClearData('user',1);
        return Json::successful('清除数据成功!');
    }
    //清除内容分类
    public function articledata(){
        SystemCleardata::ClearData('article_category',1);
        SystemCleardata::ClearData('article',1);
        SystemCleardata::ClearData('article_content',1);
        $this->delDirAndFile('./public/uploads/article/');
        return Json::successful('清除数据成功!');
    }
    //清除制定表数据
    public  function  ClearData($table_name,$status){
        $table_name = Config::get('database')['prefix'].$table_name;
        if($status){
            @db::query('TRUNCATE TABLE '.$table_name);
        }else{
            @db::query('DELETE FROM'.$table_name);
        }

    }
    //递归删除文件
    function delDirAndFile($dirName,$subdir=true){
        if ($handle = @opendir("$dirName")){
            while(false !== ($item = readdir($handle))){
                if($item != "." && $item != ".."){
                    if(is_dir("$dirName/$item"))
                        $this->delDirAndFile("$dirName/$item",false);
                    else
                        @unlink("$dirName/$item");
                }
            }
            closedir($handle);
            if(!$subdir) @rmdir($dirName);
        }
    }
}