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

// 应用公共文件
use app\services\pay\PayServices;
use crmeb\services\CacheService;
use crmeb\services\HttpService;
use Fastknife\Service\ClickWordCaptchaService;
use think\exception\ValidateException;
use crmeb\services\FormBuilder as Form;
use app\services\other\UploadService;
use Fastknife\Service\BlockPuzzleCaptchaService;
use app\services\system\lang\LangTypeServices;
use app\services\system\lang\LangCodeServices;
use app\services\system\lang\LangCountryServices;
use think\facade\Config;
use think\facade\Log;
use think\facade\Db;

if (!function_exists('crmebLog')) {
    /**
     * CRMEB Log 日志
     * @param $msg
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/03
     */
    function crmebLog($msg)
    {
        Log::write($msg, 'crmeb');
    }
}

if (!function_exists('success')) {
    /**
     * 响应助手函数
     * @param mixed $msg 响应消息
     * @param array|null $data 响应数据
     * @param array|null $replace 消息替换数组
     * @return \think\Response
     * @see \crmeb\utils\Json::success()
     */
    function success($msg = 'success', ?array $data = null, ?array $replace = [])
    {
        return app('json')->success($msg, $data, $replace);
    }
}

if (!function_exists('fail')) {
    /**
     * 失败响应助手函数
     * @param mixed $msg 响应消息
     * @param array|null $data 响应数据
     * @param array|null $replace 消息替换数组
     * @return \think\Response
     * @see \crmeb\utils\Json::fail()
     */
    function fail($msg = 'fail', ?array $data = null, ?array $replace = [])
    {
        return app('json')->fail($msg, $data, $replace);
    }
}

if (!function_exists('getWorkerManUrl')) {

    /**
     * 获取客服数据
     * @return mixed
     */
    function getWorkerManUrl()
    {
        $ws = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'wss://' : 'ws://';
        $host = $_SERVER['HTTP_HOST'];
        $data['admin'] = $ws . $host . '/notice';
        $data['chat'] = $ws . $host . '/msg';
        return $data;
    }
}
if (!function_exists('object2array')) {

    /**
     * 对象转数组
     * @param $object
     * @return array|mixed
     */
    function object2array($object)
    {
        $array = [];
        if (is_object($object)) {
            foreach ($object as $key => $value) {
                $array[$key] = $value;
            }
        } else {
            $array = $object;
        }
        return $array;
    }
}

if (!function_exists('exception')) {
    /**
     * 抛出异常处理
     * @param $msg
     * @param int $code
     * @param string $exception
     * @throws \think\Exception
     */
    function exception($msg, $code = 0, $exception = '')
    {
        $e = $exception ?: '\think\Exception';
        throw new $e($msg, $code);
    }
}

if (!function_exists('sys_config')) {
    /**
     * 获取系统单个配置
     * @param string $name
     * @param string $default
     * @return string
     */
    function sys_config(string $name, $default = '')
    {
        if (empty($name))
            return $default;
        $sysConfig = app('sysConfig')->get($name);
        if (is_array($sysConfig)) {
            foreach ($sysConfig as &$item) {
                if (!is_array($item)) {
                    if (strpos($item, '/uploads/system/') !== false || strpos($item, '/statics/system_images/') !== false) $item = set_file_url($item);
                }
            }
        } else {
            if (strpos($sysConfig, '/uploads/system/') !== false || strpos($sysConfig, '/statics/system_images/') !== false) $sysConfig = set_file_url($sysConfig);
        }
        $config = is_array($sysConfig) ? $sysConfig : trim($sysConfig);
        if ($config === '' || $config === false) {
            return $default;
        } else {
            return $config;
        }
    }
}

if (!function_exists('sys_data')) {
    /**
     * 获取系统单个数据
     * @param string $name
     * @return string
     */
    function sys_data(string $name, int $limit = 0)
    {
        return app('sysGroupData')->getData($name, $limit);
    }
}

if (!function_exists('filter_emoji')) {

    // 过滤掉emoji表情
    function filter_emoji($str)
    {
        $str = preg_replace_callback(    //执行一个正则表达式搜索并且使用一个回调进行替换
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);
        return $str;
    }
}


