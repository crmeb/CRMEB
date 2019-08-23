<?php
namespace app\ebapi\controller;

use app\core\model\user\UserBill;
use app\core\model\system\SystemUserLevel;
use app\core\model\system\SystemUserTask;
use app\core\model\user\UserLevel;
use app\ebapi\model\store\StoreCategory;
use app\core\model\routine\RoutineFormId;//待完善
use app\ebapi\model\store\StoreCouponIssue;
use app\ebapi\model\store\StoreProduct;
use app\core\util\GroupDataService;
use service\HttpService;
use service\JsonService;
use app\core\util\SystemConfigService;
use service\UploadService;
use service\UtilService;
use service\CacheService;
use think\Cache;
use Api\Storage\COS\COS;
use Api\Storage\OSS\OSS;
use Api\Storage\Qiniu\Qiniu;
use app\admin\model\system\SystemAttachment;

/**
 * 小程序公共接口
 * Class PublicApi
 * @package app\ebapi\controller
 *
 */
class PublicApi extends AuthController
{
    /*
     * 白名单不验证token 如果传入token执行验证获取信息，没有获取到用户信息
     * */
    public static function whiteList()
    {
        return [
            'index',
            'get_index_groom_list',
            'get_hot_product',
            'refresh_cache',
            'clear_cache',
            'get_logo_url',
            'get_my_naviga',
        ];
    }

    /*
     * 获取个人中心菜单
     * */
    public function get_my_naviga()
    {
        return JsonService::successful(['routine_my_menus'=>GroupDataService::getData('routine_my_menus')]);
    }
    /*
     * 获取授权登录log
     * */
    public function get_logo_url()
    {
        $routine_logo=SystemConfigService::get('routine_logo');
        if(strstr($routine_logo,'http')===false) $routine_logo=SystemConfigService::get('site_url').$routine_logo;
        return JsonService::successful(['logo_url'=>str_replace('\\','/',$routine_logo)]);
    }
    /**
     * TODO 获取首页推荐不同类型产品的轮播图和产品
     * @param int $type
     */
    public function get_index_groom_list($type = 1){
        $info['banner'] = [];
        $info['list'] = [];
        if($type == 1){//TODO 精品推荐
            $info['banner'] = GroupDataService::getData('routine_home_bast_banner')?:[];//TODO 首页精品推荐图片
            $info['list'] = StoreProduct::getBestProduct('id,image,store_name,cate_id,price,ot_price,IFNULL(sales,0) + IFNULL(ficti,0) as sales,unit_name,sort');//TODO 精品推荐个数
        }else if($type == 2){//TODO 热门榜单
            $info['banner'] = GroupDataService::getData('routine_home_hot_banner')?:[];//TODO 热门榜单 猜你喜欢推荐图片
            $info['list'] = StoreProduct::getHotProduct('id,image,store_name,cate_id,price,ot_price,unit_name,sort,IFNULL(sales,0) + IFNULL(ficti,0) as sales',0,$this->uid);//TODO 热门榜单 猜你喜欢
        }else if($type == 3){//TODO 首发新品
            $info['banner'] = GroupDataService::getData('routine_home_new_banner')?:[];//TODO 首发新品推荐图片
            $info['list'] = StoreProduct::getNewProduct('id,image,store_name,cate_id,price,ot_price,unit_name,sort,IFNULL(sales,0) + IFNULL(ficti,0) as sales',0,$this->uid);//TODO 首发新品
        }else if($type == 4){//TODO 促销单品
            $info['banner'] = GroupDataService::getData('routine_home_benefit_banner')?:[];//TODO 促销单品推荐图片
            $info['list'] = StoreProduct::getBenefitProduct('id,image,store_name,cate_id,price,ot_price,stock,unit_name,sort');//TODO 促销单品
        }
        return JsonService::successful($info);
    }

