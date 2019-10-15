<?php
/**
 * Created by PhpStorm.
 * User: sugar1569
 * Date: 2018/5/24
 * Time: 10:58
 */

namespace app\admin\controller\system;


use app\admin\controller\AuthController;
use crmeb\services\JsonService as Json;
use think\facade\Db;

/**
 * 清除默认数据理控制器
 * Class SystemclearData
 * @package app\admin\controller\system
 */
class SystemclearData  extends AuthController
{

    public function index(){
      return $this->fetch();
    }
    //清除用户数据
    public function userRelevantData(){
        self::clearData('user_recharge',1);
        self::clearData('user_address',1);
        self::clearData('user_bill',1);
        self::clearData('user_enter',1);
        self::clearData('user_extract',1);
        self::clearData('user_notice',1);
        self::clearData('user_notice_see',1);
        self::clearData('wechat_qrcode',1);
        self::clearData('wechat_message',1);
        self::clearData('store_visit',1);
        self::clearData('store_coupon_user',1);
        self::clearData('store_coupon_issue_user',1);
        self::clearData('store_bargain_user',1);
        self::clearData('store_bargain_user_help',1);
        self::clearData('store_product_reply',1);
        self::clearData('routine_qrcode',1);
        self::clearData('routine_form_id',1);
        self::clearData('user_sign',1);
        self::clearData('user_task_finish',1);
        self::clearData('user_level',1);
        self::clearData('token',1);
        self::clearData('user_group',1);
        $this->delDirAndFile('./public/uploads/store/comment');
        self::clearData('store_product_relation',1);
        return Json::successful('清除数据成功!');
    }
    //清除商城数据
    public function storeData(){
        self::clearData('store_coupon',1);
        self::clearData('store_coupon_issue',1);
        self::clearData('store_bargain',1);
        self::clearData('store_combination',1);
        self::clearData('store_combination_attr',1);
        self::clearData('store_combination_attr_result',1);
        self::clearData('store_combination_attr_value',1);
        self::clearData('store_product_attr',1);
        self::clearData('store_product_attr_result',1);
        self::clearData('store_product_attr_value',1);
        self::clearData('store_seckill',1);
        self::clearData('store_seckill_attr',1);
        self::clearData('store_seckill_attr_result',1);
        self::clearData('store_seckill_attr_value',1);
        self::clearData('store_product',1);
        self::clearData('store_visit',1);
        return Json::successful('清除数据成功!');
    }
    //清除产品分类
    public function categoryData(){
        self::clearData('store_category',1);
        return Json::successful('清除数据成功!');
    }
    //清除订单数据
    public function orderData(){
        self::clearData('store_order',1);
        self::clearData('store_order_cart_info',1);
        self::clearData('store_order_status',1);
        self::clearData('store_pink',1);
        self::clearData('store_cart',1);
        self::clearData('store_order_status',1);
        return Json::successful('清除数据成功!');
    }
    //清除客服数据
    public function kefuData(){
        self::clearData('store_service',1);
        $this->delDirAndFile('./public/uploads/store/service');
        self::clearData('store_service_log',1);
        return Json::successful('清除数据成功!');
    }

    //清除微信管理数据
    public function wechatData(){
        self::clearData('wechat_media',1);
        self::clearData('wechat_reply',1);
        self::clearData('cache',1);
        $this->delDirAndFile('./public/uploads/wechat');
        return Json::successful('清除数据成功!');
    }
    //清除所有附件
    public function uploadData(){
        self::clearData('system_attachment',1);
        self::clearData('system_attachment_category',1);
        $this->delDirAndFile('./public/uploads/');
        return Json::successful('清除上传文件成功!');
    }
    //清除微信用户
    public function  wechatuserData(){
        self::clearData('wechat_user',1);
        self::clearData('user',1);
        return Json::successful('清除数据成功!');
    }
    //清除内容分类
    public function articledata(){
        self::clearData('article_category',1);
        self::clearData('article',1);
        self::clearData('article_content',1);
        return Json::successful('清除数据成功!');
    }
    //清除系统记录
    public function systemdata(){
        self::clearData('system_notice_admin',1);
        self::clearData('system_log',1);
        return Json::successful('清除数据成功!');
    }
    //清除制定表数据
    public  function  clearData($table_name,$status){
        $table_name = config('database.connections.' . config('database.default'))['prefix'].$table_name;
        if($status){
            @db::execute('TRUNCATE TABLE '.$table_name);
        }else{
            @db::execute('DELETE FROM'.$table_name);
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