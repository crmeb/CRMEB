<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\api\controller\v1;


use app\services\activity\coupon\StoreCouponIssueServices;
use app\services\article\ArticleServices;
use app\services\diy\DiyComponentServices;
use app\services\diy\DiyServices;
use app\services\diy\ThemeServices;

use app\services\personnel\ServicePersonnelServices;
use app\services\order\OrderCenterServices;
use app\services\order\StoreCartServices;
use app\dao\order\OrderCenterDao;
use app\services\other\AgreementServices;
use app\services\other\CacheServices;
use app\services\product\product\StoreCategoryServices;
use app\services\product\product\StoreProductRelationServices;
use app\services\product\CenterProductServices;
use app\services\shipping\ExpressServices;
use app\services\shipping\SystemCityServices;
use app\services\system\AppVersionServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\system\config\SystemConfigServices;
use app\services\driver\upload\UploadStorageServices;
use app\services\driver\pay\PayChannelServices;
use app\services\system\lang\LangCodeServices;
use app\services\system\lang\LangCountryServices;
use app\services\system\lang\LangTypeServices;
use app\services\system\store\SystemStoreServices;
use app\services\system\store\SystemStoreStaffServices;
use app\services\user\UserBillServices;
use app\services\user\UserExtractServices;
use app\services\user\UserInvoiceServices;
use app\services\user\UserServices;
use app\Request;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;
use app\services\driver\upload\UploadService;
use crmeb\services\pay\Pay;
use crmeb\services\pay\PayChannelConfig;
use crmeb\services\workerman\ChannelService;

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
        $banner = sys_data('routine_home_banner') ?: []; //TODO 首页banner图
        $menus = sys_data('routine_home_menus') ?: []; //TODO 首页按钮
        $roll = sys_data('routine_home_roll_news') ?: []; //TODO 首页滚动新闻
        $activity = sys_data('routine_home_activity', 3) ?: []; //TODO 首页活动区域图片
        $explosive_money = sys_data('index_categy_images') ?: []; //TODO 首页超值爆款
        $site_name = sys_config('site_name');
        $routine_index_page = sys_data('routine_index_page');
        $info['fastInfo'] = $routine_index_page[0]['fast_info'] ?? ''; //TODO 快速选择简介
        $logoUrl = sys_config('routine_index_logo'); //TODO 首页LOGO
        if (strstr($logoUrl, 'http') === false && $logoUrl) {
            $logoUrl = sys_config('site_url') . $logoUrl;
        }
        $logoUrl = str_replace('\\', '/', $logoUrl);
        $fastNumber = (int)sys_config('fast_number', 0);//TODO 快速选择分类个数

        /** @var StoreCategoryServices $categoryService */
        $categoryService = app()->make(StoreCategoryServices::class);
        $info['fastList'] = $fastNumber ? $categoryService->byIndexList($fastNumber, 'id,cate_name,pid,pic') : [];//TODO 快速选择分类个数
        /** @var CenterProductServices $storeProductServices */
        $storeProductServices = app()->make(CenterProductServices::class);
        // 获取猜你喜欢推荐商品
        [, , , $likeInfo] = $storeProductServices->getRecommendProductArr((int)$request->uid(), []);
        if ($request->uid()) {
            //通过事件代理查询用户关注状态，避免 app 层直接引用 modules
            $results = event('ChannelServiceProxy', ['endpoint' => 'wechat_mp', 'service' => 'WechatUserServices', 'action' => 'value', 'args' => [['uid' => $request->uid()], 'subscribe']]);
            $subscribe = (bool)proxy_event_result($results, false);
        } else {
            $subscribe = true;
        }
        $tengxun_map_key = sys_config('tengxun_map_key');
        return app('json')->success(compact('banner', 'menus', 'roll', 'info', 'activity', 'likeInfo', 'logoUrl', 'site_name', 'subscribe', 'tengxun_map_key', 'explosive_money'));
    }

    /**
     * 获取分享配置
     * @return mixed
     */
    public function share()
    {
        $data['img'] = sys_config('wechat_share_img');
        if (strstr($data['img'], 'http') === false && $data['img'] !== '') {
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
        $data['icp_url'] = sys_config('icp_url');
        $data['network_security'] = sys_config('network_security');
        $data['network_security_url'] = sys_config('network_security_url');
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
    public function menuUser(Request $request)
    {
        $menusInfo = sys_data('routine_my_menus') ?? [];
        $uid = 0;
        $userInfo = [];
        if ($request->hasMacro('user')) $userInfo = $request->user();
        if ($request->hasMacro('uid')) $uid = $request->uid();

        //用户等级开关
        $vipOpen = sys_config('member_func_status');
        //分销功能开关
        $brokerageFuncStatus = sys_config('brokerage_func_status');
        //余额功能开关
        $balanceFuncStatus = sys_config('balance_func_status');
        //付费会员开关
        $svipOpen = sys_config('member_card_status');
        $userService = $userOrder = $userVerifyStatus = $deliveryUser = $invoiceStatus = $isUserPromoter = false;
        if ($uid && $userInfo) {
            //是否客服
            $userService = (bool)(event('KefuConfigQuery', ['uid' => $uid, 'customer' => 0])[0] ?? false);
            //是否订单管理
            $userOrder = (bool)(event('KefuConfigQuery', ['uid' => $uid, 'customer' => 1])[0] ?? false);
            //是否核销员
            $userVerifyStatus = app()->make(SystemStoreStaffServices::class)->verifyStatus($uid);
            //是否配送员
            $deliveryUser = app()->make(ServicePersonnelServices::class)->checkoutIsService($uid);
            //发票功能开关
            $invoiceStatus = app()->make(UserInvoiceServices::class)->invoiceFuncStatus(false);
            //是否分销员
            $isUserPromoter = app()->make(UserServices::class)->checkUserPromoter($uid, $userInfo);
        }
        $auth = [];
        $auth['/pages/users/user_vip/index'] = $vipOpen;
        $auth['/pages/users/user_spread_user/index'] = $brokerageFuncStatus && $isUserPromoter;
        $auth['/pages/annex/settled/index'] = $brokerageFuncStatus && sys_config('store_brokerage_statu') == 1 && !$isUserPromoter;
        $auth['/pages/users/user_money/index'] = $balanceFuncStatus;
        $auth['/pages/users/user_invoice_list/index'] = $invoiceStatus;
        $auth['/pages/annex/vip_paid/index'] = $svipOpen;
        $auth['/kefu/mobile_list'] = $userService;
        foreach ($menusInfo as $key => &$value) {
            if (isset($value['is_show']) && $value['is_show'] == 0) {
                unset($menusInfo[$key]);
                continue;
            }
            if ($value['url'] == '/pages/users/user_spread_user/index' && $auth['/pages/annex/settled/index']) {
                $value['name'] = '分销申请';
                $value['url'] = '/pages/annex/settled/index';
            }
            if (isset($auth[$value['url']]) && !$auth[$value['url']]) {
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
        $diy_data = $diyServices->get(['template_name' => 'member', 'type' => 1], ['value', 'order_status', 'my_banner_status', 'my_menus_status', 'business_status']);
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
     * @param SystemAttachmentServices $services
     * @return mixed
     */
    public function uploadImage(Request $request, SystemAttachmentServices $services)
    {
        $data = $request->postMore([
            ['filename', 'file'],
        ]);
        if (!$data['filename']) return app('json')->fail('参数错误');
        if (CacheService::has('start_uploads_' . $request->uid()) && CacheService::get('start_uploads_' . $request->uid()) >= 100) return app('json')->fail('非法操作');
        $upload = UploadService::init();
        $info = $upload->to('store/comment')->validate()->move($data['filename']);
        if ($info === false) {
            return app('json')->fail($upload->getError());
        }
        $res = $upload->getUploadInfo();
        $services->attachmentAdd($res['name'], $res['size'], $res['type'], $res['dir'], $res['thumb_path'], 1, upload_type(), $res['time'], 3);
        if (CacheService::has('start_uploads_' . $request->uid()))
            $start_uploads = (int)CacheService::get('start_uploads_' . $request->uid());
        else
            $start_uploads = 0;
        $start_uploads++;
        CacheService::set('start_uploads_' . $request->uid(), $start_uploads, 86400);
        $res['dir'] = path_to_url($res['dir']);
        if (strpos($res['dir'], 'http') === false) $res['dir'] = $request->domain() . $res['dir'];
        return app('json')->success('图片上传成功', ['name' => $res['name'], 'url' => $res['dir']]);
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
    public function smsPayNotify(Request $request)
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
            return app('json')->success('操作成功');
        }
        return app('json')->fail('操作失败');
    }

    /**
     * 记录用户分享
     * @param Request $request
     * @param UserBillServices $services
     * @return mixed
     */
    public function userShare(Request $request, UserBillServices $services)
    {
        $uid = (int)$request->uid();
        $services->setUserShare($uid);
        return app('json')->success('更新成功');
    }

    /**
     * 获取图片base64
     * @param Request $request
     * @return mixed
     */
    public function getImageBase64(Request $request)
    {
        [$imageUrl, $codeUrl] = $request->postMore([
            ['image', ''],
            ['code', ''],
        ], true);
        /** @var UploadStorageServices $systemStorageServices */
        $systemStorageServices = app()->make(UploadStorageServices::class);
        $domainArr = $systemStorageServices->getColumn([], 'domain');
        $siteHost = parse_url((string) sys_config('site_url'), PHP_URL_HOST) ?: $request->host();
        $domainArr = array_merge($domainArr, [$siteHost]);
        $domainArr = array_unique(array_diff($domainArr, ['']));
        if (count($domainArr)) {
            $domainArr = array_map(function ($item) {
                return str_replace(['https://', 'http://'], '', $item);
            }, $domainArr);
        }
        $domainArr[] = 'mp.weixin.qq.com';
        $imageUrlHost = $imageUrl ? (parse_url($imageUrl)['host'] ?? $imageUrl) : $imageUrl;
        $codeUrlHost = $codeUrl ? (parse_url($codeUrl)['host'] ?? $codeUrl) : $codeUrl;
        if ($domainArr && (($imageUrl && !in_array($imageUrlHost, $domainArr)) || ($codeUrl && !in_array($codeUrlHost, $domainArr)))) {
            return app('json')->success(['code' => false, 'image' => false]);
        }
        if ($imageUrl !== '' && !preg_match('/.*(\.png|\.jpg|\.jpeg|\.gif)$/', $imageUrl) && strpos(strtolower($imageUrl), "phar://") !== false) {
            return app('json')->success(['code' => false, 'image' => false]);
        }
        if ($codeUrl !== '' && !(preg_match('/.*(\.png|\.jpg|\.jpeg|\.gif)$/', $codeUrl) || strpos($codeUrl, 'https://mp.weixin.qq.com/cgi-bin/showqrcode') !== false) && strpos(strtolower($codeUrl), "phar://") !== false) {
            return app('json')->success(['code' => false, 'image' => false]);
        }
        try {
            $code = CacheService::remember($codeUrl, function () use ($codeUrl) {
                $codeTmp = $code = $codeUrl ? image_to_base64($codeUrl) : false;
                if (!$codeTmp) {
                    $putCodeUrl = put_image($codeUrl);
                    //TODO
                    $code = $putCodeUrl ? image_to_base64(app()->request->domain(true) . '/' . $putCodeUrl) : false;
                    if ($putCodeUrl) {
                        unlink($_SERVER["DOCUMENT_ROOT"] . DS . $putCodeUrl);
                    }
                }
                return $code;
            });
            $image = CacheService::remember($imageUrl, function () use ($imageUrl) {
                $imageTmp = $image = $imageUrl ? image_to_base64($imageUrl) : false;
                if (!$imageTmp) {
                    $putImageUrl = put_image($imageUrl);
                    //TODO
                    $image = $putImageUrl ? image_to_base64(app()->request->domain(true) . '/' . $putImageUrl) : false;
                    if ($putImageUrl) {
                        unlink($_SERVER["DOCUMENT_ROOT"] . DS . $putImageUrl);
                    }
                }
                return $image;
            });
            return app('json')->success(compact('code', 'image'));
        } catch (\Exception $e) {
            return app('json')->fail('操作失败');
        }
    }

    /**
     * 门店列表
     * @return mixed
     */
    public function storeList(Request $request, SystemStoreServices $services)
    {
        list($latitude, $longitude) = $request->getMore([
            ['latitude', ''],
            ['longitude', ''],
        ], true);
        $latitude = $this->filterCoordinate($latitude, -90, 90);
        $longitude = $this->filterCoordinate($longitude, -180, 180);
        if ($latitude === false || $longitude === false) {
            return app('json')->fail('经纬度参数错误');
        }
        $data['list'] = $services->getStoreList(['type' => 0], ['id', 'name', 'phone', 'address', 'detailed_address', 'image', 'latitude', 'longitude'], $latitude, $longitude);
        $data['tengxun_map_key'] = sys_config('tengxun_map_key');
        return app('json')->success($data);
    }

    /**
     * 过滤经纬度参数
     * @param mixed $coordinate
     * @param float $min
     * @param float $max
     * @return false|string
     */
    private function filterCoordinate($coordinate, float $min, float $max)
    {
        if ($coordinate === null) {
            return '';
        }
        if (is_string($coordinate)) {
            $coordinate = trim($coordinate);
        }
        if ($coordinate === '') {
            return '';
        }
        if (!is_scalar($coordinate) || !is_numeric($coordinate)) {
            return false;
        }
        $coordinate = (float)$coordinate;
        if ($coordinate < $min || $coordinate > $max) {
            return false;
        }
        return rtrim(rtrim(sprintf('%.8F', $coordinate), '0'), '.');
    }

    /**
     * 查找城市数据
     * @param Request $request
     * @return mixed
     */
    public function cityList(Request $request)
    {
        /** @var SystemCityServices $systemCity */
        $systemCity = app()->make(SystemCityServices::class);
        return app('json')->success($systemCity->cityList());
    }

    /**
     * 获取拼团数据
     * @return mixed
     */
    public function pink(UserServices $user)
    {
        //通过事件代理查询拼团数量，避免 app 层直接引用 modules
        $results = event('ActivityDataProxy', ['mode' => 'combination', 'sub_service' => 'pink', 'action' => 'getCount', 'where' => ['is_refund' => 0]]);
        $data['pink_count'] = proxy_event_result($results, 0);
        //通过事件代理查询拼团用户UID列表，避免 app 层直接引用 modules
        $results = event('ActivityDataProxy', ['mode' => 'combination', 'sub_service' => 'pink', 'action' => 'getColumn', 'args' => [['is_refund' => 0], 'uid']]);
        $column = proxy_event_result($results, []);
        if (!is_array($column)) {
            $column = [];
        }
        $uids = array_flip($column);
        if (count($uids)) {
            $uids = array_rand($uids, count($uids) < 3 ? count($uids) : 3);
        }
        $data['avatars'] = $uids ? $user->getColumn(is_array($uids) ? [['uid', 'in', $uids]] : ['uid' => $uids], 'avatar') : [];
        foreach ($data['avatars'] as &$avatar) {
            if (strpos($avatar, '/statics/system_images/') !== false) {
                $avatar = set_file_url($avatar);
            }
        }
        return app('json')->success($data);
    }

    /**
     * 复制口令接口
     * @return mixed
     */
    public function copyWords()
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
    public function copyShareWords(Request $request)
    {
        list($productId) = $request->getMore([
            ['product_id', ''],
        ], true);
        /** @var CenterProductServices $productService */
        $productService = app()->make(CenterProductServices::class);
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
    public function homeProductsList(Request $request, DiyServices $services)
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
                return app('json')->success('暂无数据');
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
        $version = parse_ini_file(app()->getRootPath() . '.version');
        return app('json')->success(['version' => $version['version'], 'version_code' => $version['version_code']]);
    }

    /**
     * 获取多语言缓存
     * @return \think\Response
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/06
     */
    public function getLangVersion()
    {
        return app('json')->success(app()->make(LangCodeServices::class)->getLangVersion());
    }

    /**
     * 商城基础配置汇总接口
     * @return \think\Response
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/04/03
     */
    public function getMallBasicConfig()
    {
        $data['site_name'] = sys_config('site_name'); //网站名称
        $data['site_url'] = sys_config('site_url'); //网站地址
        $data['wap_login_logo'] = sys_config('wap_login_logo'); //移动端登录logo
        $data['record_No'] = sys_config('record_No'); //备案号
        $data['icp_url'] = sys_config('icp_url'); //备案号链接
        $data['network_security'] = sys_config('network_security'); //网安备案
        $data['network_security_url'] = sys_config('network_security_url'); //网安备案链接
        $data['store_self_mention'] = sys_config('store_self_mention'); //是否开启到店自提
        $data['invoice_func_status'] = sys_config('invoice_func_status'); //发票功能启用
        $data['special_invoice_status'] = sys_config('special_invoice_status'); //专用发票启用
        $data['member_func_status'] = sys_config('member_func_status'); //用户等级启用
        $data['balance_func_status'] = sys_config('balance_func_status'); //余额功能启用
        $data['recharge_switch'] = sys_config('recharge_switch'); //小程序充值开关
        $data['member_card_status'] = sys_config('member_card_status'); //是否开启付费会员
        $data['member_price_status'] = sys_config('member_price_status'); //商品会员折扣价展示启用
        /** @var PayChannelServices $payChannelServices */
        $payChannelServices = app()->make(PayChannelServices::class);
        $data['ali_pay_status'] = $payChannelServices->getChannelStatus('alipay'); //支付宝是否启用
        $data['pay_weixin_open'] = $payChannelServices->getChannelStatus('weixin'); //微信是否启用
        $data['yue_pay_status'] = $payChannelServices->getChannelStatus('yue'); //余额支付渠道是否启用
        $data['offline_pay_status'] = $payChannelServices->getChannelStatus('offline'); //线下是否启用
        $data['friend_pay_status'] = $payChannelServices->getChannelStatus('friend'); //好友是否启用
        $data['wechat_auth_switch'] = (int)in_array(1, sys_config('routine_auth_type')); //微信登录开关
        $data['phone_auth_switch'] = (int)in_array(2, sys_config('routine_auth_type')); //手机号登录开关
        $data['wechat_status'] = sys_config('wechat_appid') !== '' && sys_config('wechat_appsecret') !== ''; //公众号是否配置
        $data['site_func'] = sys_config('model_checkbox', ['seckill', 'bargain', 'combination']);
        return app('json')->success($data);
    }

    /**
     * 小程序跳转链接接口
     * @param $id
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/2/26
     */
    public function getSchemeUrl($id)
    {
        //通过事件代理查询小程序方案URL，避免 app 层直接引用 modules
        $results = event('ChannelServiceProxy', ['endpoint' => 'wechat_mini', 'service' => 'RoutineSchemeServices', 'action' => 'value', 'args' => [$id, 'url']]);
        $url = proxy_event_result($results, '');
        if ($url) {
            echo '<script>window.location.href="' . $url . '";</script>';
        } else {
            echo '<h1>未找到跳转路径</h1>';
        }
    }

    /**
     * 微信服务商支付
     * @param Request $request
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/4/7
     */
    public function servicePayResult(Request $request)
    {
        [$sub_mch_id, $out_trade_no, $check_code] = $request->getMore([
            ['sub_mch_id', ''],
            ['out_trade_no', ''],
            ['check_code', ''],
        ], true);
        $data['site_name'] = sys_config('site_name'); //网站名称
        $data['site_url'] = sys_config('site_url'); //网站地址
        $data['site_logo'] = sys_config('wap_login_logo'); //移动端登录logo
        $order = app()->make(OrderCenterDao::class)->getOne(['order_no' => $out_trade_no]);
        if (!$order) {
            throw new ApiException('订单不存在');
        }
        $data['goods_name'] = app()->make(OrderCenterServices::class)->getCarIdByProductTitle((int)$order['id']);
        $data['pay_price'] = $order['pay_price'];
        $data['jump_url'] = sys_config('site_url') . '/pages/goods/order_pay_status/index?order_id=' . $out_trade_no . '&msg=支付成功&type=3&totalPrice=' . $data['pay_price'];
        return app('json')->header(['X-Frame-Options' => 'payapp.weixin.qq.com'])->success($data);
    }

    public function getTransferInfo(Request $request, $order_id, $type)
    {
        $extractServices = app()->make(UserExtractServices::class);
        $uid = (int)$request->uid();
        if ($type == 1) {
            return app('json')->success($extractServices->transferInfo($uid, (string)$order_id));
        } else {
            //通过事件代理查询抽奖记录，避免 app 层直接引用 modules
            $results = event('MarketingServiceProxy', ['module' => 'lottery', 'service' => 'LuckLotteryRecordServices', 'action' => 'getRecordByOrderId', 'args' => [$uid, $order_id]]);
            $info = proxy_event_result($results, []);
            $info['true_extract_price'] = $info['num'];
        }
        if ($info['state'] == 'WAIT_USER_CONFIRM') {
            $pay = new Pay('v3_wechat_pay');
            $res = $pay->queryTransferBills($order_id);
            if (isset($res['fail_reason']) && $res['fail_reason'] !== '') {
                //通过事件代理更新抽奖记录状态为失败，避免 app 层直接引用 modules
                event('MarketingServiceProxy', ['module' => 'lottery', 'service' => 'LuckLotteryRecordServices', 'action' => 'update', 'args' => [$info['id'], ['state' => 'FAIL']]]);
                $info['state'] = 'FAIL';
            }
        }
        // 转账由 V3 支付渠道发起，H5 展示层所需 AppID 也从同一支付渠道读取，
        // 避免因应用模块配置为空而导致支付参数不一致。
        $v3PayConfig = PayChannelConfig::get('weixin_v3');
        switch ($info['channel_type']) {
            case 'wechat':
                $info['wechat_appid'] = $v3PayConfig['wechat_appid'] ?? '';
                break;
            case 'routine':
                $info['wechat_appid'] = $v3PayConfig['routine_appId'] ?? '';
                break;
            case 'app':
                $info['wechat_appid'] = $v3PayConfig['wechat_app_appid'] ?? '';
                break;
        }
        // 转账查询固定使用 V3 驱动，商户号也必须读取 V3 渠道配置。
        $info['mchid'] = $v3PayConfig['pay_weixin_mchid'] ?? '';
        return app('json')->success($info);
    }

    /**
     * 获取主题信息
     * @param string $type 主题类型，当为'user'时会附加用户权限和订单统计信息
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/12/25
     */
    public function themeInfo(Request $request, $type = '')
    {
        // 获取主题ID参数
        [$theme_id] = $request->getMore([
            ['theme_id', 0],
        ], true);
        $themeInfo = app()->make(ThemeServices::class)->getThemeInfo($theme_id, $type);

        if (in_array($type, ['home', 'detail', 'user']) && $themeInfo) {
            foreach ($themeInfo['value'] as &$userDataItem) {
                if ($userDataItem['name'] == 'customerService') {
                    $userDataItem['routine_contact_type'] = (int)sys_config('routine_contact_type');
                }
            }
            // 不可用组件节点已在数据出口直接剔除（见下），前端拿到的即干净数据，无需任何过滤判断
            /** @var DiyComponentServices $diyComponentServices */
            $diyComponentServices = app()->make(DiyComponentServices::class);
            $themeInfo['value'] = $diyComponentServices->filterDiyValue($themeInfo['value']);
        }

        // 当类型为'user'时，处理用户相关的权限和菜单配置
        if ($type == 'user') {
            // 初始化用户信息
            $uid = 0;
            $userInfo = [];
            if ($request->hasMacro('uid')) $uid = $request->uid();
            if ($request->hasMacro('user')) $userInfo = $request->user();

            // 获取系统功能开关配置
            //用户等级开关
            $levelOpen = (bool)sys_config('member_func_status');
            //分销功能开关
            $brokerageOpen = (bool)sys_config('brokerage_func_status');
            //余额功能开关
            $balanceOpen = (bool)sys_config('balance_func_status');
            //付费会员开关
            $sVipOpen = (bool)sys_config('member_card_status');
            //发票功能
            $invoiceOpen = (bool)sys_config('invoice_func_status');

            // 初始化用户角色标识
            $userIsService = $userIsOrder = $userIsVerify = $userIsDelivery = $userIsPromoter = false;

            if ($uid && $userInfo) {
                /** @var OrderCenterDao $orderCenterDao */
                $orderCenterDao = app()->make(OrderCenterDao::class);

                // 检查用户角色权限
                //是否客服
                $userIsService = (bool)(event('KefuConfigQuery', ['uid' => $uid, 'customer' => 0])[0] ?? false);
                //是否订单管理
                $userIsOrder = (bool)(event('KefuConfigQuery', ['uid' => $uid, 'customer' => 1])[0] ?? false);
                //是否核销员
                $userIsVerify = (bool)app()->make(SystemStoreStaffServices::class)->verifyStatus($uid);
                //是否配送员
                $userIsDelivery = (bool)app()->make(ServicePersonnelServices::class)->checkoutIsService($uid);
                //是否分销员
                $userIsPromoter = (bool)app()->make(UserServices::class)->checkUserPromoter($uid, $userInfo);

                // 统计各状态订单数量，用于菜单角标显示（基于 eb_order_center 表）
                $orderAuth = [];
                $countWhere = ['is_del' => 0, 'uid' => $uid];
                // 全部订单（不含退款中和已退款）
                $orderAuth['/pages/goods/order_list/index'] = (int)$orderCenterDao->count($countWhere + ['status_code' => ['pending_payment', 'processing', 'completed']]);
                // 待付款
                $orderAuth['/pages/goods/order_list/index?status=0'] = (int)$orderCenterDao->count($countWhere + ['status_code' => 'pending_payment']);
                // 待发货：processing + delivery_time 为空
                $orderAuth['/pages/goods/order_list/index?status=1'] = (int)$orderCenterDao->count($countWhere + ['status_code' => 'processing', ['delivery_time', 'null', '']]);
                // 待收货：processing + delivery_time 不为空 + take_time 为空
                $orderAuth['/pages/goods/order_list/index?status=2'] = (int)$orderCenterDao->count($countWhere + ['status_code' => 'processing', ['delivery_time', 'not null', ''], ['take_time', 'null', '']]);
                // 待评价：completed（需 JOIN 扩展表精确区分，此处近似取值）
                $orderAuth['/pages/goods/order_list/index?status=3'] = (int)$orderCenterDao->count($countWhere + ['status_code' => 'completed']);
                // 已完成：completed
                $orderAuth['/pages/goods/order_list/index?status=4'] = (int)$orderCenterDao->count($countWhere + ['status_code' => 'completed']);
                $orderAuth['/pages/users/user_return_list/index'] = (int)$orderCenterDao->getDb()->where([
                    'uid' => $uid,
                    'is_del' => 0,
                    'refund_status' => \app\model\order\OrderCenter::REFUND_APPLYING,
                ])->count();
            }

            // 配置各页面的访问权限
            $auth = [];
            $auth['/pages/users/user_vip/index'] = $levelOpen;
            $auth['/pages/users/user_spread_user/index'] = $brokerageOpen && $userIsPromoter;
            $auth['/pages/annex/settled/index'] = $brokerageOpen && sys_config('store_brokerage_statu') == 1 && !$userIsPromoter;
            $auth['/pages/users/user_money/index'] = $balanceOpen;
            $auth['/pages/users/user_invoice_list/index'] = $invoiceOpen;
            $auth['/pages/annex/vip_paid/index'] = $sVipOpen;
            $auth['/kefu/mobile_list'] = $userIsService;

            // 处理主题菜单配置
            if ($themeInfo) {
                foreach ($themeInfo['value'] as &$userDataItem) {
                    if ($userDataItem['name'] == 'menus') {
                        foreach ($userDataItem['menuConfig']['list'] as &$menuDataItem) {
                            // 设置菜单显示权限和角标数量
                            $menuDataItem['show'] = ($auth[$menuDataItem['info'][1]['value']] ?? true) && $menuDataItem['show'];
                            $menuDataItem['num'] = $orderAuth[$menuDataItem['info'][1]['value']] ?? 0;

                            // 处理客服链接，拼接完整URL
                            if ($menuDataItem['info'][1]['value'] == '/kefu/mobile_list') {
                                $menuDataItem['info'][1]['value'] = sys_config('site_url') . $menuDataItem['info'][1]['value'];
                                // 小程序环境强制使用https
                                if ($request->isRoutine()) {
                                    $menuDataItem['info'][1]['value'] = str_replace('http://', 'https://', $menuDataItem['info'][1]['value']);
                                }
                            }

                            // 处理客服聊天页面，添加联系方式类型配置
                            if ($menuDataItem['info'][1]['value'] == '/pages/extension/customer_list/chat') {
                                if ($request->isRoutine()) {
                                    $menuDataItem['routine_contact_type'] = (int)sys_config('routine_contact_type', 0);
                                }
                            }

                            if ($menuDataItem['info'][1]['value'] == '/pages/users/user_spread_user/index' && $brokerageOpen && sys_config('store_brokerage_statu') == 1 && !$userIsPromoter) {
                                $menuDataItem['info'][0]['value'] = '分销申请';
                                $menuDataItem['info'][1]['value'] = '/pages/annex/settled/index';
                                $menuDataItem['show'] = true;
                            }
                        }
                    }
                }
            }
        }

        return app('json')->success($themeInfo);
    }

    /**
     * 获取主题版本信息
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/12/25
     */
    public function themeVersion(Request $request)
    {
        [$theme_id] = $request->getMore([
            ['theme_id', 0],
        ], true);
        $themeVersion = app()->make(ThemeServices::class)->getThemeVersion($theme_id);
        return app('json')->success(['version' => $themeVersion]);
    }

    /**
     * 自定义组件-用户
     * @param Request $request
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/1/12
     */
    public function themeUser(Request $request)
    {
        $userInfo = $request->uid() ? $request->user() : [];
        if (!$userInfo) return app('json')->fail('暂无数据');
        $user = [
            'nickname' => $userInfo['nickname'],
            'uid' => $userInfo['uid'],
            'image' => $userInfo['avatar'],
            'collection_num' => app()->make(StoreProductRelationServices::class)->count(['uid' => $userInfo['uid']]),
            'cart_num' => app()->make(StoreCartServices::class)->count(['uid' => $userInfo['uid']]),
            'order_num' => $userInfo['pay_count'],
            'integral' => $userInfo['integral'],
            'now_money' => $userInfo['now_money'],
            'brokerage_price' => $userInfo['brokerage_price'],
            'unread_msg_num' => app()->make(\app\services\message\NoticeServices::class)->getUnreadCount($userInfo['uid']),
        ];
        return app('json')->success($user);
    }

    /**
     * 自定义组件-文章
     * @param Request $request
     * @return \think\Response
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/1/12
     */
    public function themeArticle(Request $request)
    {
        $where = $request->getMore([
            ['ids', ''],
            ['cid', ''],
            ['order', 0],
            ['sort', 0],
            ['limit', 10],
        ]);
        $data = app()->make(ArticleServices::class)->getThemeArticle($where);
        return app('json')->success($data);
    }

    /**
     * 自定义组件-优惠券
     * @param Request $request
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/1/14
     */
    public function themeCoupon(Request $request)
    {
        $where = $request->getMore([
            ['ids', ''],
            ['type', ''],
            ['user_type', ''],
            ['send_type', ''],
            ['is_min_price', 0],
            ['min_price', 0],
            ['start_time', ''],
            ['end_time', ''],
            ['order', 0],
            ['sort', 0],
            ['limit', 10],
        ]);
        $data = app()->make(StoreCouponIssueServices::class)->getThemeCoupon($where);
        return app('json')->success($data);
    }

    /**
     * 自定义组件-商品
     * @param Request $request
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/1/14
     */
    public function themeProduct(Request $request)
    {
        $where = $request->getMore([
            ['ids', ''],
            ['cate_ids', ''],
            ['order', 0],
            ['sort', 0],
            ['limit', 10],
        ]);
        $data = app()->make(CenterProductServices::class)->getThemeProduct($where);
        return app('json')->success($data);
    }

    /**
     * 获取主题导航数据
     * 调用 ThemeServices 中的 themeNavigation 方法获取导航配置并返回 JSON 响应
     * @return mixed
     */
    public function themeNavigation()
    {
        // 实例化 ThemeServices 并调用 themeNavigation 方法获取导航数据
        $data = app()->make(ThemeServices::class)->themeNavigation();
        // 返回成功响应，包含导航数据
        return app('json')->success($data);
    }

    /**
     * 主页获取(精简版)
     * @param Request $request
     * @return mixed
     */
    public function indexV2(Request $request)
    {
        $fastNumber = (int)sys_config('fast_number', 0);//TODO 快速选择分类个数
        /** @var StoreCategoryServices $categoryService */
        $categoryService = app()->make(StoreCategoryServices::class);
        $info['fastList'] = $fastNumber ? $categoryService->byIndexList($fastNumber, 'id,cate_name,pid,pic') : [];//TODO 快速选择分类个数
        /** @var CenterProductServices $storeProductServices */
        $storeProductServices = app()->make(CenterProductServices::class);
        // 获取猜你喜欢推荐商品
        [, , , $likeInfo] = $storeProductServices->getRecommendProductArr((int)$request->uid(), []);
        if ($request->uid()) {
            /** @var UserServices $userService */
            $userService = app()->make(UserServices::class);
            //看是否会员过期
            $userService->offMemberLevel($request->uid());
            //通过事件代理查询用户关注状态，避免 app 层直接引用 modules
            $results = event('ChannelServiceProxy', ['endpoint' => 'wechat_mp', 'service' => 'WechatUserServices', 'action' => 'value', 'args' => [['uid' => $request->uid(), 'user_type' => 'wechat'], 'subscribe']]);
            $subscribe = (bool)proxy_event_result($results, false);
        } else {
            $subscribe = true;
        }
        $tengxun_map_key = sys_config('tengxun_map_key');
        $site_name = sys_config('site_name');
        return app('json')->success(compact('info', 'likeInfo', 'subscribe', 'tengxun_map_key', 'site_name'));
    }

    /**
     * 获取页面数据(v2)
     * @param Request $request
     * @param string $name
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDiyV2(Request $request, $name = '')
    {
        list($id) = $request->getMore([
            ['id', 0]
        ], true);
        /** @var DiyServices $diyService */
        $diyService = app()->make(DiyServices::class);
        $data = $diyService->getDiy($id);
        // 数据出口直接剔除不可用组件节点（节点列表与关联对象两种形态均已覆盖），
        // 前端拿到的即干净数据，插件重新启用后数据自动恢复（运行时过滤，不落库）
        if (is_array($data)) {
            /** @var DiyComponentServices $diyComponentServices */
            $diyComponentServices = app()->make(DiyComponentServices::class);
            if (isset($data['value']) && is_array($data['value'])) {
                $data['value'] = $diyComponentServices->filterDiyValue($data['value']);
            } else {
                $data = $diyComponentServices->filterDiyValue($data);
            }
        }
        return app('json')->success($data);
    }

    /**
     * 获取DIY版本号(v2)
     * @param int $id
     * @return mixed
     */
    public function getVersionV2($id = 0)
    {
        /** @var DiyServices $diyService */
        $diyService = app()->make(DiyServices::class);
        $data = $diyService->getDiyVersion((int)$id) ?: [];
        return app('json')->success(['version' => $data['version'] ?? '', 'is_diy' => $data['is_diy'] ?? 0]);
    }

    /**
     * 是否强制绑定手机号
     * @return mixed
     */
    public function bindPhoneStatus()
    {
        $status = (bool)sys_config('store_user_mobile');
        return app('json')->success(compact('status'));
    }

    /**
     * 是否关注(v2)
     * @param Request $request
     * @return mixed
     */
    public function subscribeV2(Request $request)
    {
        //通过事件代理查询用户关注状态，避免 app 层直接引用 modules
        $results = event('ChannelServiceProxy', ['endpoint' => 'wechat_mp', 'service' => 'WechatUserServices', 'action' => 'value', 'args' => [['uid' => $request->uid(), 'user_type' => 'wechat'], 'subscribe']]);
        $subscribe = (bool)proxy_event_result($results, false);
        return app('json')->success(['subscribe' => $subscribe]);
    }

    /**
     * 获取提货点自提开启状态
     * @return mixed
     */
    public function getStoreStatus()
    {
        $data['store_status'] = sys_config('store_self_mention', 0);
        return app('json')->success($data);
    }

    /**
     * 获取颜色选择和分类模板选择
     * @param DiyServices $services
     * @param $name
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function colorChange(DiyServices $services, $name)
    {
        $status = (int)$services->getColorChange((string)$name);
        $is_diy = $services->value(['status' => 1, 'is_del' => 0], 'is_diy');
        return app('json')->success(compact('status', 'is_diy'));
    }

    /**
     * 获取Diy签到
     * @param Request $request
     * @return mixed
     */
    public function getDiySign(Request $request)
    {
        $uid = (int)$request->uid();
        //通过事件代理查询签到配置，避免 app 层直接引用 modules
        $results = event('MarketingServiceProxy', ['module' => 'signin', 'service' => 'UserSignServices', 'action' => 'signConfig', 'args' => [$uid, 1]]);
        return app('json')->success(proxy_event_result($results, []));
    }
}