    /**
     * 首页
     */
    public function index(){
        $banner = GroupDataService::getData('routine_home_banner')?:[];//TODO 首页banner图
        $menus = GroupDataService::getData('routine_home_menus')?:[];//TODO 首页按钮
        $roll = GroupDataService::getData('routine_home_roll_news')?:[];//TODO 首页滚动新闻
        $activity = GroupDataService::getData('routine_home_activity',3)?:[];//TODO 首页活动区域图片
        $info['fastInfo'] = SystemConfigService::get('fast_info');//TODO 快速选择简介
        $info['bastInfo'] = SystemConfigService::get('bast_info');//TODO 精品推荐简介
        $info['firstInfo'] = SystemConfigService::get('first_info');//TODO 首发新品简介
        $info['salesInfo'] = SystemConfigService::get('sales_info');//TODO 促销单品简介
        $logoUrl = SystemConfigService::get('routine_index_logo');//TODO 促销单品简介
        if(strstr($logoUrl,'http')===false) $logoUrl=SystemConfigService::get('site_url').$logoUrl;
        $logoUrl = str_replace('\\','/',$logoUrl);
        $fastNumber = (int)SystemConfigService::get('fast_number');//TODO 快速选择分类个数
        $bastNumber = (int)SystemConfigService::get('bast_number');//TODO 精品推荐个数
        $firstNumber = (int)SystemConfigService::get('first_number');//TODO 首发新品个数
        $info['fastList'] = StoreCategory::byIndexList($fastNumber);//TODO 快速选择分类个数
        $info['bastList'] = StoreProduct::getBestProduct('id,image,store_name,cate_id,price,ot_price,IFNULL(sales,0) + IFNULL(ficti,0) as sales,unit_name,sort',$bastNumber,$this->uid);//TODO 精品推荐个数
        $info['firstList'] = StoreProduct::getNewProduct('id,image,store_name,cate_id,price,unit_name,sort',$firstNumber);//TODO 首发新品个数
        $info['bastBanner'] = GroupDataService::getData('routine_home_bast_banner')?:[];//TODO 首页精品推荐图片
        $benefit = StoreProduct::getBenefitProduct('id,image,store_name,cate_id,price,ot_price,stock,unit_name,sort',3);//TODO 首页促销单品
        $lovely =[];//TODO 首发新品顶部图
        $likeInfo = StoreProduct::getHotProduct('id,image,store_name,cate_id,price,unit_name,sort',3);//TODO 热门榜单 猜你喜欢
        $couponList=StoreCouponIssue::getIssueCouponList($this->uid,3);
        return $this->successful(compact('banner','menus','roll','info','activity','lovely','benefit','likeInfo','logoUrl','couponList'));
    }

    /**
     * 猜你喜欢  加载
     * @param Request $request
     */
    public function get_hot_product(){
        $data = UtilService::getMore([['offset',0],['limit',0]],$this->request);
        $hot = StoreProduct::getHotProductLoading('id,image,store_name,cate_id,price,unit_name,sort',$data['offset'],$data['limit']);//猜你喜欢
        return $this->successful($hot);
    }

    /*
     * 根据经纬度获取当前地理位置
     * */
    public function getlocation($latitude='',$longitude=''){
        $location=HttpService::getRequest('https://apis.map.qq.com/ws/geocoder/v1/',
            ['location'=>$latitude.','.$longitude,'key'=>'U65BZ-F2IHX-CGZ4I-73I7L-M6FZF-TEFCH']);
        $location=$location ? json_decode($location,true) : [];
        if($location && isset($location['result']['address'])){
            try{
                $address=$location['result']['address_component']['street'];
                return $this->successful(['address'=>$address]);
            }catch (\Exception $e){
                return $this->fail('获取位置信息失败!');
            }
        }else{
            return $this->fail('获取位置信息失败!');
        }
    }

    /*
     * 根据key来取系统的值
     * */
    public function get_system_config_value($name=''){
        if($name=='') return JsonService::fail('缺少参数');
        $name=str_replace(SystemConfigService::$ProtectedKey,'',$name);
        if(strstr($name,',')!==false){
            return $this->successful(SystemConfigService::more($name));
        }else{
            $value=SystemConfigService::get($name);
            $value=is_array($value) ? $value[0] : $value;
            return $this->successful([$name=>$value]);
        }
    }

