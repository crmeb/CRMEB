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

if (!function_exists('filterEmoji')) {

    // 过滤掉emoji表情
    function filterEmoji($str)
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


if (!function_exists('strReplace')) {
    /**
     * @param string $string 需要替换的字符串
     * @param int $start 开始的保留几位
     * @param int $end 最后保留几位
     * @return string
     */
    function strReplace($string, $start, $end)
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
     * $type 类型
     */
    function make_path($path,$type = 2)
    {
        $path =  DS.ltrim(rtrim($path));
        switch ($type){
            case 1:
                $path .= DS.date('Y');
                break;
            case 2:
                $path .=  DS.date('Y').DS.date('m');
                break;
            case 3:
                $path .=  DS.date('Y').DS.date('m').DS.date('d');
                break;
        }
        try{
            if (is_dir(app()->getRootPath().'public'.DS.'uploads'.$path) == true || mkdir(app()->getRootPath().'public'.DS.'uploads'.$path, 0777, true) == true) {
                return trim(str_replace(DS, '/',$path),'.');
            }else return '';
        }catch (\Exception $e){
            return '无法创建文件夹，请检查您的上传目录权限：' . app()->getRootPath() . 'public' . DS . 'uploads' . DS. 'attach' . DS;
        }

    }
}