<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\controller\v1\system;

use think\facade\App;
use app\adminapi\controller\AuthController;
use app\services\system\SystemClearServices;
use app\services\product\product\StoreProductServices;
use app\services\system\attachment\SystemAttachmentServices;


/**
 * 清除默认数据理控制器
 * Class SystemClearData
 * @package app\admin\controller\system
 */
class SystemClearData extends AuthController
{
    /**
     * 构造方法
     * SystemClearData constructor.
     * @param App $app
     * @param SystemClearServices $services
     */
    public function __construct(App $app, SystemClearServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 统一方法
     * @param $type
     */
    public function index($type)
    {
        switch ($type) {
            case 'temp':
                return $this->userTemp();
                break;
            case 'recycle':
                return $this->recycleProduct();
                break;
            case 'store':
                return $this->storeData();
                break;
            case 'category':
                return $this->categoryData();
                break;
            case 'order':
                return $this->orderData();
                break;
            case 'kefu':
                return $this->kefuData();
                break;
            case 'wechat':
                return $this->wechatData();
                break;
            case 'attachment':
                return $this->attachmentData();
                break;
            case 'article':
                return $this->articledata();
                break;
            case 'system':
                return $this->systemdata();
                break;
            case 'user':
                return $this->userRelevantData();
                break;
            case 'wechatuser':
                return $this->wechatuserData();
                break;
            default:
                return app('json')->fail('参数有误');
        }
    }

    /**
     * 清除用户生成的临时附件
     * @param int $type
     * @throws \Exception
     */
    public function userTemp()
    {
        /** @var SystemAttachmentServices $services */
        $services = app()->make(SystemAttachmentServices::class);
        $services->delete(2, 'module_type');
        return app('json')->success('清除数据成功!');
    }

    //清除回收站商品
    public function recycleProduct()
    {
        /** @var StoreProductServices $services */
        $services = app()->make(StoreProductServices::class);
        $services->delete(1, 'is_del');
        return app('json')->success('清除数据成功!');
    }

    /**
     * 清除用户数据
     * @return mixed
     */
    public function userRelevantData()
    {
        $this->services->clearData([
            'user_address', 'user_bill', 'user_enter',
            'user_notice', 'user_notice_see', 'wechat_message', 'store_visit',
            'store_coupon_user', 'store_coupon_issue_user',
            'store_product_reply', 'store_product_cate',
            'user_group', 'user_visit', 'user_label', 'user_label_relation', 'user_label_relation',
            'store_product_relation', 'sms_record', 'system_file',
            'qrcode','user_invoice'

        ], true);
        $this->services->delDirAndFile('./public/uploads/store/comment');
        return app('json')->success('清除数据成功!');
    }

    /**
     * 清除商城数据
     * @return mixed
     */
    public function storeData()
    {
        $this->services->clearData([
            'store_coupon_issue', 'store_product_attr',
            'store_product_attr_result', 'store_product_cate', 'store_product_attr_value', 'store_product_description',
            'store_product_rule', 'store_product', 'store_visit', 'store_product_log', 'category', 'delivery_service',
            'store_product_coupon'
        ], true);
        return app('json')->success('清除数据成功!');
    }

    /**
     * 清除商品分类
     * @return mixed
     */
    public function categoryData()
    {
        $this->services->clearData(['store_category'], true);
        return app('json')->success('清除数据成功!');
    }

    /**
     * 清除订单数据
     * @return mixed
     */
    public function orderData()
    {
        $this->services->clearData(['store_order', 'store_order_cart_info', 'store_order_status',
            'store_cart', 'store_order_status'
        ], true);
        return app('json')->success('清除数据成功!');
    }

    /**
     * 清除客服数据
     * @return mixed
     */
    public function kefuData()
    {
        $this->services->clearData([
            'article','article_category','article_content','category','delivery_service','qrcode','routine_access_token','routine_form_id','routine_qrcode',
            'sms_record','store_cart','store_category','store_coupon','store_coupon_issue','store_coupon_issue_user','store_coupon_product','store_coupon_user',
            'store_order','store_order_cart_info','store_order_economize','store_order_invoice','store_order_status','store_product','store_product_attr',
            'store_product_attr_result','store_product_attr_value','store_product_cate','store_product_coupon','store_product_description','store_product_log',
            'store_product_relation','store_product_reply','store_product_rule','store_visit','system_attachment','system_attachment_category','system_file',
            'system_log','system_notice','system_notice_admin','system_role','user','user_address','user_bill','user_enter','user_friends','user_group',
            'user_invoice','user_label','user_label_relation','user_notice','user_notice_see','user_search','user_visit','wechat_key','wechat_media','wechat_message',
            'wechat_news_category','wechat_qrcode','wechat_reply','wechat_user'
        ], true);
//        $this->services->delDirAndFile('./public/uploads/store/service');
        return app('json')->success('清除数据成功!');
    }

    /**
     * 清除微信管理数据
     * @return mixed
     */
    public function wechatData()
    {
        $this->services->clearData([
            'wechat_media', 'wechat_reply', 'cache', 'wechat_key',
            'wechat_news_category'
        ], true);
        $this->services->delDirAndFile('./public/uploads/wechat');
        return app('json')->success('清除数据成功!');
    }

    /**
     * 清除所有附件
     * @return mixed
     */
    public function attachmentData()
    {
        $this->services->clearData([
            'system_attachment', 'system_attachment_category'
        ], true);
        $this->services->delDirAndFile('./public/uploads/');
        return app('json')->success('清除上传文件成功!');
    }

    /**
     * 清除微信用户
     * @return mixed
     */
    public function wechatuserData()
    {
        $this->services->clearData([
            'user', 'wechat_user'
        ], true);
        return app('json')->success('清除数据成功!');
    }

    //清除内容分类
    public function articledata()
    {
        $this->services->clearData([
            'article_category', 'article', 'article_content'
        ], true);
        return app('json')->success('清除数据成功!');
    }

    //清除系统记录
    public function systemdata()
    {
        $this->services->clearData([
            'system_notice_admin', 'system_log'
        ], true);
        return app('json')->success('清除数据成功!');
    }

    /**
     * 替换域名方法
     * @return mixed
     */
    public function replaceSiteUrl()
    {
        list($url) = $this->request->postMore([
            ['url', '']
        ], true);
        if (!$url)
            return app('json')->fail('请输入需要更换的域名');
        if (!verify_domain($url))
            return app('json')->fail('域名不合法');
        $this->services->replaceSiteUrl($url);
        return app('json')->success('替换成功！');
    }
}
