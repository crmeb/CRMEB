<?php
/**
 * Created by CRMEB.
 * User: 136327134@qq.com
 * Date: 2019/4/12 11:20
 */

namespace app\core\behavior;

use app\core\model\ApiMenus;
use app\core\model\user\User;
use app\core\util\ApiLogs;
use app\core\util\ReturnCode;
use app\core\util\TokenService;
use service\JsonService;
use service\UtilService;
use think\Cache;
use think\Config;
use think\Request;
use app\core\implement\BehaviorIntterface;

class ApiAuthBehavior implements BehaviorIntterface
{
    //Request实例化对象
     protected $request=null;

    public function run(){
        $this->request=Request::instance();
        //开启路由
        $hash = $this->request->routeInfo();
        if(Config::get('url_route_on') && isset($hash['rule'][1])){
            $hash=$hash['rule'][1];
        }else{
           //未开启路由或者开启路由并没有使用路由
            $hash=UtilService::getCurrentController($this->request);
        }
        //检测访问接口是否存在，并读取接口详细信息
        if(Cache::has(ApiLogs::AB_API_INFO . $hash)){
            $apiInfo=Cache::get(ApiLogs::AB_API_INFO.$hash);
        }else {
            $apiInfo = ApiMenus::getHash(['hash' => $hash]);
            if (!$apiInfo) return JsonService::returnData(ReturnCode::DB_READ_ERROR, '获取接口配置数据失败!');
            Cache::set(ApiLogs::AB_API_INFO.$hash,$apiInfo,ApiLogs::EXPIRE);
        }
        //是否验证accessToken 是测试则不验证
        if ($apiInfo['access_token'] && !$apiInfo['is_test']) if($accessRes = $this->checkAccessToken()) return $accessRes;
        //是否为测试模式
        if (!$apiInfo['is_test']) if($versionRes = $this->checkVersion()) return $versionRes;
        //验证用户token信息
        $loginRes = $this->checkLogin($apiInfo['need_login']);
        if ($loginRes) return $loginRes;
    }

    /*
     * 验证access_token不存在则返回错误信息
     * @return array || boolean
     * */
    public function checkAccessToken()
    {
        if($this->request===null) $this->request=Request::instance();
        $access_token = $this->request->header(ApiLogs::ACCESS_TOKEN,'');
        if($access_token==='') return JsonService::returnData(ReturnCode::ACCESS_TOKEN_TIMEOUT, '缺少参数access-token!');
        if(!Cache::has(ApiLogs::ACCESS_TOKEN_PREFIX.$access_token)) return JsonService::returnData(ReturnCode::ACCESS_TOKEN_TIMEOUT,'access-token已失效！');
        //执行更多验证信息

        return false;
    }

    /*
     * 验证Api参数版本检测
     * @return array || boolean
     * */
    public function checkVersion()
    {
        if($this->request===null) $this->request=Request::instance();
        $version = $this->request->header(ApiLogs::API_VERSION,'');
        if($version==='') return JsonService::returnData(ReturnCode::EMPTY_PARAMS,'缺少API版本号！');
        if($version != Config::get('ebApi.API_VERSION')) return JsonService::returnData(ReturnCode::VERSION_INVALID,'API版本号与系统版本号不匹配');
        return false;
    }

    /*
     * 验证用户token信息
     *  @param number $needLogin 是否验证用户token
     * */
    public function checkLogin($needLogin)
    {
        if($this->request===null) $this->request=Request::instance();
        $userToken = $this->request->header(ApiLogs::USER_TOKEN, '');
        if(!$userToken && !$needLogin) return JsonService::returnData(ReturnCode::ERROR,'请传入token验证您的身份信息');
        //验证token
        $Tokencheck=TokenService::checkToken($userToken,$needLogin);
        if($Tokencheck===true){
            return ['uid'=>0];
        }else if(is_array($Tokencheck)){
            list($uid)=$Tokencheck;
            $userInfo = User::get($uid);
        }else return JsonService::returnData(ReturnCode::USER_TOKEN_ERROR,'没有获取到用户信息,请传入token验证您的身份信息');
        if((!$userInfo || !isset($userInfo)) && !$needLogin) return JsonService::returnData(ReturnCode::ERROR,'用户信息获取失败,没有这样的用户!');
        if(isset($userInfo->status) && !$userInfo->status) return JsonService::returnData(ReturnCode::USER_STATUS_ERROR,'您已被禁止登录');
    }

}