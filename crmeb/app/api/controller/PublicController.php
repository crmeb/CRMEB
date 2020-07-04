<?php

namespace app\api\controller;

use app\admin\model\system\SystemAttachment;
use app\models\store\StoreCategory;
use app\models\store\StoreCouponIssue;
use app\models\store\StorePink;
use app\models\store\StoreProduct;
use app\models\store\StoreService;
use app\models\system\Express;
use app\models\system\SystemCity;
use app\models\system\SystemStore;
use app\models\system\SystemStoreStaff;
use app\models\user\User;
use app\models\user\UserBill;
use app\models\user\WechatUser;
use app\Request;
use crmeb\services\CacheService;
use crmeb\services\UtilService;
use crmeb\services\workerman\ChannelService;
use think\facade\Cache;
use crmeb\services\upload\Upload;

/**
 * 公共类
 * Class PublicController
 * @package app\api\controller
 */
class PublicController
{
    /**
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
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
        $info['fastInfo'] = $routine_index_page[0]['fast_info'] ?? '';//sys_config('fast_info');//TODO 快速选择简介
        $info['bastInfo'] = $routine_index_page[0]['bast_info'] ?? '';//sys_config('bast_info');//TODO 精品推荐简介
        $info['firstInfo'] = $routine_index_page[0]['first_info'] ?? '';//sys_config('first_info');//TODO 首发新品简介
        $info['salesInfo'] = $routine_index_page[0]['sales_info'] ?? '';//sys_config('sales_info');//TODO 促销单品简介
        $logoUrl = sys_config('routine_index_logo');//TODO 促销单品简介
        if (strstr($logoUrl, 'http') === false && $logoUrl) $logoUrl = sys_config('site_url') . $logoUrl;
        $logoUrl = str_replace('\\', '/', $logoUrl);
        $fastNumber = sys_config('fast_number', 0);//TODO 快速选择分类个数
        $bastNumber = sys_config('bast_number', 0);//TODO 精品推荐个数
        $firstNumber = sys_config('first_number', 0);//TODO 首发新品个数
        $promotionNumber = sys_config('promotion_number', 0);//TODO 首发新品个数
        $info['fastList'] = StoreCategory::byIndexList((int)$fastNumber, false);//TODO 快速选择分类个数
        $info['bastList'] = StoreProduct::getBestProduct('id,image,store_name,cate_id,price,ot_price,IFNULL(sales,0) + IFNULL(ficti,0) as sales,unit_name', (int)$bastNumber, $request->uid(), false);//TODO 精品推荐个数
        $info['firstList'] = StoreProduct::getNewProduct('id,image,store_name,cate_id,price,unit_name,IFNULL(sales,0) + IFNULL(ficti,0) as sales', (int)$firstNumber, $request->uid(), false);//TODO 首发新品个数
        $info['bastBanner'] = sys_data('routine_home_bast_banner') ?? [];//TODO 首页精品推荐图片
        $benefit = StoreProduct::getBenefitProduct('id,image,store_name,cate_id,price,ot_price,stock,unit_name', $promotionNumber);//TODO 首页促销单品
        $lovely = sys_data('routine_home_new_banner') ?: [];//TODO 首发新品顶部图
        $likeInfo = StoreProduct::getHotProduct('id,image,store_name,cate_id,price,ot_price,unit_name', 3);//TODO 热门榜单 猜你喜欢
        $couponList = StoreCouponIssue::getIssueCouponList($request->uid(), 3);
        if ($request->uid()) {
            $subscribe = WechatUser::where('uid', $request->uid())->value('subscribe') ? true : false;
        } else {
            $subscribe = true;
        }
        $newGoodsBananr = sys_config('new_goods_bananr');
        $tengxun_map_key = sys_config('tengxun_map_key');
        return app('json')->successful(compact('banner', 'menus', 'roll', 'info', 'activity', 'lovely', 'benefit', 'likeInfo', 'logoUrl', 'couponList', 'site_name', 'subscribe', 'newGoodsBananr', 'tengxun_map_key', 'explosive_money'));
    }

    /**
     * 获取分享配置
     * @return mixed
     */
    public function share()
    {
        $data['img'] = sys_config('wechat_share_img');
        if (strstr($data['img'], 'http') === false) $data['img'] = sys_config('site_url') . $data['img'];
        $data['img'] = str_replace('\\', '/', $data['img']);
        $data['title'] = sys_config('wechat_share_title');
        $data['synopsis'] = sys_config('wechat_share_synopsis');
        return app('json')->successful(compact('data'));
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
        $user = $request->user();
        $vipOpen = sys_config('vip_open');
        $vipOpen = is_string($vipOpen) ? (int)$vipOpen : $vipOpen;
        foreach ($menusInfo as $key => &$value) {
            $value['pic'] = set_file_url($value['pic']);
            if ($value['id'] == 137 && !(intval(sys_config('store_brokerage_statu')) == 2 || $user->is_promoter == 1))
                unset($menusInfo[$key]);
            if ($value['id'] == 174 && !StoreService::orderServiceStatus($user->uid))
                unset($menusInfo[$key]);
            if (((!StoreService::orderServiceStatus($user->uid)) && (!SystemStoreStaff::verifyStatus($user->uid))) && $value['wap_url'] === '/order/order_cancellation')
                unset($menusInfo[$key]);
            if (((!StoreService::orderServiceStatus($user->uid)) && (!SystemStoreStaff::verifyStatus($user->uid))) && $value['wap_url'] === '/admin/order_cancellation/index')
                unset($menusInfo[$key]);
            if ((!StoreService::orderServiceStatus($user->uid)) && $value['wap_url'] === '/admin/order/index')
                unset($menusInfo[$key]);
            if ($value['wap_url'] == '/user/vip' && !$vipOpen)
                unset($menusInfo[$key]);
            if ($value['wap_url'] == '/customer/index' && !StoreService::orderServiceStatus($user->uid))
                unset($menusInfo[$key]);
        }
        return app('json')->successful(['routine_my_menus' => $menusInfo]);
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
        return app('json')->successful($searchKeyword);
    }