if (!function_exists('str_middle_replace')) {
    /** TODO 系统未使用
     * @param string $string 需要替换的字符串
     * @param int $start 开始的保留几位
     * @param int $end 最后保留几位
     * @return string
     */
    function str_middle_replace($string, $start, $end)
    {
        $strlen = mb_strlen($string, 'UTF-8');//获取字符串长度
        $firstStr = mb_substr($string, 0, $start, 'UTF-8');//获取第一位
        $lastStr = mb_substr($string, -1, $end, 'UTF-8');//获取最后一位
        return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($string, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;

    }
}


if (!function_exists('sensitive_words_filter')) {

    /**
     * 敏感词过滤
     *
     * @param string
     * @return string
     */
    function sensitive_words_filter($str)
    {
        if (!$str) return '';
        $file = app()->getAppPath() . 'public/statics/plug/censorwords/CensorWords';
        $words = file($file);
        foreach ($words as $word) {
            $word = str_replace(array("\r\n", "\r", "\n", "/", "<", ">", "=", " "), '', $word);
            if (!$word) continue;

            $ret = preg_match("/$word/", $str, $match);
            if ($ret) {
                return $match[0];
            }
        }
        return '';
    }
}

if (!function_exists('make_path')) {

    /**
     * 上传路径转化,默认路径
     * @param $path
     * @param int $type
     * @param bool $force
     * @return string
     */
    function make_path($path, int $type = 2, bool $force = false)
    {
        $path = DS . ltrim(rtrim($path));
        switch ($type) {
            case 1:
                $path .= DS . date('Y');
                break;
            case 2:
                $path .= DS . date('Y') . DS . date('m');
                break;
            case 3:
                $path .= DS . date('Y') . DS . date('m') . DS . date('d');
                break;
        }
        try {
            if (is_dir(app()->getRootPath() . 'public' . DS . 'uploads' . $path) == true || mkdir(app()->getRootPath() . 'public' . DS . 'uploads' . $path, 0777, true) == true) {
                return trim(str_replace(DS, '/', $path), '.');
            } else return '';
        } catch (\Exception $e) {
            if ($force)
                throw new \Exception($e->getMessage());
//            return '无法创建文件夹，请检查您的上传目录权限：' . app()->getRootPath() . 'public' . DS . 'uploads' . DS . 'attach' . DS;
            return '';
        }

    }
}


if (!function_exists('curl_file_exist')) {
    /**
     * CURL 检测远程文件是否在
     * @param $url
     * @return bool
     */
    function curl_file_exist($url)
    {
        $ch = curl_init();
        try {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            $contents = curl_exec($ch);
            if (preg_match("/404/", $contents)) return false;
            if (preg_match("/403/", $contents)) return false;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
if (!function_exists('set_file_url')) {
    /**
     * 设置附加路径
     * @param $url
     * @return bool
     */
    function set_file_url($image, $siteUrl = '')
    {
        if (!strlen(trim($siteUrl))) $siteUrl = sys_config('site_url');
        if (!$image) return $image;
        if (is_array($image)) {
            foreach ($image as &$item) {
                $domainTop1 = substr($item, 0, 4);
                $domainTop2 = substr($item, 0, 2);
                if ($domainTop1 != 'http' && $domainTop2 != '//')
                    $item = $siteUrl . str_replace('\\', '/', $item);
            }
        } else {
            $domainTop1 = substr($image, 0, 4);
            $domainTop2 = substr($image, 0, 2);
            if ($domainTop1 != 'http' && $domainTop2 != '//')
                $image = $siteUrl . str_replace('\\', '/', $image);
        }
        return $image;
    }
}

if (!function_exists('set_http_type')) {
    /**
     * 修改 https 和 http
     * @param string $url 域名
     * @param int $type 0 返回https 1 返回 http
     * @return string
     */
    function set_http_type($url, $type = 0)
    {

        // 基本验证
        if (empty($url)) {
            return $url;
        }
        
        // 检查是否是完整 URL
        $is_full_url = (strpos($url, '://') !== false);
        
        if ($is_full_url) {
            // 处理完整 URL
            if ($type) {
                // 转换为 HTTP
                $url = preg_replace('/^https:/i', 'http:', $url);
            } else {
                // 转换为 HTTPS
                $url = preg_replace('/^http:/i', 'https:', $url);
            }
        }
        
        return $url;
    }

}

if (!function_exists('check_card')) {
    /**
     * 身份证验证
     * @param $card
     * @return bool
     */
    function check_card($card)
    {
        $city = [11 => "北京", 12 => "天津", 13 => "河北", 14 => "山西", 15 => "内蒙古", 21 => "辽宁", 22 => "吉林", 23 => "黑龙江 ", 31 => "上海", 32 => "江苏", 33 => "浙江", 34 => "安徽", 35 => "福建", 36 => "江西", 37 => "山东", 41 => "河南", 42 => "湖北 ", 43 => "湖南", 44 => "广东", 45 => "广西", 46 => "海南", 50 => "重庆", 51 => "四川", 52 => "贵州", 53 => "云南", 54 => "西藏 ", 61 => "陕西", 62 => "甘肃", 63 => "青海", 64 => "宁夏", 65 => "新疆", 71 => "台湾", 81 => "香港", 82 => "澳门", 91 => "国外 "];
        $tip = "";
        $match = "/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/";
        $pass = true;
        if (!$card || !preg_match($match, $card)) {
            //身份证格式错误
            $pass = false;
        } else if (!$city[substr($card, 0, 2)]) {
            //地址错误
            $pass = false;
        } else {
            //18位身份证需要验证最后一位校验位
            if (strlen($card) == 18) {
                $card = str_split($card);
                //∑(ai×Wi)(mod 11)
                //加权因子
                $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
                //校验位
                $parity = [1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2];
                $sum = 0;
                $ai = 0;
                $wi = 0;
                for ($i = 0; $i < 17; $i++) {
                    $ai = $card[$i];
                    $wi = $factor[$i];
                    $sum += $ai * $wi;
                }
                $last = $parity[$sum % 11];
                if ($parity[$sum % 11] != $card[17]) {
                    //                        $tip = "校验位错误";
                    $pass = false;
                }
            } else {
                $pass = false;
            }
        }
        if (!$pass) return false;/* 身份证格式错误*/
        return true;/* 身份证格式正确*/
    }
}
if (!function_exists('check_link')) {
    /**
     * 地址验证
     * @param string $link
     * @return false|int
     */
    function check_link(string $link)
    {
        return preg_match("/^(http|https|ftp):\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+[\/=\?%\-&_~`@[\]\’:+!]*([^<>\”])*$/", $link);
    }
}
if (!function_exists('check_phone')) {
    /**
     * 手机号验证
     * @param $phone
     * @return false|int
     */
    function check_phone($phone)
    {
        return preg_match("/^1[3456789]\d{9}$/", $phone);
    }
}
if (!function_exists('anonymity')) {
    /**
     * 匿名处理处理用户昵称
     * @param $name
     * @return string
     */
    function anonymity($name, $type = 1)
    {
        if ($type == 1) {
            return mb_substr($name, 0, 1, 'UTF-8') . '**' . mb_substr($name, -1, 1, 'UTF-8');
        } else {
            $strLen = mb_strlen($name, 'UTF-8');
            $min = 3;
            if ($strLen <= 1)
                return '*';
            if ($strLen <= $min)
                return mb_substr($name, 0, 1, 'UTF-8') . str_repeat('*', $min - 1);
            else
                return mb_substr($name, 0, 1, 'UTF-8') . str_repeat('*', $strLen - 1) . mb_substr($name, -1, 1, 'UTF-8');
        }
    }
}
if (!function_exists('sort_list_tier')) {
    /**
     * 分级排序
     * @param $data
     * @param int $pid
     * @param string $field
     * @param string $pk
     * @param string $html
     * @param int $level
     * @param bool $clear
     * @return array
     */
    function sort_list_tier($data, $pid = 0, $field = 'pid', $pk = 'id', $html = '|-----', $level = 1, $clear = true)
    {
        static $list = [];
        if ($clear) $list = [];
        foreach ($data as $k => $res) {
            if ($res[$field] == $pid) {
                $res['html'] = str_repeat($html, $level);
                $list[] = $res;
                unset($data[$k]);
                sort_list_tier($data, $res[$pk], $field, $pk, $html, $level + 1, false);
            }
        }
        return $list;
    }
}

if (!function_exists('sort_city_tier')) {
    /**
     * 城市数据整理
     * @param $data
     * @param int $pid
     * @param string $field
     * @param string $pk
     * @param string $html
     * @param int $level
     * @param bool $clear
     * @return array
     */
    function sort_city_tier($data, $pid = 0, $navList = [])
    {
        foreach ($data as $k => $menu) {
            if ($menu['parent_id'] == $pid) {
                unset($menu['parent_id']);
                unset($data[$k]);
                $menu['c'] = sort_city_tier($data, $menu['v']);
                $navList[] = $menu;
            }
        }
        return $navList;
    }
}

if (!function_exists('time_tran')) {
    /**
     * 时间戳人性化转化
     * @param $time
     * @return string
     */
    function time_tran($time)
    {
        $t = time() - $time;
        $f = array(
            '31536000' => '年',
            '2592000' => '个月',
            '604800' => '星期',
            '86400' => '天',
            '3600' => '小时',
            '60' => '分钟',
            '1' => '秒'
        );
        foreach ($f as $k => $v) {
            if (0 != $c = floor($t / (int)$k)) {
                return $c . $v . '前';
            }
        }
    }
}

if (!function_exists('url_to_path')) {
    /**
     * url转换路径
     * @param $url
     * @return string
     */
    function url_to_path($url)
    {
        $path = trim(str_replace('/', DS, $url), DS);
        if (0 !== strripos($path, 'public'))
            $path = 'public' . DS . $path;
        return app()->getRootPath() . $path;
    }
}

if (!function_exists('path_to_url')) {
    /**
     * 路径转url路径
     * @param $path
     * @return string
     */
    function path_to_url($path)
    {
        return trim(str_replace(DS, '/', $path), '.');
    }
}

if (!function_exists('image_to_base64')) {
    /**
     * 获取图片转为base64
     * @param string $avatar
     * @return bool|string
     */
    function image_to_base64($avatar = '', $timeout = 9)
    {
        $avatar = str_replace('https', 'http', $avatar);
        try {
            $url = parse_url($avatar);
            if ($url['scheme'] . '://' . $url['host'] == sys_config('site_url')) {
                $pattern = '/<\?php(.*?)\?>/s';
                $imgData = preg_replace($pattern, '', file_get_contents(public_path() . substr($url['path'], 1)));
                return "data:image/jpeg;base64," . base64_encode($imgData);
            }
            $url = $url['host'];
            $header = [
                'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
                'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
                'Accept-Encoding: gzip, deflate, br',
                'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'Host:' . $url
            ];
            $dir = pathinfo($url);
            $host = $dir['dirname'];
            $refer = $host . '/';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_REFERER, $refer);
            curl_setopt($curl, CURLOPT_URL, $avatar);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            $data = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($code == 200) {
                return "data:image/jpeg;base64," . base64_encode($data);
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('put_image')) {
    /**
     * 获取图片转为base64
     * @param string $avatar
     * @return bool|string
     */
    function put_image($url, $filename = '')
    {

        if ($url == '') {
            return false;
        }
        try {
            if ($filename == '') {
                $ext = pathinfo($url, PATHINFO_EXTENSION);
                if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    return false;
                }
                $filename = time() . "." . $ext;
            }

            // 保存文件到指定目录
            $imgData = file_get_contents($url);
            $pattern = '/<\?php(.*?)\?>/s';
            $imgData = preg_replace($pattern, '', $imgData);
            if ($imgData !== false) {
                $path = 'uploads' . DS . 'qrcode' . DS . $filename;
                if (file_put_contents($path, $imgData) !== false) {
                    return $path;
                }
            }
        } catch (\Exception $e) {
        }

        return false;
    }
}


if (!function_exists('debug_file')) {
    /**
     * 文件调试
     * @param $content
     */
    function debug_file($content, string $fileName = 'error', string $ext = 'txt')
    {
        $msg = '[' . date('Y-m-d H:i:s', time()) . '] [ DEBUG ] ';
        $pach = app()->getRuntimePath();
        file_put_contents($pach . $fileName . '.' . $ext, $msg . print_r($content, true) . "\r\n", FILE_APPEND);
    }
}


if (!function_exists('sql_filter')) {
    /**
     * sql 参数过滤
     * @param string $str
     * @return mixed
     */
    function sql_filter(string $str)
    {
        $filter = ['select ', 'insert ', 'update ', 'delete ', 'drop', 'truncate ', 'declare', 'xp_cmdshell', '/add', ' or ', 'exec', 'create', 'chr', 'mid', ' and ', 'execute'];
        $toupper = array_map(function ($str) {
            return strtoupper($str);
        }, $filter);
        return str_replace(array_merge($filter, $toupper, ['%20']), '', $str);
    }
}

if (!function_exists('filter_str')) {
    /**
     * 过滤字符串敏感字符
     * @param $str
     * @return array|mixed|string|string[]|null
     */
    function filter_str($str)
    {
        $param_filter_type = sys_config('param_filter_type');
        if ($param_filter_type != 0) {
            $rules = preg_split('/\r\n|\r|\n/', base64_decode(sys_config('param_filter_data')));
            if ($param_filter_type == 1) {
                foreach ($rules as $item) {
                    if (preg_match($item, $str)) {
                        throw new \Exception('接口请求失败：非法操作！');
                    }
                }
            }
            if (filter_var($str, FILTER_VALIDATE_URL)) {
                $url = parse_url($str);
                if (!isset($url['scheme'])) return $str;
                $host = $url['scheme'] . '://' . $url['host'];
                $str = $host . preg_replace($rules, '', str_replace($host, '', $str));
            } else {
                $str = preg_replace($rules, '', $str);
            }
        }
        return $str;
    }
}

if (!function_exists('is_brokerage_statu')) {

    /**
     * 是否能成为推广人
     * @param float $price
     * @return bool
     */
    function is_brokerage_statu(float $price)
    {
        if (!sys_config('brokerage_func_status')) {
            return false;
        }
        $storeBrokerageStatus = sys_config('store_brokerage_statu', 1);
        if ($storeBrokerageStatus == 1) {
            return false;
        } else if ($storeBrokerageStatus == 2) {
            return true;
        } else {
            $storeBrokeragePrice = sys_config('store_brokerage_price', 0);
            return $price >= $storeBrokeragePrice;
        }
    }
}

if (!function_exists('array_unique_fb')) {
    /**
     * 二维数组去掉重复值
     * @param $array
     * @return array
     */
    function array_unique_fb($array)
    {
        $out = array();
        foreach ($array as $key => $value) {
            if (!in_array($value, $out)) {
                $out[$key] = $value;
            }
        }
        $out = array_values($out);
        return $out;
    }
}


if (!function_exists('get_crmeb_version')) {
    /**
     * 获取CRMEB系统版本号
     * @param string $default
     * @return string
     */
    function get_crmeb_version($default = 'v1.0.0')
    {
        try {
            $version = parse_ini_file(app()->getRootPath() . '.version');
            return $version['version'] ?? $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (!function_exists('get_crmeb_version_vode')) {
    /**
     * 获取CRMEB系统版本号
     * @param string $default
     * @return string
     */
    function get_crmeb_version_vode($default = '0')
    {
        try {
            $version = parse_ini_file(app()->getRootPath() . '.version');
            return $version['version_code'] ?? $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (!function_exists('get_file_link')) {
    /**
     * 获取文件带域名的完整路径
     * @param string $link
     * @return string
     */
    function get_file_link(string $link)
    {
        if (!$link) {
            return '';
        }
        if (substr($link, 0, 4) === "http" || substr($link, 0, 2) === "//") {
            return $link;
        } else {
            return app()->request->domain() . $link;
        }
    }
}

if (!function_exists('tidy_tree')) {
    /**
     * 格式化分类
     * @param $menusList
     * @param int $pid
     * @param array $navList
     * @return array
     */
    function tidy_tree($menusList, $pid = 0, $navList = [])
    {
        foreach ($menusList as $k => $menu) {
            if ($menu['parent_id'] == $pid) {
                unset($menusList[$k]);
                $menu['children'] = tidy_tree($menusList, $menu['id']);
                if ($menu['children']) $menu['expand'] = true;
                $navList[] = $menu;
            }
        }
        return $navList;
    }
}

if (!function_exists('create_form')) {
    /**
     * 表单生成方法
     * @param string $title
     * @param array $field
     * @param $url
     * @param string $method
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    function create_form(string $title, array $field, $url, string $method = 'POST')
    {
        $form = Form::createForm((string)$url);//提交地址
        $form->setMethod($method);//提交方式
        $form->setRule($field);//表单字段
        $form->setTitle($title);//表单标题
        $rules = $form->formRule();
        $title = $form->getTitle();
        $action = $form->getAction();
        $method = $form->getMethod();
        $info = '';
        $status = true;
        $methodData = ['POST', 'PUT', 'GET', 'DELETE'];
        if (!in_array(strtoupper($method), $methodData)) {
            throw new ValidateException('请求方式有误');
        }
        return compact('rules', 'title', 'action', 'method', 'info', 'status');
    }
}

if (!function_exists('msectime')) {
    /**
     * 获取毫秒数
     * @return float
     */
    function msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }
}


if (!function_exists('array_bc_sum')) {
    /**
     * 获取一维数组的总合高精度
     * @param array $data
     * @return string
     */
    function array_bc_sum(array $data)
    {
        $sum = '0';
        foreach ($data as $item) {
            $sum = bcadd($sum, (string)$item, 2);
        }
        return $sum;
    }
}

if (!function_exists('get_tree_children')) {
    /**
     * tree 子菜单
     * @param array $data 数据
     * @param string $childrenname 子数据名
     * @param string $keyName 数据key名
     * @param string $pidName 数据上级key名
     * @return array
     */
    function get_tree_children(array $data, string $childrenname = 'children', string $keyName = 'id', string $pidName = 'pid')
    {
        $list = array();
        foreach ($data as $value) {
            $list[$value[$keyName]] = $value;
        }
        $tree = array(); //格式化好的树
        foreach ($list as $item) {
            if (isset($list[$item[$pidName]])) {
                $list[$item[$pidName]][$childrenname][] = &$list[$item[$keyName]];
            } else {
                $tree[] = &$list[$item[$keyName]];
            }
        }
        return $tree;
    }
}

if (!function_exists('get_tree_children_value')) {

    function get_tree_children_value(array $data, $value, string $childrenname = 'children', string $keyName = 'id')
    {
        static $childrenValue = [];
        foreach ($data as $item) {
            $childrenData = $item[$childrenname] ?? [];
            if (count($childrenData)) {
                return get_tree_children_value($childrenData, $childrenname, $keyName);
            } else {
                if ($item[$keyName] == $value) {
                    $childrenValue[] = $item['value'];
                }
            }
        }
        return $childrenValue;
    }
}


if (!function_exists('get_tree_value')) {
    /**
     * 获取
     * @param array $data
     * @param int|string $value
     * @return array
     */
    function get_tree_value(array $data, $value)
    {
//        static $childrenValue = [];
//        foreach ($data as &$item) {
//            if ($item['value'] == $value) {
//                $childrenValue[] = $item['value'];
//                if ($item['pid']) {
//                    $value = $item['pid'];
//                    unset($item);
//                    return get_tree_value($data, $value);
//                }
//            }
//        }
//        return $childrenValue;
        $childrenValue = []; // 用于存储找到的子值的数组
        foreach ($data as $item) {
            if ($item['value'] == $value) { // 如果当前项的'value'键与给定值匹配
                $childrenValue[] = $item['value']; // 将当前值添加到子值数组中
                if ($item['pid']) { // 如果当前项有'pid'值，表示有父项
                    // 递归调用get_tree_value函数，并将父项的'pid'值作为新的$value参数
                    $childrenValue = array_merge($childrenValue, get_tree_value($data, $item['pid']));
                }
            }
        }
        return $childrenValue; // 返回包含所有子值的数组
    }
}

if (!function_exists('get_image_thumb')) {
    /**
     * 获取缩略图
     * @param $filePath
     * @param string $type all|big|mid|small
     * @param bool $is_remote_down
     * @return mixed|string|string[]
     */
    function get_image_thumb($filePath, string $type = 'all', bool $is_remote_down = false)
    {
        if (!$filePath || !is_string($filePath) || strpos($filePath, '?') !== false) return $filePath;
        try {
            $upload = UploadService::getOssInit($filePath, $is_remote_down);
            //TODO
            $fileArr = explode('/', $filePath);
            $data = $upload->thumb($filePath, end($fileArr), $type);
            $image = $type == 'all' ? $data : $data[$type] ?? $filePath;
        } catch (\Throwable $e) {
            $image = $filePath;
        }
        $data = parse_url($image);
        if (!isset($data['host']) && (substr($image, 0, 2) == './' || substr($image, 0, 1) == '/')) {//不是完整地址
            $image = sys_config('site_url') . $image;
        }
        //请求是https 图片是http 需要改变图片地址
        //TODO 是否要读取后台配置url
        if (strpos(request()->domain(), 'https:') !== false && strpos($image, 'https:') === false) {
            $image = str_replace('http:', 'https:', $image);
        }
        return $image;
    }
}

if (!function_exists('get_thumb_water')) {
    /**
     * 处理数组获取缩略图、水印
     * @param $list
     * @param string $type
     * @param array|string[] $field 1、['image','images'] type 取值参数:type 2、['small'=>'image','mid'=>'images'] type 取field数组的key
     * @param bool $is_remote_down
     * @return array|mixed|string|string[]
     */
    function get_thumb_water($list, string $type = 'small', array $field = ['image'], bool $is_remote_down = false)
    {
        // 未开启缩略图功能 直接返回原数据
        if (!sys_config('image_thumb_status', 0)) {
            return $list;
        }
        if (!$list || !$field) return $list;
        $baseType = $type;
        $data = $list;
        if (is_string($list)) {
            $field = [$type => 'image'];
            $data = ['image' => $list];
        }
        if (is_array($data)) {
            foreach ($field as $type => $key) {
                if (is_integer($type)) {//索引数组，默认type
                    $type = $baseType;
                }
                //一维数组
                if (isset($data[$key])) {
                    if (is_array($data[$key])) {
                        $path_data = [];
                        foreach ($data[$key] as $k => $path) {
                            $path_data[] = get_image_thumb($path, $type, $is_remote_down);
                        }
                        $data[$key] = $path_data;
                    } else {
                        $data[$key] = get_image_thumb($data[$key], $type, $is_remote_down);
                    }
                } else {
                    foreach ($data as &$item) {
                        if (!isset($item[$key]))
                            continue;
                        if (is_array($item[$key])) {
                            $path_data = [];
                            foreach ($item[$key] as $k => $path) {
                                $path_data[] = get_image_thumb($path, $type, $is_remote_down);
                            }
                            $item[$key] = $path_data;
                        } else {
                            $item[$key] = get_image_thumb($item[$key], $type, $is_remote_down);
                        }
                    }
                }
            }
        }
        return is_string($list) ? ($data['image'] ?? '') : $data;
    }
}

if (!function_exists('getLang')) {
    /**
     * 多语言翻译函数：根据当前语言环境将传入的“中文语言标识”翻译成对应语言文本，并支持变量替换
     *
     * 执行流程：
     * 1. 异常捕获：整个逻辑包裹在 try-catch 中，任何环节出错直接返回原标识，避免系统因语言模块异常而中断
     * 2. 依赖注入：一次性获取三个核心服务实例
     *    - LangCountryServices：负责国家/地区与语言类型的映射
     *    - LangTypeServices：负责语言类型（如 zh-CN、en-US）的元数据
     *    - LangCodeServices：负责语言码表（code => 翻译文本）的读取
     * 3. 语言范围（range）判定优先级：
     *    ① 优先读取请求头 cb-lang（前端/接口主动指定）
     *    ② 若无，则读取系统默认语言（LangTypeServices.is_default = 1）
     *    ③ 若系统未配置默认语言，则读取浏览器 Accept-Language 首个语言标签
     *    ④ 若仍为空，则强制 fallback 到 zh-CN，确保后续逻辑有值可用
     * 4. 缓存加速：所有“一经写入、极少变动”的数据统一使用 CacheService::remember() 缓存 3600 秒，降低数据库压力
     *    - sys_lang_source_map：中文 remarks => code 的映射，用于把传入的“中文标识”转成内部 code
     *    - type_id_{range}：根据语言简码（如 zh-CN）反查对应的 type_id
     *    - lang_type_data：所有启用的语言类型 id => file_name 映射表，用于校验语言是否合法
     *    - lang_{file_name}：具体语言包 code => 翻译文本 的完整数组
     * 5. 翻译过程：
     *    - 若语言类型不存在，直接返回原标识
     *    - 若“中文标识”在映射表中存在且对应 code 在语言包中存在，则取翻译文本；否则返回原标识
     * 6. 变量替换：支持 {:变量名} 语法，将翻译文本中的占位符批量替换为 $replace 数组中的值
     * 7. 异常兜底：catch 中记录详细错误日志（文件名/行号/异常信息），依旧返回原标识，保证业务继续
     *
     * @param string $msg   中文语言标识（remarks），如 "用户名不能为空"
     * @param array  $replace 可选的变量映射，如 ['name' => '手机号']，会将文本中的 {:name} 替换为“手机号”
     * @return string       最终翻译后的文本；任何异常或找不到翻译时返回原标识
     */
    function getLang($msg, array $replace = [])
    {
        /* 整个翻译过程一旦出错，直接返回原标识，避免中断业务 */
        try {
            /* --------------- 1. 依赖注入：获取语言相关服务 --------------- */
            /** @var LangCountryServices $langCountryServices */
            $langCountryServices = app()->make(LangCountryServices::class);
            /** @var LangTypeServices $langTypeServices */
            $langTypeServices = app()->make(LangTypeServices::class);
            /** @var LangCodeServices $langCodeServices */
            $langCodeServices = app()->make(LangCodeServices::class);

            /* --------------- 2. 确定当前语言范围（range） --------------- */
            $request = app()->request;
            // 优先取前端/接口指定的语言
            $range = $request->header('cb-lang');
            if (!$range) {
                // 未指定时，读取系统默认语言
                $range = CacheService::remember('range_name', function () use ($langTypeServices) {
                    return $langTypeServices->value(['is_default' => 1], 'file_name');
                });
                if (!$range) {
                    // 系统也未配置默认语言，则尝试使用浏览器 Accept-Language
                    if ($request->header('accept-language') !== null) {
                        $range = explode(',', $request->header('accept-language'))[0];
                    } else {
                        // 最终兜底：简体中文
                        $range = 'zh-CN';
                    }
                }
            }

            /* --------------- 3. 读取各类映射数据（带缓存） --------------- */
            // 中文 remarks => code 映射表，用于把传入的“中文标识”转成内部 code
            $langZhCn = CacheService::remember('sys_lang_source_map', function () use ($langCodeServices) {
                return $langCodeServices->getColumn(['type_id' => 1], 'code', 'remarks');
            }, 3600);

            // 根据语言简码（如 zh-CN）反查对应的 type_id
            $typeId = CacheService::remember('type_id_' . $range, function () use ($langCountryServices, $range) {
                return $langCountryServices->value(['code' => $range], 'type_id') ?: 1;
            }, 3600);

            // 所有启用的语言类型 id => file_name 映射表
            $langData = CacheService::remember('lang_type_data', function () use ($langTypeServices) {
                return $langTypeServices->getColumn(['status' => 1, 'is_del' => 0], 'file_name', 'id');
            }, 3600);

            /* --------------- 4. 校验语言类型是否合法 --------------- */
            if (!isset($langData[$typeId])) {
                return $msg;
            }

            /* --------------- 5. 读取当前语言包（code => 翻译文本） --------------- */
            $langStr = 'lang_' . str_replace('-', '_', $langData[$typeId]); // 构造缓存 key
            $lang = CacheService::remember($langStr, function () use ($typeId, $langCodeServices) {
                return $langCodeServices->getColumn(['type_id' => $typeId], 'lang_explain', 'code');
            }, 3600);

            /* --------------- 6. 获取翻译文本 --------------- */
            if (isset($langZhCn[$msg]) && isset($lang[$langZhCn[$msg]])) {
                // 映射表存在且语言包中存在对应 code，则使用翻译文本
                $message = (string)$lang[$langZhCn[$msg]];
            } else {
                // 找不到翻译，返回原标识
                $message = $msg;
            }

            /* --------------- 7. 变量替换（支持 {:变量名} 语法） --------------- */
            if (!empty($replace) && is_array($replace)) {
                // 构造占位符数组，如 ['name'] -> ['{:name}']
                $key = array_map(function ($v) { return "{:{$v}}"; }, array_keys($replace));
                // 批量替换
                $message = str_replace($key, array_values($replace), $message);
            }

            return $message;
        } catch (\Throwable $e) {
            /* 记录详细错误日志，依旧返回原标识，保证业务继续 */
            Log::error('获取语言msg：' . $msg . '发生错误，错误原因是：' . json_encode([
                    'file'  => $e->getFile(),
                    'message' => $e->getMessage(),
                    'line'  => $e->getLine()
                ]));
            return $msg;
        }
    }
}

if (!function_exists('aj_captcha_check_one')) {
    /**
     * 验证滑块1次验证
     * @param string $token
     * @param string $pointJson
     * @return bool
     */
    function aj_captcha_check_one(string $captchaType, string $token, string $pointJson)
    {
        aj_get_serevice($captchaType)->check($token, $pointJson);
        return true;
    }
}

if (!function_exists('aj_captcha_check_two')) {
    /**
     * 验证滑块2次验证
     * @param string $token
     * @param string $pointJson
     * @return bool
     */
    function aj_captcha_check_two(string $captchaType, string $captchaVerification)
    {
        aj_get_serevice($captchaType)->verificationByEncryptCode($captchaVerification);
        return true;
    }
}


if (!function_exists('aj_captcha_create')) {
    /**
     * 创建验证码
     * @return array
     */
    function aj_captcha_create(string $captchaType)
    {
        return aj_get_serevice($captchaType)->get();
    }
}

if (!function_exists('aj_get_serevice')) {

    /**
     * @param string $captchaType
     * @return ClickWordCaptchaService|BlockPuzzleCaptchaService
     */
    function aj_get_serevice(string $captchaType)
    {
        $config = Config::get('ajcaptcha');
        switch ($captchaType) {
            case "clickWord":
                $service = new ClickWordCaptchaService($config);
                break;
            case "blockPuzzle":
                $service = new BlockPuzzleCaptchaService($config);
                break;
            default:
                throw new ValidateException('captchaType参数不正确！');
        }
        return $service;
    }
}

if (!function_exists('out_push')) {
    /**
     * 默认数据推送
     * @param string $pushUrl
     * @param array $data
     * @param string $tip
     * @return bool
     */
    function out_push(string $pushUrl, array $data, string $tip = ''): bool
    {
        $param = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = HttpService::postRequest($pushUrl, $param, ['Content-Type:application/json', 'Content-Length:' . strlen($param)]);
        $res = $res ? json_decode($res, true) : [];
        if (!$res || !isset($res['code']) || $res['code'] != 0) {
            \think\facade\Log::error(['msg' => $tip . '推送失败', 'data' => $res]);
            return false;
        }
        return true;
    }
}

if (!function_exists('dump_sql')) {
    /**
     * 打印sql
     * @param string $pushUrl
     * @param array $data
     * @param string $tip
     * @return bool
     */
    function dump_sql()
    {
        Db::listen(function ($sql) {
            var_dump($sql);
        });
    }
}

if (!function_exists('toIntArray')) {

    /**
     * 处理ids等并过滤参数
     * @param $data
     * @param string $separator
     * @return array
     */
    function toIntArray($data, string $separator = ',')
    {
        if (!is_string($data) && !is_int($data)) {
            return array_unique(array_diff(array_map('intval', $data), [0]));
        } else {
            return !empty($data) ? array_unique(array_diff(array_map('intval', explode($separator, $data)), [0])) : [];
        }
    }
}
