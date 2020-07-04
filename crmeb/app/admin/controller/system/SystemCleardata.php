<?php
/**
 * Created by PhpStorm.
 * User: sugar1569
 * Date: 2018/5/24
 * Time: 10:58
 */

namespace app\admin\controller\system;


use app\admin\controller\AuthController;
use app\admin\model\store\StoreProduct;
use crmeb\services\JsonService as Json;
use crmeb\services\SystemConfigService;
use think\facade\Config;
use think\facade\Db;

/**
 * 清除默认数据理控制器
 * Class SystemclearData
 * @package app\admin\controller\system
 */
class SystemclearData extends AuthController
{

    public function index()
    {
        return $this->fetch();
    }

    //清除用户数据
    public function userRelevantData()
    {
        self::clearData('user_recharge', 1);
        self::clearData('user_address', 1);
        self::clearData('user_bill', 1);
        self::clearData('user_enter', 1);
        self::clearData('user_extract', 1);
        self::clearData('user_notice', 1);
        self::clearData('user_notice_see', 1);
        self::clearData('wechat_qrcode', 1);
        self::clearData('wechat_message', 1);
        self::clearData('store_visit', 1);
        self::clearData('store_coupon_user', 1);
        self::clearData('store_coupon_issue_user', 1);
        self::clearData('store_bargain_user', 1);
        self::clearData('store_bargain_user_help', 1);
        self::clearData('store_product_reply', 1);
        self::clearData('routine_qrcode', 1);
        self::clearData('routine_form_id', 1);
        self::clearData('user_sign', 1);
        self::clearData('user_task_finish', 1);
        self::clearData('user_level', 1);
        self::clearData('user_token', 1);
        self::clearData('user_group', 1);
        $this->delDirAndFile('./public/uploads/store/comment');
        self::clearData('store_product_relation', 1);
        return Json::successful('清除数据成功!');
    }

    //清除商城数据
    public function storeData()
    {
        self::clearData('store_coupon', 1);
        self::clearData('store_coupon_issue', 1);
        self::clearData('store_bargain', 1);
        self::clearData('store_combination', 1);
        self::clearData('store_product_attr', 1);
        self::clearData('store_product_attr_result', 1);
        self::clearData('store_product_attr_value', 1);
        self::clearData('store_product_description', 1);
        self::clearData('store_product_reply', 1);
        self::clearData('store_seckill', 1);
        self::clearData('store_product', 1);
        self::clearData('store_visit', 1);
        return Json::successful('清除数据成功!');
    }

    //清除产品分类
    public function categoryData()
    {
        self::clearData('store_category', 1);
        self::clearData('store_product_cate', 1);
        return Json::successful('清除数据成功!');
    }

    //清除订单数据
    public function orderData()
    {
        self::clearData('store_order', 1);
        self::clearData('store_order_cart_info', 1);
        self::clearData('store_order_status', 1);
        self::clearData('store_pink', 1);
        self::clearData('store_cart', 1);
        self::clearData('store_order_status', 1);
        return Json::successful('清除数据成功!');
    }

    //清除客服数据
    public function kefuData()
    {
        self::clearData('store_service', 1);
        $this->delDirAndFile('./public/uploads/store/service');
        self::clearData('store_service_log', 1);
        return Json::successful('清除数据成功!');
    }

    //清除微信管理数据
    public function wechatData()
    {
        self::clearData('wechat_media', 1);
        self::clearData('wechat_reply', 1);
        self::clearData('cache', 1);
        $this->delDirAndFile('./public/uploads/wechat');
        return Json::successful('清除数据成功!');
    }

    //清除所有附件
    public function uploadData()
    {
        self::clearData('system_attachment', 1);
        self::clearData('system_attachment_category', 1);
        $this->delDirAndFile('./public/uploads/');
        return Json::successful('清除上传文件成功!');
    }

    //清除微信用户
    public function wechatuserData()
    {
        self::clearData('wechat_user', 1);
        self::clearData('user', 1);
        return Json::successful('清除数据成功!');
    }

    //清除内容分类
    public function articledata()
    {
        self::clearData('article_category', 1);
        self::clearData('article', 1);
        self::clearData('article_content', 1);
        return Json::successful('清除数据成功!');
    }

    //清除系统记录
    public function systemdata()
    {
        self::clearData('system_notice_admin', 1);
        self::clearData('system_log', 1);
        return Json::successful('清除数据成功!');
    }

