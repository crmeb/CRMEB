<?php

namespace OSS\Tests;

use OSS\Core\OssException;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'StsBase.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'AssumeRole.php';

class StsClient
{

    public $AccessSecret;


    public function doAction($params, $format="JSON")
    {
        $request_url = $this->generateSignedURL($params);

        $response = $this->sendRequest($request_url, $format);

        $result= $this->parseResponse($response, $format);

        return $result;
    }

    private function sendRequest($url, $format)
    {
        $curl_handle = curl_init();

        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($curl_handle, CURLOPT_HEADER, true);

        $response = curl_exec($curl_handle);
        $headerSize = curl_getinfo($curl_handle, CURLINFO_HEADER_SIZE);
        $response = substr($response, $headerSize);

        if (curl_getinfo($curl_handle, CURLINFO_HTTP_CODE) != '200') {
            $errors = $this->parseResponse($response, $format);
            throw new OssException($errors->Code);
        }

        curl_close($curl_handle);

        return $response;
    }

    private function parseResponse($body, $format)
    {
        if ("JSON" == $format) {
            $respObject = json_decode($body);
        } elseif ("XML" == $format) {
            $respObject = @simplexml_load_string($body);
        } elseif ("RAW" == $format) {
            $respObject = $body;
        }
        return $respObject;
    }

    private function generateSignedURL($arr)
    {
        $request_url = 'https://sts.aliyuncs.com/?';

        foreach ($arr as $key=>$item) {
            if (is_null($item)) unset($arr[$key]);
        }

        $Signature = $this->computeSignature($arr, $this->AccessSecret);
        ksort($arr);
        foreach ($arr as $key => $value) {
            $request_url .=   $key."=".urlencode($value)."&";
        }
        $request_url .="Signature=".urlencode($Signature);

        return $request_url;
    }

    private function computeSignature($parameters, $accessKeySecret)
    {
        ksort($parameters);
        $canonicalizedQueryString = '';
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .= '&' . $this->percentEncode($key). '=' . $this->percentEncode($value);
        }
        $stringToSign = 'GET&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
        $signature = $this->signString($stringToSign, $accessKeySecret."&");

        return $signature;
    }

    private function signString($source, $accessSecret)
    {
        return    base64_encode(hash_hmac('sha1', $source, $accessSecret, true));
    }

    private function percentEncode($str)
    {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }
}
