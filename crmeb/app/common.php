<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


if (!function_exists('exception')) {
    /**
     * 抛出异常处理
     *
     * @param string $msg 异常消息
     * @param integer $code 异常代码 默认为0
     * @param string $exception 异常类
     *
     * @throws Exception
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

        $config = trim(app('sysConfig')->get($name));
        if ($config === '' || $config === false) {
            return $default;
        } else {
            return $config;
        }
    }
}

if (!function_exists('sys_data')) {
    /**
     * 获取系统单个配置
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
        $file = app()->getAppPath() . 'public/static/plug/censorwords/CensorWords';
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
            return '无法创建文件夹，请检查您的上传目录权限：' . app()->getRootPath() . 'public' . DS . 'uploads' . DS . 'attach' . DS;
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
        $domainTop = substr($image, 0, 4);
        if ($domainTop == 'http') return $image;
        $image = str_replace('\\', '/', $image);
        return $siteUrl . $image;
    }
}

if (!function_exists('set_http_type')) {
    /**
     * 修改 https 和 http
     * @param $url $url 域名
     * @param int $type 0 返回https 1 返回 http
     * @return string
     */
    function set_http_type($url, $type = 0)
    {
        $domainTop = substr($url, 0, 5);
        if ($type) {
            if ($domainTop == 'https') $url = 'http' . substr($url, 5, strlen($url));
        } else {
            if ($domainTop != 'https') $url = 'https:' . substr($url, 5, strlen($url));
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
if (!function_exists('anonymity')) {
    /**
     * 匿名处理处理用户昵称
     * @param $name
     * @return string
     */
    function anonymity($name)
    {
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
        try {
            $url = parse_url($avatar);
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

                $ext = pathinfo($url);
                if ($ext['extension'] != "jpg" && $ext['extension'] != "png" && $ext['extension'] != "jpeg") {
                    return false;
                }
                $filename = time() . "." . $ext['extension'];
            }

            //文件保存路径
            ob_start();
            readfile($url);
            $img = ob_get_contents();
            ob_end_clean();
            $path = 'uploads/qrcode';
            $fp2 = fopen($path . '/' . $filename, 'a');
            fwrite($fp2, $img);
            fclose($fp2);
            return $path . '/' . $filename;
        } catch (\Exception $e) {
            return false;
        }
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

if (!function_exists('is_brokerage_statu')) {

    /**
     * 是否能成为推广人
     * @param float $price
     * @return bool
     */
    function is_brokerage_statu(float $price)
    {
        $storeBrokerageStatus = sys_config('store_brokerage_statu', 1);
        if ($storeBrokerageStatus == 1) {
            return false;
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
