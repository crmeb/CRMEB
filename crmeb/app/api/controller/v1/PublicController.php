<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\api\controller\v1;


use app\services\activity\combination\StorePinkServices;
use app\services\diy\DiyServices;
use app\services\kefu\service\StoreServiceServices;
use app\services\order\DeliveryServiceServices;
use app\services\other\AgreementServices;
use app\services\other\CacheServices;
use app\services\product\product\StoreCategoryServices;
use app\services\product\product\StoreProductServices;
use app\services\shipping\ExpressServices;
use app\services\shipping\SystemCityServices;
use app\services\system\AppVersionServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\system\config\SystemConfigServices;
use app\services\system\lang\LangCodeServices;
use app\services\system\lang\LangCountryServices;
use app\services\system\lang\LangTypeServices;
use app\services\system\store\SystemStoreServices;
use app\services\system\store\SystemStoreStaffServices;
use app\services\user\UserBillServices;
use app\services\user\UserInvoiceServices;
use app\services\user\UserServices;
use app\services\wechat\WechatUserServices;
use app\Request;
use crmeb\services\CacheService;
use app\services\other\UploadService;
use crmeb\services\workerman\ChannelService;
use think\facade\Cache;

/**
 * 公共类
 * Class PublicController
 * @package app\api\controller
 */