    /**
     * 图片上传
     * @param Request $request
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function upload_image(Request $request)
    {
        $data = UtilService::postMore([
            ['filename', 'file'],
        ], $request);
        if (!$data['filename']) return app('json')->fail('参数有误');
        if (Cache::has('start_uploads_' . $request->uid()) && Cache::get('start_uploads_' . $request->uid()) >= 100) return app('json')->fail('非法操作');
        $upload_type = sys_config('upload_type', 1);
        $upload = new Upload((int)$upload_type, [
            'accessKey' => sys_config('accessKey'),
            'secretKey' => sys_config('secretKey'),
            'uploadUrl' => sys_config('uploadUrl'),
            'storageName' => sys_config('storage_name'),
            'storageRegion' => sys_config('storage_region'),
        ]);
        $info = $upload->to('store/comment')->validate()->move($data['filename']);
        if ($info === false) {
            return app('json')->fail($upload->getError());
        }
        $res = $upload->getUploadInfo();
        SystemAttachment::attachmentAdd($res['name'], $res['size'], $res['type'], $res['dir'], $res['thumb_path'], 1, $upload_type, $res['time'], 2);
        if (Cache::has('start_uploads_' . $request->uid()))
            $start_uploads = (int)Cache::get('start_uploads_' . $request->uid());
        else
            $start_uploads = 0;
        $start_uploads++;
        Cache::set('start_uploads_' . $request->uid(), $start_uploads, 86400);
        $res['dir'] = path_to_url($res['dir']);
        if (strpos($res['dir'], 'http') === false) $res['dir'] = $request->domain() . $res['dir'];
        return app('json')->successful('图片上传成功!', ['name' => $res['name'], 'url' => $res['dir']]);
    }

    /**
     * 物流公司
     * @return mixed
     */
    public function logistics()
    {
        $expressList = Express::lst();
        if (!$expressList) return app('json')->successful([]);
        return app('json')->successful($expressList->hidden(['code', 'id', 'sort', 'is_show'])->toArray());
    }