    /*
     * 获取系统
     * */
    public function get_system_group_data_value($name='',$multi=0){
        if($name=='') return $this->successful([$name=>[]]);
        if($multi==1){
            $name=json_decode($name,true);
            $value=[];
            foreach ($name as $item){
                $value[$item]=GroupDataService::getData($item)?:[];
            }
            return $this->successful($value);
        }else{
            $value= GroupDataService::getData($name)?:[];
            return $this->successful([$name=>$value]);
        }
    }
    /*
     * 删除指定资源
     *
     * */
    public function delete_image(){
        $post=UtilService::postMore([
            ['pic',''],
        ]);
        if($post['pic']=='') return $this->fail('缺少删除资源');
        try{
            $attinfo = SystemAttachment::get($post['pic']);
            if($attinfo){
                if($attinfo['image_type'] == 1){
                    @unlink(ROOT_PATH.ltrim($attinfo['att_dir'],'.'));
                    @unlink(ROOT_PATH.ltrim($attinfo['satt_dir'],'.'));
                }else if($attinfo['image_type'] == 2){
                    Qiniu::delete($attinfo['name']);
                }else if($attinfo['image_type'] == 3){
                    OSS::delete($attinfo['name']);
                }else if($attinfo['image_type'] == 4){
                    COS::delete($attinfo['name']);
                }
                SystemAttachment::where(['att_id'=>$post['pic']])->delete();
            }
            return $this->successful('删除成功');
        }catch (\Exception $e){
            return $this->fail('刪除失败',['line'=>$e->getLine(),'message'=>$e->getMessage()]);
        }
    }

    /**
     * 上传图片
     * @param string $filename
     * @return \think\response\Json
     */
    public function upload($dir='')
    {
        $data = UtilService::postMore([
            ['filename',''],
        ],$this->request);
        if(Cache::has('start_uploads_'.$this->uid) && Cache::get('start_uploads_'.$this->uid) >= 100) return $this->fail('非法操作');
        $res = UploadService::image($data['filename'],$dir ? $dir: 'store/comment');
        if(!is_array($res)) return $this->fail(isset($res['error']) ? $res['error'] : '上传失败',$res);
        if(Cache::has('start_uploads_'.$this->uid))
            $start_uploads=(int)Cache::get('start_uploads_'.$this->uid);
        else
            $start_uploads=0;
        $start_uploads++;
        Cache::set('start_uploads_'.$this->uid,$start_uploads,86400);
        return $this->successful('图片上传成功!', ['name' => $res['name'], 'url' => UploadService::pathToUrl($res['dir'])]);
    }

    /**
     * 获取退款理由
     */
    public function get_refund_reason(){
        $reason = SystemConfigService::get('stor_reason')?:[];//退款理由
        $reason = str_replace("\r\n","\n",$reason);//防止不兼容
        $reason = explode("\n",$reason);
        return $this->successful($reason);
    }

    /**
     * 获取提现银行
     */
    public function get_user_extract_bank(){
        $extractBank = SystemConfigService::get('user_extract_bank')?:[];//提现银行
        $extractBank = str_replace("\r\n","\n",$extractBank);//防止不兼容
        $data['extractBank'] = explode("\n",is_array($extractBank)  ? ( isset($extractBank[0]) ? $extractBank[0]: $extractBank): $extractBank);
        $data['minPrice'] = SystemConfigService::get('user_extract_min_price');//提现最低金额
        return $this->successful($data);
    }

    /**
     * 收集发送模板信息的formID
     * @param string $formId
     */
    public function get_form_id($formId = ''){
        if($formId==''){
            list($formIds)=UtilService::postMore([
                ['formIds',[]]
            ],$this->request,true);
            foreach ($formIds as $formId){
                RoutineFormId::SetFormId($formId,$this->uid);
            }
        }else
            RoutineFormId::SetFormId($formId,$this->uid);
        return $this->successful('');
    }

    /**
     * 刷新数据缓存
     */
    public function refresh_cache(){
        `php think optimize:schema`;
        `php think optimize:autoload`;
        `php think optimize:route`;
        `php think optimize:config`;
    }

    /*
    * 清除系统全部缓存
    * @return
    * */
    public function clear_cache()
    {
        \think\Cache::clear();
    }

    /*
     * 获取会员等级
     * */
    public function get_level_list()
    {
        return JsonService::successful(SystemUserLevel::getLevelList($this->uid));
    }

    /*
     * 获取某个等级的任务
     * @param int $level_id 等级id
     * @return json
     * */
    public function get_task($level_id=''){
        return JsonService::successful(SystemUserTask::getTashList($level_id,$this->uid));
    }

    /*
     * 检测用户是否可以成为会员
     * */
    public function set_level_complete()
    {
        return JsonService::successful(UserLevel::setLevelComplete($this->uid));
    }

    /*
     * 记录用户分享次数
     * */
    public function set_user_share()
    {
        return JsonService::successful(UserBill::setUserShare($this->uid));
    }

}