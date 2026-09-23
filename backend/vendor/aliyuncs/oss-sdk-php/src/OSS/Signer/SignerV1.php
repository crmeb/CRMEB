<?php

namespace OSS\Signer;

use OSS\Core\OssUtil;
use OSS\Http\RequestCore;
use OSS\Credentials\Credentials;

class SignerV1 implements SignerInterface
{
    public function sign(RequestCore $request, Credentials $credentials, array &$options)
    {
        // Date
        if (!isset($request->request_headers['Date'])) {
            $request->add_header('Date', gmdate('D, d M Y H:i:s \G\M\T'));
        }
        // Credentials information
        if (!empty($credentials->getSecurityToken())) {
            $request->add_header("x-oss-security-token", $credentials->getSecurityToken());
        }
        $headers = $request->request_headers;
        $method = strtoupper($request->method);
        $date = $headers['Date'];
        $resourcePath = $this->getResourcePath($options);
        $queryString = parse_url($request->request_url, PHP_URL_QUERY);
        $query = array();
        if ($queryString !== null) {
            parse_str($queryString, $query);
        }
        $stringToSign = $this->calcStringToSign($method, $date, $headers, $resourcePath, $query);
//        printf("sign str:%s" . PHP_EOL, $stringToSign);
        $options['string_to_sign'] = $stringToSign;
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $credentials->getAccessKeySecret(), true));
        $request->add_header('Authorization', 'OSS ' . $credentials->getAccessKeyId() . ':' . $signature);
    }

    public function presign(RequestCore $request, Credentials $credentials, array &$options)
    {
        $headers = $request->request_headers;
        // Date
        $expiration = $options['expiration'];
        if (!isset($request->request_headers['Date'])) {
            $request->add_header('Date', gmdate('D, d M Y H:i:s \G\M\T'));
        }
        $parsed_url = parse_url($request->request_url);
        $queryString = isset($parsed_url['query']) ? $parsed_url['query'] : '';
        $query = array();
        if ($queryString !== null) {
            parse_str($queryString, $query);
        }
        // Credentials information
        if (!empty($credentials->getSecurityToken())) {
            $query["security-token"] = $credentials->getSecurityToken();
        }
        $method = strtoupper($request->method);
        $date = $expiration . "";
        $resourcePath = $this->getResourcePath($options);
        $stringToSign = $this->calcStringToSign($method, $date, $headers, $resourcePath, $query);
        $options['string_to_sign'] = $stringToSign;
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $credentials->getAccessKeySecret(), true));
        $query['OSSAccessKeyId'] = $credentials->getAccessKeyId();
        $query['Expires'] = $date;
        $query['Signature'] = $signature;
        $queryString = OssUtil::toQueryString($query);
        $parsed_url['query'] = $queryString;
        $request->request_url = OssUtil::unparseUrl($parsed_url);
    }

    private function getResourcePath(array $options)
    {
        $resourcePath = '/';
        if (strlen($options['bucket']) > 0) {
            $resourcePath .= $options['bucket'] . '/';
        }
        if (strlen($options['key']) > 0) {
            $resourcePath .= $options['key'];
        }
        return $resourcePath;
    }

    private function calcStringToSign($method, $date, array $headers, $resourcePath, array $query)
    {
        /*
		SignToString =
			VERB + "\n"
			+ Content-MD5 + "\n"
			+ Content-Type + "\n"
			+ Date + "\n"
			+ CanonicalizedOSSHeaders
			+ CanonicalizedResource
		Signature = base64(hmac-sha1(AccessKeySecret, SignToString))
	    */
        $contentMd5 = '';
        $contentType = '';
        // CanonicalizedOSSHeaders
        $signheaders = array();
        foreach ($headers as $key => $value) {
            $lowk = strtolower($key);
            if (strncmp($lowk, "x-oss-", 6) == 0) {
                $signheaders[$lowk] = $value;
            } else if ($lowk === 'content-md5') {
                $contentMd5 = $value;
            } else if ($lowk === 'content-type') {
                $contentType = $value;
            }
        }
        ksort($signheaders);
        $canonicalizedOSSHeaders = '';
        foreach ($signheaders as $key => $value) {
            $canonicalizedOSSHeaders .= $key . ':' . $value . "\n";
        }
        // CanonicalizedResource
        $signquery = array();
        foreach ($query as $key => $value) {
            if (in_array($key, $this->signKeyList)) {
                $signquery[$key] = $value;
            }
        }
        ksort($signquery);
        $sortedQueryList = array();
        foreach ($signquery as $key => $value) {
            if (strlen($value) > 0) {
                $sortedQueryList[] = $key . '=' . $value;
            } else {
                $sortedQueryList[] = $key;
            }
        }
        $queryStringSorted = implode('&', $sortedQueryList);
        $canonicalizedResource = $resourcePath;
        if (!empty($queryStringSorted)) {
            $canonicalizedResource .= '?' . $queryStringSorted;
        }
        return $method . "\n" . $contentMd5 . "\n" . $contentType . "\n" . $date . "\n" . $canonicalizedOSSHeaders . $canonicalizedResource;
    }

    private $signKeyList = array(
        "acl", "uploads", "location", "cors",
        "logging", "website", "referer", "lifecycle",
        "delete", "append", "tagging", "objectMeta",
        "uploadId", "partNumber", "security-token", "x-oss-security-token",
        "position", "img", "style", "styleName",
        "replication", "replicationProgress",
        "replicationLocation", "cname", "bucketInfo",
        "comp", "qos", "live", "status", "vod",
        "startTime", "endTime", "symlink",
        "x-oss-process", "response-content-type", "x-oss-traffic-limit",
        "response-content-language", "response-expires",
        "response-cache-control", "response-content-disposition",
        "response-content-encoding", "udf", "udfName", "udfImage",
        "udfId", "udfImageDesc", "udfApplication",
        "udfApplicationLog", "restore", "callback", "callback-var", "qosInfo",
        "policy", "stat", "encryption", "versions", "versioning", "versionId", "requestPayment",
        "x-oss-request-payer", "sequential",
        "inventory", "inventoryId", "continuation-token", "asyncFetch",
        "worm", "wormId", "wormExtend", "withHashContext",
        "x-oss-enable-md5", "x-oss-enable-sha1", "x-oss-enable-sha256",
        "x-oss-hash-ctx", "x-oss-md5-ctx", "transferAcceleration",
        "regionList", "cloudboxes", "x-oss-ac-source-ip", "x-oss-ac-subnet-mask", "x-oss-ac-vpc-id", "x-oss-ac-forward-allow",
        "metaQuery", "resourceGroup", "rtc", "x-oss-async-process", "responseHeader"
    );
}