    /**
     * 短信购买异步通知
     *
     * @param Request $request
     * @return mixed
     */
    public function sms_pay_notify(Request $request)
    {
        list($order_id, $price, $status, $num, $pay_time, $attach) = UtilService::postMore([
            ['order_id', ''],
            ['price', 0.00],
            ['status', 400],
            ['num', 0],
            ['pay_time', time()],
            ['attach', 0],
        ], $request, true);
        if ($status == 200) {
            ChannelService::instance()->send('PAY_SMS_SUCCESS', ['price' => $price, 'number' => $num], [$attach]);
            return app('json')->successful();
        }
        return app('json')->fail();
    }

    /**
     * 记录用户分享
     * @param Request $request
     * @return mixed
     */
    public function user_share(Request $request)
    {
        return app('json')->successful(UserBill::setUserShare($request->uid()));
    }

    /**
     * 获取图片base64
     * @param Request $request
     * @return mixed
     */
    public function get_image_base64(Request $request)
    {
        list($imageUrl, $codeUrl) = UtilService::postMore([
            ['image', ''],
            ['code', ''],
        ], $request, true);
        try {
            $codeTmp = $code = $codeUrl ? image_to_base64($codeUrl) : false;
            if (!$codeTmp) {
                $putCodeUrl = put_image($codeUrl);
                $code = $putCodeUrl ? image_to_base64($_SERVER['HTTP_HOST'] . '/' . $putCodeUrl) : false;
                $code ?? unlink($_SERVER["DOCUMENT_ROOT"] . '/' . $putCodeUrl);
            }

            $imageTmp = $image = $imageUrl ? image_to_base64($imageUrl) : false;
            if (!$imageTmp) {
                $putImageUrl = put_image($imageUrl);
                $image = $putImageUrl ? image_to_base64($_SERVER['HTTP_HOST'] . '/' . $putImageUrl) : false;
                $image ?? unlink($_SERVER["DOCUMENT_ROOT"] . '/' . $putImageUrl);
            }
            return app('json')->successful(compact('code', 'image'));
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 门店列表
     * @return mixed
     */
    public function store_list(Request $request)
    {
        list($latitude, $longitude, $page, $limit) = UtilService::getMore([
            ['latitude', ''],
            ['longitude', ''],
            ['page', 1],
            ['limit', 10]
        ], $request, true);
        $list = SystemStore::lst($latitude, $longitude, $page, $limit);
        if (!$list) $list = [];
        $data['list'] = $list;
        $data['tengxun_map_key'] = sys_config('tengxun_map_key');
        return app('json')->successful($data);
    }

    /**
     * 查找城市数据
     * @param Request $request
     * @return mixed
     */
    public function city_list(Request $request)
    {
        $list = CacheService::get('CITY_LIST', function () {
            $list = SystemCity::with('children')->field(['city_id', 'name', 'id', 'parent_id'])->where('parent_id', 0)->order('id asc')->select()->toArray();
            $data = [];
            foreach ($list as &$item) {
                $value = ['v' => $item['city_id'], 'n' => $item['name']];
                if ($item['children']) {
                    foreach ($item['children'] as $key => &$child) {
                        $value['c'][$key] = ['v' => $child['city_id'], 'n' => $child['name']];
                        unset($child['id'], $child['area_code'], $child['merger_name'], $child['is_show'], $child['level'], $child['lng'], $child['lat'], $child['lat']);
                        if (SystemCity::where('parent_id', $child['city_id'])->count()) {
                            $child['children'] = SystemCity::where('parent_id', $child['city_id'])->field(['city_id', 'name', 'id', 'parent_id'])->select()->toArray();
                            foreach ($child['children'] as $kk => $vv) {
                                $value['c'][$key]['c'][$kk] = ['v' => $vv['city_id'], 'n' => $vv['name']];
                            }
                        }
                    }
                }
                $data[] = $value;
            }
            return $data;
        }, 0);
        return app('json')->successful($list);
    }

    /**
     * 获取拼团数据
     * @return mixed
     */
    public function pink()
    {
        $data['pink_count'] = StorePink::where(['status' => 2, 'is_refund' => 0])->count();
        $data['avatars'] = User::whereIn('uid', function ($query) {
            $query->name('store_pink')->where(['status' => 2, 'is_refund' => 0])->field(['uid'])->select();
        })->limit(3)->order('uid desc')->column('avatar');
        return app('json')->successful($data);
    }

}