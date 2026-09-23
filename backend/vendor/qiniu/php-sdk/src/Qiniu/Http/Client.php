<?php

namespace Qiniu\Http;

use Qiniu\Config;
use Qiniu\Http\Middleware;

final class Client
{
    /**
     * @param $url
     * @param array $headers
     * @param RequestOptions $opt
     * @return Response
     */
    public static function get($url, array $headers = array(), $opt = null)
    {
        $request = new Request('GET', $url, $headers, null, $opt);
        return self::sendRequestWithMiddleware($request);
    }

    /**
     * @param $url
     * @param array $headers
     * @param array $opt detail see {@see Request::$opt}
     * @return Response
     */
    public static function delete($url, array $headers = array(), $opt = null)
    {
        $request = new Request('DELETE', $url, $headers, null, $opt);
        return self::sendRequest($request);
    }

    /**
     * @param $url
     * @param $body
     * @param array $headers
     * @param RequestOptions $opt
     * @return Response
     */
    public static function post($url, $body, array $headers = array(), $opt = null)
    {
        $request = new Request('POST', $url, $headers, $body, $opt);
        return self::sendRequest($request);
    }

    /**
     * @param $url
     * @param $body
     * @param array $headers
     * @param RequestOptions $opt
     * @return Response
     */
    public static function PUT($url, $body, array $headers = array(), $opt = null)
    {
        $request = new Request('PUT', $url, $headers, $body, $opt);
        return self::sendRequest($request);
    }

    /**
     * @param $url
     * @param array $fields
     * @param string $name
     * @param string $fileName
     * @param $fileBody
     * @param null $mimeType
     * @param array $headers
     * @param RequestOptions $opt
     * @return Response
     */
    public static function multipartPost(
        $url,
        $fields,
        $name,
        $fileName,
        $fileBody,
        $mimeType = null,
        $headers = array(),
        $opt = null
    ) {
        $data = array();
        $mimeBoundary = md5(microtime());

        foreach ($fields as $key => $val) {
            array_push($data, '--' . $mimeBoundary);
            array_push($data, "Content-Disposition: form-data; name=\"$key\"");
            array_push($data, '');
            array_push($data, $val);
        }

        array_push($data, '--' . $mimeBoundary);
        $finalMimeType = empty($mimeType) ? 'application/octet-stream' : $mimeType;
        $finalFileName = self::escapeQuotes($fileName);
        array_push($data, "Content-Disposition: form-data; name=\"$name\"; filename=\"$finalFileName\"");
        array_push($data, "Content-Type: $finalMimeType");
        array_push($data, '');
        array_push($data, $fileBody);

        array_push($data, '--' . $mimeBoundary . '--');
        array_push($data, '');

        $body = implode("\r\n", $data);
        $contentType = 'multipart/form-data; boundary=' . $mimeBoundary;
        $headers['Content-Type'] = $contentType;
        $request = new Request('POST', $url, $headers, $body, $opt);
        return self::sendRequest($request);
    }

    private static function userAgent()
    {
        $sdkInfo = "QiniuPHP/" . Config::SDK_VER;

        $systemInfo = php_uname("s");
        $machineInfo = php_uname("m");

        $envInfo = "($systemInfo/$machineInfo)";

        $phpVer = phpversion();

        $ua = "$sdkInfo $envInfo PHP/$phpVer";
        return $ua;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public static function sendRequestWithMiddleware($request)
    {
        $middlewares = $request->opt->middlewares;
        $handle = Middleware\compose($middlewares, function ($req) {
            return Client::sendRequest($req);
        });
        return $handle($request);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public static function sendRequest($request)
    {
        $t1 = microtime(true);
        $ch = curl_init();
        $options = array(
            CURLOPT_USERAGENT => self::userAgent(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false,
            CURLOPT_CUSTOMREQUEST => $request->method,
            CURLOPT_URL => $request->url,
        );
        foreach ($request->opt->getCurlOpt() as $k => $v) {
            $options[$k] = $v;
        }
        // Handle open_basedir & safe mode
        if (!ini_get('safe_mode') && !ini_get('open_basedir')) {
            $options[CURLOPT_FOLLOWLOCATION] = true;
        }
        if (!empty($request->headers)) {
            $headers = array();
            foreach ($request->headers as $key => $val) {
                array_push($headers, "$key: $val");
            }
            $options[CURLOPT_HTTPHEADER] = $headers;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        if (!empty($request->body)) {
            $options[CURLOPT_POSTFIELDS] = $request->body;
        }
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $t2 = microtime(true);
        $duration = round($t2 - $t1, 3);
        $ret = curl_errno($ch);
        if ($ret !== 0) {
            $r = new Response(-1, $duration, array(), null, curl_error($ch));
            curl_close($ch);
            return $r;
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = Header::parseRawText(substr($result, 0, $header_size));
        $body = substr($result, $header_size);
        curl_close($ch);
        return new Response($code, $duration, $headers, $body, null);
    }

    private static function escapeQuotes($str)
    {
        if (is_null($str)) {
            return null;
        }
        $find = array("\\", "\"");
        $replace = array("\\\\", "\\\"");
        return str_replace($find, $replace, $str);
    }
}
