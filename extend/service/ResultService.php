<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/07
 */

class ResultService
{

    const SUCCESSFUL_CODE = 200;

    const FAILED_CODE = 400;

    protected static function getStd()
    {
        return new \StdClass();
    }

    /**
     * 成功结果
     * @param string $msg
     * @param array $data
     * @param string $defaultMsg
     * @return StdClass
     */
    public static function successful($msg = 'ok', $data = [], $defaultMsg = 'ok')
    {
        if(is_array($msg)){
            $data = $msg;
            $msg = $defaultMsg;
        }
        if(is_callable($data)) $data = $data();
        $result = self::getStd();
        $result->code = self::SUCCESSFUL_CODE;
        $result->meg = $msg;
        $result->data = $data;
        return $result;
    }


    /**
     * 失败结果
     * @param $msg
     * @param array $data
     * @return StdClass
     */
    public static function failed($msg, $data = [])
    {
        $result = self::getStd();
        if(is_callable($data)) $data = $data();
        $result->code = self::FAILED_CODE;
        $result->meg = $msg;
        $result->data = $data;
        return $result;
    }
}