class PublicController
{
    /**
     * 主页获取
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $banner = sys_data('routine_home_banner') ?: [];//TODO 首页banner图
        $menus = sys_data('routine_home_menus') ?: [];//TODO 首页按钮
        $roll = sys_data('routine_home_roll_news') ?: [];//TODO 首页滚动新闻
        $activity = sys_data('routine_home_activity', 3) ?: [];//TODO 首页活动区域图片
        $explosive_money = sys_data('index_categy_images') ?: [];//TODO 首页超值爆款
        $site_name = sys_config('site_name');
        $routine_index_page = sys_data('routine_index_page');
        $info['fastInfo'] = $routine_index_page[0]['fast_info'] ?? '';//TODO 快速选择简介
        $info['bastInfo'] = $routine_index_page[0]['bast_info'] ?? '';//TODO 精品推荐简介
        $info['firstInfo'] = $routine_index_page[0]['first_info'] ?? '';//TODO 首发新品简介
        $info['salesInfo'] = $routine_index_page[0]['sales_info'] ?? '';//TODO 促销单品简介
        $logoUrl = sys_config('routine_index_logo');//TODO 促销单品简介
        if (strstr($logoUrl, 'http') === false && $logoUrl) {
            $logoUrl = sys_config('site_url') . $logoUrl;
        }
        $logoUrl = str_replace('\\', '/', $logoUrl);
        $fastNumber = (int)sys_config('fast_number', 0);//TODO 快速选择分类个数

        /** @var StoreCategoryServices $categoryService */
        $categoryService = app()->make(StoreCategoryServices::class);
        $info['fastList'] = $fastNumber ? $categoryService->byIndexList($fastNumber, 'id,cate_name,pid,pic') : [];//TODO 快速选择分类个数
        /** @var StoreProductServices $storeProductServices */
        $storeProductServices = app()->make(StoreProductServices::class);
        //获取推荐商品
        [$baseList, $firstList, $benefit, $likeInfo, $vipList] = $storeProductServices->getRecommendProductArr((int)$request->uid(), ['is_best', 'is_new', 'is_benefit', 'is_hot']);
        $info['bastList'] = $baseList;//TODO 精品推荐个数
        $info['firstList'] = $firstList;//TODO 首发新品个数
        $info['bastBanner'] = sys_data('routine_home_bast_banner') ?? [];//TODO 首页精品推荐图片
        $lovely = sys_data('routine_home_new_banner') ?: [];//TODO 首发新品顶部图
        if ($request->uid()) {
            /** @var WechatUserServices $wechatUserService */
            $wechatUserService = app()->make(WechatUserServices::class);
            $subscribe = (bool)$wechatUserService->value(['uid' => $request->uid()], 'subscribe');
        } else {
            $subscribe = true;
        }
        $newGoodsBananr = sys_config('new_goods_bananr');
        $tengxun_map_key = sys_config('tengxun_map_key');
        return app('json')->success(compact('banner', 'menus', 'roll', 'info', 'activity', 'lovely', 'benefit', 'likeInfo', 'logoUrl', 'site_name', 'subscribe', 'newGoodsBananr', 'tengxun_map_key', 'explosive_money'));
    }

    /**
     * 获取分享配置
     * @return mixed
     */
    public function share()
    {
        $data['img'] = sys_config('wechat_share_img');
        if (strstr($data['img'], 'http') === false) {
            $data['img'] = sys_config('site_url') . $data['img'];
        }
        $data['img'] = str_replace('\\', '/', $data['img']);
        $data['title'] = sys_config('wechat_share_title');
        $data['synopsis'] = sys_config('wechat_share_synopsis');
        return app('json')->success($data);
    }

    /**
     * 获取网站配置
     * @return mixed
     */
    public function getSiteConfig()
    {
        $data['record_No'] = sys_config('record_No');
        return app('json')->success($data);
    }

    /**
     * 获取个人中心菜单
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function menu_user(Request $request)
    {
        $menusInfo = sys_data('routine_my_menus') ?? [];
        $uid = 0;
        $userInfo = [];
        if ($request->hasMacro('user')) $userInfo = $request->user();
        if ($request->hasMacro('uid')) $uid = $request->uid();

        $vipOpen = sys_config('member_func_status');
        $brokerageFuncStatus = sys_config('brokerage_func_status');
        $balanceFuncStatus = sys_config('balance_func_status');
        $vipCard = sys_config('member_card_status', 0);
        $svipOpen = (bool)sys_config('member_card_status');
        $userService = $invoiceStatus = $deliveryUser = $isUserPromoter = $userVerifyStatus = $userOrder = true;

        if ($uid && $userInfo) {
            /** @var StoreServiceServices $storeService */
            $storeService = app()->make(StoreServiceServices::class);
            $userService = $storeService->checkoutIsService(['uid' => $uid, 'status' => 1]);
            $userOrder = $storeService->checkoutIsService(['uid' => $uid, 'status' => 1, 'customer' => 1]);
            /** @var SystemStoreStaffServices $systemStoreStaff */
            $systemStoreStaff = app()->make(SystemStoreStaffServices::class);
            /** @var UserServices $user */
            $user = app()->make(UserServices::class);
            /** @var UserInvoiceServices $userInvoice */
            $userInvoice = app()->make(UserInvoiceServices::class);
            $invoiceStatus = $userInvoice->invoiceFuncStatus(false);
            /** @var DeliveryServiceServices $deliveryService */
            $deliveryService = app()->make(DeliveryServiceServices::class);
            $deliveryUser = $deliveryService->checkoutIsService($uid);
            $isUserPromoter = $user->checkUserPromoter($uid, $userInfo);
            $userVerifyStatus = $systemStoreStaff->verifyStatus($uid);
        }
        $auth = [];
        $auth['/pages/users/user_vip/index'] = !$vipOpen;
        $auth['/pages/users/user_spread_user/index'] = !$brokerageFuncStatus || !$isUserPromoter || $uid == 0;
        $auth['/pages/users/user_money/index'] = !$balanceFuncStatus;
        $auth['/pages/admin/order/index'] = !$userOrder || $uid == 0;
        $auth['/pages/admin/order_cancellation/index'] = (!$userVerifyStatus && !$deliveryUser) || $uid == 0;
        $auth['/pages/users/user_invoice_list/index'] = !$invoiceStatus;
        $auth['/pages/annex/vip_paid/index'] = !$vipCard || !$svipOpen;
        $auth['/kefu/mobile_list'] = !$userService || $uid == 0;
        foreach ($menusInfo as $key => &$value) {
            if (isset($auth[$value['url']]) && $auth[$value['url']]) {
                unset($menusInfo[$key]);
                continue;
            }
            if ($value['url'] == '/kefu/mobile_list') {
                $value['url'] = sys_config('site_url') . $value['url'];
                if ($request->isRoutine()) {
                    $value['url'] = str_replace('http://', 'https://', $value['url']);
                }
            }
        }
        /** @var SystemConfigServices $systemConfigServices */
        $systemConfigServices = app()->make(SystemConfigServices::class);
        $bannerInfo = $systemConfigServices->getSpreadBanner() ?? [];
        $my_banner = sys_data('routine_my_banner');
        $routine_contact_type = sys_config('routine_contact_type', 0);
        /** @var DiyServices $diyServices */
        $diyServices = app()->make(DiyServices::class);
        $diy_data = $diyServices->get(['template_name' => 'member', 'type' => 1], ['value', 'order_status', 'my_banner_status']);
        $diy_data = $diy_data ? $diy_data->toArray() : [];
        return app('json')->success(['routine_my_menus' => array_merge($menusInfo), 'routine_my_banner' => $my_banner, 'routine_spread_banner' => $bannerInfo, 'routine_contact_type' => $routine_contact_type, 'diy_data' => $diy_data]);
    }

    /**
     * 热门搜索关键字获取
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function search()
    {
        $routineHotSearch = sys_data('routine_hot_search') ?? [];
        $searchKeyword = [];
        if (count($routineHotSearch)) {
            foreach ($routineHotSearch as $key => &$item) {
                array_push($searchKeyword, $item['title']);
            }
        }
        return app('json')->success($searchKeyword);
    }


    /**
     * 图片上传
     * @param Request $request
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function upload_image(Request $request, SystemAttachmentServices $services)
    {
        $data = $request->postMore([
            ['filename', 'file'],
        ]);
        if (!$data['filename']) return app('json')->fail(100100);
        if (Cache::has('start_uploads_' . $request->uid()) && Cache::get('start_uploads_' . $request->uid()) >= 100) return app('json')->fail(100101);
        $upload = UploadService::init();
        $info = $upload->to('store/comment')->validate()->move($data['filename']);
        if ($info === false) {
            return app('json')->fail($upload->getError());
        }
        $res = $upload->getUploadInfo();
        $services->attachmentAdd($res['name'], $res['size'], $res['type'], $res['dir'], $res['thumb_path'], 1, (int)sys_config('upload_type', 1), $res['time'], 3);
        if (Cache::has('start_uploads_' . $request->uid()))
            $start_uploads = (int)Cache::get('start_uploads_' . $request->uid());
        else
            $start_uploads = 0;
        $start_uploads++;
        Cache::set('start_uploads_' . $request->uid(), $start_uploads, 86400);
        $res['dir'] = path_to_url($res['dir']);
        if (strpos($res['dir'], 'http') === false) $res['dir'] = $request->domain() . $res['dir'];
        return app('json')->success(100009, ['name' => $res['name'], 'url' => $res['dir']]);
    }

    /**
     * 物流公司
     * @return mixed
     */
    public function logistics(ExpressServices $services)
    {
        $expressList = $services->expressList();
        return app('json')->success($expressList ?? []);
    }

    /**
     * 短信购买异步通知
     *
     * @param Request $request
     * @return mixed
     */
    public function sms_pay_notify(Request $request)
    {
        [$order_id, $price, $status, $num, $pay_time, $attach] = $request->postMore([
            ['order_id', ''],
            ['price', 0.00],
            ['status', 400],
            ['num', 0],
            ['pay_time', time()],
            ['attach', 0],
        ], true);
        if ($status == 200) {
            try {
                ChannelService::instance()->send('PAY_SMS_SUCCESS', ['price' => $price, 'number' => $num], [$attach]);
            } catch (\Throwable $e) {
            }
            return app('json')->success(100010);
        }
        return app('json')->fail(100005);
    }

    /**
     * 记录用户分享
     * @param Request $request
     * @param UserBillServices $services
     * @return mixed
     */
    public function user_share(Request $request, UserBillServices $services)
    {
        $uid = (int)$request->uid();
        $services->setUserShare($uid);
        return app('json')->success(100012);
    }

    /**
     * 获取图片base64
     * @param Request $request
     * @return mixed
     */
    public function get_image_base64(Request $request)
    {
        [$imageUrl, $codeUrl] = $request->postMore([
            ['image', ''],
            ['code', ''],
        ], true);
        if ($imageUrl !== '' && !preg_match('/.*(\.png|\.jpg|\.jpeg|\.gif)$/', $imageUrl)) {
            return app('json')->success(['code' => false, 'image' => false]);
        }
        if ($codeUrl !== '' && !(preg_match('/.*(\.png|\.jpg|\.jpeg|\.gif)$/', $codeUrl) || strpos($codeUrl, 'https://mp.weixin.qq.com/cgi-bin/showqrcode') !== false)) {
            return app('json')->success(['code' => false, 'image' => false]);
        }
        try {
            $code = CacheService::remember($codeUrl, function () use ($codeUrl) {
                $codeTmp = $code = $codeUrl ? image_to_base64($codeUrl) : false;
                if (!$codeTmp) {
                    $putCodeUrl = put_image($codeUrl);
                    $code = $putCodeUrl ? image_to_base64(app()->request->domain(true) . '/' . $putCodeUrl) : false;
                    $code ?? unlink($_SERVER["DOCUMENT_ROOT"] . '/' . $putCodeUrl);
                }
                return $code;
            });
            $image = CacheService::remember($imageUrl, function () use ($imageUrl) {
                $imageTmp = $image = $imageUrl ? image_to_base64($imageUrl) : false;
                if (!$imageTmp) {
                    $putImageUrl = put_image($imageUrl);
                    $image = $putImageUrl ? image_to_base64(app()->request->domain(true) . '/' . $putImageUrl) : false;
                    $image ?? unlink($_SERVER["DOCUMENT_ROOT"] . '/' . $putImageUrl);
                }
                return $image;
            });
            return app('json')->success(compact('code', 'image'));
        } catch (\Exception $e) {
            return app('json')->fail(100005);
        }
    }

    /**
     * 门店列表
     * @return mixed
     */
    public function store_list(Request $request, SystemStoreServices $services)
    {
        list($latitude, $longitude) = $request->getMore([
            ['latitude', ''],
            ['longitude', ''],
        ], true);
        $data['list'] = $services->getStoreList(['type' => 0], ['id', 'name', 'phone', 'address', 'detailed_address', 'image', 'latitude', 'longitude'], $latitude, $longitude);
        $data['tengxun_map_key'] = sys_config('tengxun_map_key');
        return app('json')->success($data);
    }

    /**
     * 查找城市数据
     * @param Request $request
     * @return mixed
     */
    public function city_list(Request $request)
    {
        /** @var SystemCityServices $systemCity */
        $systemCity = app()->make(SystemCityServices::class);
        return app('json')->success($systemCity->cityList());
    }

    /**
     * 获取拼团数据
     * @return mixed
     */
    public function pink(StorePinkServices $pink, UserServices $user)
    {
        $data['pink_count'] = $pink->getCount(['is_refund' => 0]);
        $uids = array_flip($pink->getColumn(['is_refund' => 0], 'uid'));
        if (count($uids)) {
            $uids = array_rand($uids, count($uids) < 3 ? count($uids) : 3);
        }
        $data['avatars'] = $uids ? $user->getColumn(is_array($uids) ? [['uid', 'in', $uids]] : ['uid' => $uids], 'avatar') : [];
        return app('json')->success($data);
    }

    /**
     * 复制口令接口
     * @return mixed
     */
    public function copy_words()
    {
        $data['words'] = sys_config('copy_words');
        return app('json')->success($data);
    }

    /**生成口令关键字
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function copy_share_words(Request $request)
    {
        list($productId) = $request->getMore([
            ['product_id', ''],
        ], true);
        /** @var StoreProductServices $productService */
        $productService = app()->make(StoreProductServices::class);
        $keyWords['key_words'] = $productService->getProductWords($productId);
        return app('json')->success($keyWords);
    }

    /**
     * 获取页面数据
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDiy(DiyServices $services, $id = 0)
    {
        return app('json')->success($services->getDiyInfo((int)$id));
    }

    /**
     * 获取底部导航
     * @param DiyServices $services
     * @param string $template_name
     * @return mixed
     */
    public function getNavigation(DiyServices $services, string $template_name = '')
    {
        return app('json')->success($services->getNavigation($template_name));
    }

    /**
     * 首页商品数据
     * @param Request $request
     */
    public function home_products_list(Request $request, DiyServices $services)
    {
        $data = $request->getMore([
            ['priceOrder', ''],
            ['newsOrder', ''],
            ['salesOrder', ''],
            [['type', 'd'], 0],
            ['ids', ''],
            [['selectId', 'd'], ''],
            ['selectType', 0],
            ['isType', 0],
        ]);
        $where = [];
        $where['is_show'] = 1;
        $where['is_del'] = 0;
        $where['productId'] = '';
        if ($data['selectType'] == 1) {
            if (!$data['ids']) {
                return app('json')->success(100011);
            }
            $where['ids'] = $data['ids'] ? explode(',', $data['ids']) : [];
            if ($data['type'] != 2 && $data['type'] != 3 && $data['type'] != 8) {
                $where['type'] = 0;
            } else {
                $where['type'] = $data['type'];
            }
        } else {
            $where['priceOrder'] = $data['priceOrder'];
            $where['newsOrder'] = $data['newsOrder'];
            $where['salesOrder'] = $data['salesOrder'];
            $where['type'] = $data['type'];
            if ($data['selectId']) {
                /** @var StoreCategoryServices $storeCategoryServices */
                $storeCategoryServices = app()->make(StoreCategoryServices::class);
                if ($storeCategoryServices->value(['id' => $data['selectId']], 'pid')) {
                    $where['sid'] = $data['selectId'];
                } else {
                    $where['cid'] = $data['selectId'];
                }
            }
        }
        return app('json')->success($services->homeProductList($where, $request->uid()));
    }

    public function getNewAppVersion($platform)
    {
        /** @var AppVersionServices $appService */
        $appService = app()->make(AppVersionServices::class);
        return app('json')->success($appService->getNewInfo($platform));
    }

    public function getCustomerType()
    {
        $data = [];
        $data['customer_type'] = sys_config('customer_type', 0);
        $data['customer_phone'] = sys_config('customer_phone', 0);
        $data['customer_url'] = sys_config('customer_url', 0);
        $data['customer_corpId'] = sys_config('customer_corpId', 0);
        return app('json')->success($data);
    }


    /**
     * 统计代码
     * @return array|string
     */
    public function getScript()
    {
        return sys_config('statistic_script', '');
    }

    /**
     * 获取workerman请求域名
     * @return mixed
     */
    public function getWorkerManUrl()
    {
        return app('json')->success(getWorkerManUrl());
    }

    /**
     * 首页开屏广告
     * @return mixed
     */
    public function getOpenAdv()
    {
        /** @var CacheServices $cache */
        $cache = app()->make(CacheServices::class);
        $data = $cache->getDbCache('open_adv', '');
        return app('json')->success($data);
    }

    /**
     * 获取用户协议内容
     * @return mixed
     */
    public function getUserAgreement()
    {
        /** @var CacheServices $cache */
        $cache = app()->make(CacheServices::class);
        $content = $cache->getDbCache('user_agreement', '');
        return app('json')->success(compact('content'));
    }

    /**
     * 获取协议
     * @param AgreementServices $agreementServices
     * @param $type
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAgreement(AgreementServices $agreementServices, $type)
    {
        $data = $agreementServices->getAgreementBytype($type);
        return app('json')->success($data);
    }

    /**
     * 查询版权信息
     * @return mixed
     */
    public function copyright()
    {
        $copyrightContext = sys_config('nncnL_crmeb_copyright', '');
        $copyrightImage = sys_config('nncnL_crmeb_copyright_image', '');
        $siteName = sys_config('site_name', '');
        $siteLogo = sys_config('wap_login_logo', '');
        return app('json')->success(compact('copyrightContext', 'copyrightImage', 'siteName', 'siteLogo'));
    }

    /**
     * 获取多语言类型列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLangTypeList()
    {
        /** @var LangTypeServices $langTypeServices */
        $langTypeServices = app()->make(LangTypeServices::class);
        $list = $langTypeServices->langTypeList(['status' => 1, 'is_del' => 0])['list'];
        $data = [];
        foreach ($list as $item) {
            $data[] = ['name' => $item['language_name'], 'value' => $item['file_name']];
        }
        return app('json')->success($data);
    }

    /**
     * 获取当前语言json
     * @return mixed
     * @throws \Throwable
     */
    public function getLangJson()
    {
        /** @var LangTypeServices $langTypeServices */
        $langTypeServices = app()->make(LangTypeServices::class);
        /** @var LangCountryServices $langCountryServices */
        $langCountryServices = app()->make(LangCountryServices::class);

        $request = app()->request;
        //获取接口传入的语言类型
        if (!$range = $request->header('cb-lang')) {
            //没有传入则使用系统默认语言显示
            if (!$range = $langTypeServices->value(['is_default' => 1], 'file_name')) {
                //系统没有设置默认语言的话，根据浏览器语言显示，如果浏览器语言在库中找不到，则使用简体中文
                if ($request->header('accept-language') !== null) {
                    $range = explode(',', $request->header('accept-language'))[0];
                } else {
                    $range = 'zh-CN';
                }
            }
        }
        // 获取type_id
        $typeId = $langCountryServices->value(['code' => $range], 'type_id') ?: 1;

        // 获取缓存key
        $langData = $langTypeServices->getColumn(['status' => 1, 'is_del' => 0], 'file_name', 'id');
        $langStr = 'api_lang_' . str_replace('-', '_', $langData[$typeId]);

        //读取当前语言的语言包
        $lang = CacheService::remember($langStr, function () use ($typeId, $range) {
            /** @var LangCodeServices $langCodeServices */
            $langCodeServices = app()->make(LangCodeServices::class);
            return $langCodeServices->getColumn(['type_id' => $typeId, 'is_admin' => 0], 'lang_explain', 'code');
        }, 3600);
        return app('json')->success([$range => $lang]);
    }

    /**
     * 获取当前后台设置的默认语言类型
     * @return mixed
     */
    public function getDefaultLangType()
    {
        /** @var LangTypeServices $langTypeServices */
        $langTypeServices = app()->make(LangTypeServices::class);
        $lang_type = $langTypeServices->value(['is_default' => 1], 'file_name');
        return app('json')->success(compact('lang_type'));
    }

    /**
     * 获取版本号
     * @return mixed
     */
    public function getVersion()
    {
        return app('json')->success(['version' => get_crmeb_version()]);
    }
}
