<?php
/**
 * PHP表单生成器
 *
 * @package  FormBuilder
 * @author   xaboy <xaboy2005@qq.com>
 * @version  2.0
 * @license  MIT
 * @link     https://github.com/xaboy/form-builder
 * @document http://php.form-create.com
 */

namespace FormBuilder;

use \Symfony\Component\HttpFoundation\Response as HttpResponse;

abstract class Response
{
    /**
     * @param int $code
     * @param string $msg
     * @param null|array $data
     * @return HttpResponse
     */
    protected static function createResponse($code, $msg = 'ok', $data = null)
    {
        $res = compact('code', 'msg');
        if (!is_null($data)) $res['data'] = $data;

        return new HttpResponse(json_encode($res));
    }

    /**
     * 请求成功
     *
     * @param string $msg
     * @param null|array $data
     * @return HttpResponse
     */
    public static function succ($msg = 'ok', $data = null)
    {
        return self::createResponse(200, $msg, $data);
    }

    /**
     * 请求失败
     *
     * @param string $msg
     * @param null|array $data
     * @return HttpResponse
     */
    public static function fail($msg = 'fail', $data = null)
    {
        return self::createResponse(400, $msg, $data);
    }

    /**
     * 请求成功
     *
     * @param string $msg
     * @param null|array $data
     * @return HttpResponse
     */
    public static function success($msg = 'ok', $data = null)
    {
        return self::succ($msg, $data);
    }

    /**
     * 图片/文件上传成功
     *
     * @param string $filePath
     * @param string $msg
     * @param array $data
     * @return HttpResponse
     */
    public static function uploadSucc($filePath, $msg = '上传成功', array $data = [])
    {
        $data['filePath'] = $filePath;
        return self::succ($msg, $data);
    }

    /**
     * 图片/文件上传失败
     *
     * @param string $msg
     * @param null|array $data
     * @return HttpResponse
     */
    public static function uploadFail($msg = '上传失败', $data = null)
    {
        return self::fail($msg, $data);
    }

    /**
     * 图片/文件上传成功
     *
     * @param string $filePath
     * @param string $msg
     * @param array $data
     * @return HttpResponse
     */
    public static function uploadSuccess($filePath, $msg = '上传成功', array $data = [])
    {
        return self::uploadSucc($filePath, $msg, $data);
    }
}