    /**
     * 清除数据
     * @param int $type
     * @throws \Exception
     */
    public function undata($type = 1)
    {
        switch ((int)$type) {
            case 1:
                $fileImage = \app\admin\model\system\SystemAttachment::where('module_type', 2)->field(['att_dir', 'satt_dir'])->select();
                foreach ($fileImage as $image) {
                    if ($image['att_dir'] && ($imagePath = strstr($image['att_dir'], 'uploads')) !== false) {
                        if (is_file($imagePath))
                            unlink($imagePath);
                        unset($imagePath);
                    }

                    if ($image['satt_dir'] && ($imagePath = strstr($image['satt_dir'], 'uploads')) !== false) {
                        if (is_file($imagePath))
                            unlink($imagePath);
                        unset($imagePath);
                    }
                }
                \app\admin\model\system\SystemAttachment::where('module_type', 2)->delete();
                @unlink('uploads/follow/follow.jpg');//删除海报二维码
                break;
            case 2:
                StoreProduct::where('is_del', 1)->delete();
                break;
            case 3:
                $value = $this->request->param('value');
                if (!$value)
                    return Json::fail('请输入需要更换的域名');
                if (!verify_domain($value))
                    return Json::fail('域名不合法');
                $siteUrl = SystemConfigService::get('site_url', true);
                $siteUrlJosn = str_replace('http://', 'http:\\/\\/', $siteUrl);
                $valueJosn = str_replace('http://', 'http:\\/\\/', $value);
                $prefix = Config::get('database.connections.' . Config::get('database.default') . '.prefix');
                $sql = [
                    "UPDATE `{$prefix}system_attachment` SET `att_dir` = replace(att_dir ,'{$siteUrl}','{$value}'),`satt_dir` = replace(satt_dir ,'{$siteUrl}','{$value}')",
                    "UPDATE `{$prefix}store_product` SET `image` = replace(image ,'{$siteUrl}','{$value}'),`slider_image` = replace(slider_image ,'{$siteUrl}','{$value}')",
                    "UPDATE `{$prefix}store_product_attr_value` SET `image` = replace(image ,'{$siteUrl}','{$value}')",
                    "UPDATE `{$prefix}store_seckill` SET `image` = replace(image ,'{$siteUrl}','{$value}'),`images` = replace(images,'{$siteUrl}','{$value}')",
                    "UPDATE `{$prefix}store_combination` SET `image` = replace(image ,'{$siteUrl}','{$value}'),`images` = replace(images,'{$siteUrl}','{$value}')",
                    "UPDATE `{$prefix}store_bargain` SET `image` = replace(image ,'{$siteUrl}','{$value}'),`images` = replace(images,'{$siteUrl}','{$value}')",
                    "UPDATE `{$prefix}system_config` SET `value` = replace(value ,'{$siteUrlJosn}','{$valueJosn}')",
                    "UPDATE `{$prefix}article_category` SET `image` = replace(`image` ,'{$siteUrl}','{$value}')",
                    "UPDATE `{$prefix}article` SET `image_input` = replace(`image_input` ,'{$siteUrl}','{$value}')",
                    "UPDATE `{$prefix}article_content` SET `content` = replace(`content` ,'{$siteUrl}','{$value}')",
                    "UPDATE `{$prefix}store_category` SET `pic` = replace(`pic` ,'{$siteUrl}','{$value}')",
                    "UPDATE `{$prefix}system_group_data` SET `value` = replace(value ,'{$siteUrlJosn}','{$valueJosn}')",
                    "UPDATE `{$prefix}store_product_description` SET `description`= replace(description,'{$siteUrl}','{$value}')"
                ];
                try {
                    foreach ($sql as $item) {
                        db::execute($item);
                    }
                } catch (\Throwable $e) {
                    return Json::fail('替换失败，失败原因：' . $e->getMessage());
                }
                return Json::success('替换成功！');
                break;
        }
        return Json::successful('清除数据成功!');
    }

    //清除制定表数据
    public function clearData($table_name, $status)
    {
        $table_name = config('database.connections.' . config('database.default'))['prefix'] . $table_name;
        if ($status) {
            @db::execute('TRUNCATE TABLE ' . $table_name);
        } else {
            @db::execute('DELETE FROM' . $table_name);
        }

    }

    //递归删除文件
    function delDirAndFile($dirName, $subdir = true)
    {
        if ($handle = @opendir("$dirName")) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$dirName/$item"))
                        $this->delDirAndFile("$dirName/$item", false);
                    else
                        @unlink("$dirName/$item");
                }
            }
            closedir($handle);
            if (!$subdir) @rmdir($dirName);
        }
    }
}