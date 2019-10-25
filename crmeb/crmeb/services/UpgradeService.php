<?php
namespace crmeb\services;

class UpgradeService extends FileService
{
    //请求域名
    public static $domain='http://shop.crmeb.net/';
    //及时更新网址信息
    public static $updatewebinfourl ='index.php/admin/server.upgrade_api/updatewebinfo.html';
    //公共接口地址 获取版本号
    public static $isNowVersionUrl='index.php/admin/server.upgrade_api/now_version.html';
    //公共接口地址 获取版本详情
    public static $isVersionInfo='index.php/admin/server.upgrade_api/version_info.html';
    //公共接口地址 获取历史版本列表
    public static $isList='index.php/admin/server.upgrade_api/get_version_list.html';
    //公共接口地址 写入更新版本信息
    public static $isInsertLog='index.php/admin/server.upgrade_api/set_upgrade_info.html';
    //公共接口地址 获取大于当前版本的所有版本
    public static $isNowVersion='index.php/admin/server.upgrade_api/get_now_version.html';
    //公共接口地址 更新网址信息
    protected static $UpdateWeBinfo='index.php/admin/server.upgrade_api/updatewebinfo.html';
    //公共接口地址 获取多少版本未更新
    public static $NewVersionCount='index.php/admin/server.upgrade_api/new_version_count.html';
    //公共接口地址 判断是否有权限 返回1有权限，0无权限
    protected static $Isauth='index.php/admin/server.upgrade_api/isauth.html';
    //相隔付
    private static $seperater = "{&&}";
    //更新网址信息
    public function snyweninfo($serverweb){
        return self::request_post(self::$UpdateWeBinfo,$serverweb);
    }
    //判断是否有权限 返回1有权限，0无权限
    public function isauth(){
        return self::request_post(self::$Isauth);
    }
    /*
     *获取ip token 生成当前时间和过期时间
     * @param string ip
     * @param int $valid_peroid 过期周期 15天
     */
    public static function get_token($ip='',$valid_peroid=1296000){
        $request=app('request');
        if(empty($ip)) $ip=$request->ip();
        $to_ken=$request->domain().self::$seperater.$ip.self::$seperater.time().self::$seperater.(time()+$valid_peroid).self::$seperater;
        $token=self::enCode($to_ken);
        return $token;
    }
    private static function getRet($msg,$code=400){
        return ['msg'=>$msg,'code'=>$code];
    }
    /**
     *
     * @param string $url
     * @param array $post_data
     */
    public static function start(){
        $pach = app()->getRootPath().'version';
        $request = app('request');
        if(!file_exists($pach)) return self::getRet($pach.'升级文件丢失，请联系管理员');
        $version=@file($pach);
        if(!isset($version[0])) return self::getRet('获取失败');
        $lv=self::request_post(self::$isNowVersionUrl,['token'=>self::get_token($request->ip())]);
        if(isset($lv['code']) && $lv['code']==200)
            $version_lv=isset($lv['data']['version']) && $lv['data']['version'] ? $lv['data']['version'] : false;
        else
            return isset($lv['msg'])?self::getRet($lv['msg']):self::getRet('获取失败');
        if($version_lv===false) return self::getRet('获取失败');
        if(strstr($version[0],'=')!==false){
            $version=explode('=',$version[0]);
            if($version[1]!=$version_lv){
                return self::getRet($version_lv,200);
            }
        }
        return self::getRet('获取失败');
    }
    public static function getVersion(){
        $pach = app()->getRootPath().'.version';
        if(!file_exists($pach)) return self::getRet($pach.'升级文件丢失，请联系管理员');
        $version=@file($pach);
        if(!isset($version[0]) && !isset($version[1])) return self::getRet('获取失败');
        $arr=[];
        foreach ($version as $val){
            list($k,$v)=explode('=',$val);
            $arr[$k]=$v;
        }
        return self::getRet($arr,200);
    }
    /**
     * 模拟post进行url请求
     * @param string $url
     * @param array $post_data
     */
    public static function request_post($url = '', $post_data = array()) {
        if(strstr($url,'http')===false)  $url=self::$domain.$url;
        if (empty($url)){
            return false;
        }
        if(!isset($post_data['token'])) $post_data['token']=self::get_token();
        $o = "";
        foreach ( $post_data as $k => $v )
        {
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $post_data = substr($o,0,-1);
        $postUrl = $url;
        $curlPost = $post_data;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, CURLOPT_TIMEOUT,20);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        if($data){
            $data=json_decode($data,true);
        }
        return $data;
    }
    /**
     * 验证远程文件是否存在 以及下载
     * @param string $url 文件路径
     * @param string $savefile 保存地址
     */
    public static function check_remote_file_exists($url,$savefile)
    {
        $url=self::$domain.'public'.DS.'uploads'.DS.'upgrade'.DS.$url;
        $url = str_replace('\\','/',$url);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        // 发送请求
        $result = curl_exec($curl);
        $found = false;
        // 如果请求没有发送失败
        if ($result !== false)
        {
            // 再检查http响应码是否为200
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode == 200)
            {
                curl_close($curl);

                $fileservice=new self;
                //下载文件
                $zip=$fileservice->down_remote_file($url,$savefile);
                if($zip['error']>0) return false;
                if(!isset($zip['save_path']) && empty($zip['save_path'])) return false;
                if(!file_exists($zip['save_path'])) return false;
                return $zip['save_path'];
            }
        }
        curl_close($curl);
        return $found;
    }
    /**
     * 通用加密
     * @param String $string 需要加密的字串
     * @param String $skey 加密EKY
     * @return String
     */
    private static function enCode($string = '', $skey = 'fb') {
        $skey = array_reverse(str_split($skey));
        $strArr = str_split(base64_encode($string));
        $strCount = count($strArr);
        foreach ($skey as $key => $value) {
            $key < $strCount && $strArr[$key].=$value;
        }
        return str_replace('=', 'O0O0O', join('', $strArr));
    }
    /**
     * 去除回车，去取空格，去除换行，去除tab
     * @param String $str 需要去除的字串
     * @return String
     */
    public static function replace($str){
        return trim(str_replace(array("\r", "\n", "\t"), '',$str));
    }
}