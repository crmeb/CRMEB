<?php

namespace OSS\Tests;

use OSS\Http\RequestCore;
use OSS\Credentials\Credentials;
use OSS\Signer\SignerV1;
use OSS\Signer\SignerV4;
use OSS\Core\OssUtil;

class SignerTest extends \PHPUnit\Framework\TestCase
{
    public function testSignerV1Header()
    {
        // case 1
        $credentials = new Credentials("ak", "sk");
        $request = new RequestCore("http://examplebucket.oss-cn-hangzhou.aliyuncs.com");
        $request->set_method("PUT");
        $bucket = "examplebucket";
        $object = "nelson";

        $request->add_header("Content-MD5", "eB5eJF1ptWaXm4bijSPyxw==");
        $request->add_header("Content-Type", "text/html");
        $request->add_header("x-oss-meta-author", "alice");
        $request->add_header("x-oss-meta-magic", "abracadabra");
        $request->add_header("x-oss-date", "Wed, 28 Dec 2022 10:27:41 GMT");

        $request->add_header("Date", "Wed, 28 Dec 2022 10:27:41 GMT");

        $signer = new SignerV1();

        $signingOpt = array(
            'bucket' => $bucket,
            'key' => $object,
        );
        $signer->sign($request, $credentials, $signingOpt);

        $signToString = "PUT\neB5eJF1ptWaXm4bijSPyxw==\ntext/html\nWed, 28 Dec 2022 10:27:41 GMT\nx-oss-date:Wed, 28 Dec 2022 10:27:41 GMT\nx-oss-meta-author:alice\nx-oss-meta-magic:abracadabra\n/examplebucket/nelson";

        $this->assertEquals($signToString, $signingOpt['string_to_sign']);
        $this->assertEquals('OSS ak:kSHKmLxlyEAKtZPkJhG9bZb5k7M=', $request->request_headers['Authorization']);

        // case 2
        $request2 = new RequestCore("http://examplebucket.oss-cn-hangzhou.aliyuncs.com?acl");
        $request2->set_method("PUT");

        $request2->add_header("Content-MD5", "eB5eJF1ptWaXm4bijSPyxw==");
        $request2->add_header("Content-Type", "text/html");
        $request2->add_header("x-oss-meta-author", "alice");
        $request2->add_header("x-oss-meta-magic", "abracadabra");
        $request2->add_header("x-oss-date", "Wed, 28 Dec 2022 10:27:41 GMT");

        $request2->add_header("Date", "Wed, 28 Dec 2022 10:27:41 GMT");

        $signer = new SignerV1();

        $signingOpt2 = array(
            'bucket' => $bucket,
            'key' => $object,
        );
        $signer->sign($request2, $credentials, $signingOpt2);

        $signToString = "PUT\neB5eJF1ptWaXm4bijSPyxw==\ntext/html\nWed, 28 Dec 2022 10:27:41 GMT\nx-oss-date:Wed, 28 Dec 2022 10:27:41 GMT\nx-oss-meta-author:alice\nx-oss-meta-magic:abracadabra\n/examplebucket/nelson?acl";

        $this->assertEquals($signToString, $signingOpt2['string_to_sign']);
        $this->assertEquals('OSS ak:/afkugFbmWDQ967j1vr6zygBLQk=', $request2->request_headers['Authorization']);

        // case 3 with non-signed query
        $request3 = new RequestCore("http://examplebucket.oss-cn-hangzhou.aliyuncs.com?acl&non-signed-key=value");
        $request3->set_method("PUT");

        $request3->add_header("Content-MD5", "eB5eJF1ptWaXm4bijSPyxw==");
        $request3->add_header("Content-Type", "text/html");
        $request3->add_header("x-oss-meta-author", "alice");
        $request3->add_header("x-oss-meta-magic", "abracadabra");
        $request3->add_header("x-oss-date", "Wed, 28 Dec 2022 10:27:41 GMT");

        $request3->add_header("Date", "Wed, 28 Dec 2022 10:27:41 GMT");

        $signingOpt3 = array(
            'bucket' => $bucket,
            'key' => $object,
        );
        $signer->sign($request3, $credentials, $signingOpt3);

        $signToString = "PUT\neB5eJF1ptWaXm4bijSPyxw==\ntext/html\nWed, 28 Dec 2022 10:27:41 GMT\nx-oss-date:Wed, 28 Dec 2022 10:27:41 GMT\nx-oss-meta-author:alice\nx-oss-meta-magic:abracadabra\n/examplebucket/nelson?acl";

        $this->assertEquals($signToString, $signingOpt3['string_to_sign']);
        $this->assertEquals('OSS ak:/afkugFbmWDQ967j1vr6zygBLQk=', $request3->request_headers['Authorization']);
    }

    public function testSignerV1HeaderWithToken()
    {
        // case 1
        $credentials = new Credentials("ak", "sk", "token");
        $request = new RequestCore("http://examplebucket.oss-cn-hangzhou.aliyuncs.com");
        $request->set_method("PUT");
        $bucket = "examplebucket";
        $object = "nelson";

        $request->add_header("Content-MD5", "eB5eJF1ptWaXm4bijSPyxw==");
        $request->add_header("Content-Type", "text/html");
        $request->add_header("x-oss-meta-author", "alice");
        $request->add_header("x-oss-meta-magic", "abracadabra");
        $request->add_header("x-oss-date", "Wed, 28 Dec 2022 10:27:41 GMT");

        $request->add_header("Date", "Wed, 28 Dec 2022 10:27:41 GMT");

        $signer = new SignerV1();

        $signingOpt = array(
            'bucket' => $bucket,
            'key' => $object,
        );
        $signer->sign($request, $credentials, $signingOpt);

        $signToString = "PUT\neB5eJF1ptWaXm4bijSPyxw==\ntext/html\nWed, 28 Dec 2022 10:27:41 GMT\nx-oss-date:Wed, 28 Dec 2022 10:27:41 GMT\nx-oss-meta-author:alice\nx-oss-meta-magic:abracadabra\nx-oss-security-token:token\n/examplebucket/nelson";

        $this->assertEquals($signToString, $signingOpt['string_to_sign']);
        $this->assertEquals('OSS ak:H3PAlN3Vucn74tPVEqaQC4AnLwQ=', $request->request_headers['Authorization']);
        $this->assertEquals('token', $request->request_headers['x-oss-security-token']);

        // case 2
        $request2 = new RequestCore("http://examplebucket.oss-cn-hangzhou.aliyuncs.com?acl");
        $request2->set_method("PUT");

        $request2->add_header("Content-MD5", "eB5eJF1ptWaXm4bijSPyxw==");
        $request2->add_header("Content-Type", "text/html");
        $request2->add_header("x-oss-meta-author", "alice");
        $request2->add_header("x-oss-meta-magic", "abracadabra");
        $request2->add_header("x-oss-date", "Wed, 28 Dec 2022 10:27:41 GMT");

        $request2->add_header("Date", "Wed, 28 Dec 2022 10:27:41 GMT");

        $signer = new SignerV1();

        $signingOpt2 = array(
            'bucket' => $bucket,
            'key' => $object,
        );
        $signer->sign($request2, $credentials, $signingOpt2);

        $signToString = "PUT\neB5eJF1ptWaXm4bijSPyxw==\ntext/html\nWed, 28 Dec 2022 10:27:41 GMT\nx-oss-date:Wed, 28 Dec 2022 10:27:41 GMT\nx-oss-meta-author:alice\nx-oss-meta-magic:abracadabra\nx-oss-security-token:token\n/examplebucket/nelson?acl";

        $this->assertEquals($signToString, $signingOpt2['string_to_sign']);
        $this->assertEquals("OSS ak:yeceHMAsgusDPCR979RJcLtd7RI=", $request2->request_headers['Authorization']);

        // case 3 with non-signed query
        $request3 = new RequestCore("http://examplebucket.oss-cn-hangzhou.aliyuncs.com?acl&non-signed-key=value");
        $request3->set_method("PUT");

        $request3->add_header("Content-MD5", "eB5eJF1ptWaXm4bijSPyxw==");
        $request3->add_header("Content-Type", "text/html");
        $request3->add_header("x-oss-meta-author", "alice");
        $request3->add_header("x-oss-meta-magic", "abracadabra");
        $request3->add_header("x-oss-date", "Wed, 28 Dec 2022 10:27:41 GMT");

        $request3->add_header("Date", "Wed, 28 Dec 2022 10:27:41 GMT");

        $signingOpt3 = array(
            'bucket' => $bucket,
            'key' => $object,
        );
        $signer->sign($request3, $credentials, $signingOpt3);

        $signToString = "PUT\neB5eJF1ptWaXm4bijSPyxw==\ntext/html\nWed, 28 Dec 2022 10:27:41 GMT\nx-oss-date:Wed, 28 Dec 2022 10:27:41 GMT\nx-oss-meta-author:alice\nx-oss-meta-magic:abracadabra\nx-oss-security-token:token\n/examplebucket/nelson?acl";

        $this->assertEquals($signToString, $signingOpt3['string_to_sign']);
        $this->assertEquals('OSS ak:yeceHMAsgusDPCR979RJcLtd7RI=', $request2->request_headers['Authorization']);
    }

    public function testSignerV1Presign()
    {
        $credentials = new Credentials("ak", "sk");
        $request = new RequestCore("http://bucket.oss-cn-hangzhou.aliyuncs.com/key?versionId=versionId");
        $request->set_method("GET");
        $bucket = "bucket";
        $object = "key";

        $signer = new SignerV1();

        $signingOpt = array(
            'bucket' => $bucket,
            'key' => $object,
            'expiration' => 1699807420,
        );
        $signer->presign($request, $credentials, $signingOpt);

        $parsed_url = parse_url($request->request_url);
        $queryString = isset($parsed_url['query']) ? $parsed_url['query'] : '';
        $query = array();
        parse_str($queryString, $query);

        $this->assertEquals('1699807420', $query['Expires']);
        $this->assertEquals('ak', $query['OSSAccessKeyId']);
        $this->assertEquals('dcLTea+Yh9ApirQ8o8dOPqtvJXQ=', $query['Signature']);
        $this->assertEquals('versionId', $query['versionId']);
        $this->assertEquals('/key', $parsed_url['path']);
    }

    public function testSignerV1PresignWithToken()
    {
        $credentials = new Credentials("ak", "sk", "token");
        $request = new RequestCore("http://bucket.oss-cn-hangzhou.aliyuncs.com/key%2B123?versionId=versionId");
        $request->set_method("GET");
        $bucket = "bucket";
        $object = "key+123";

        $signer = new SignerV1();

        $signingOpt = array(
            'bucket' => $bucket,
            'key' => $object,
            'expiration' => 1699808204,
        );
        $signer->presign($request, $credentials, $signingOpt);

        $parsed_url = parse_url($request->request_url);
        $queryString = isset($parsed_url['query']) ? $parsed_url['query'] : '';
        $query = array();
        parse_str($queryString, $query);

        $this->assertEquals('1699808204', $query['Expires']);
        $this->assertEquals('ak', $query['OSSAccessKeyId']);
        $this->assertEquals('jzKYRrM5y6Br0dRFPaTGOsbrDhY=', $query['Signature']);
        $this->assertEquals('versionId', $query['versionId']);
        $this->assertEquals('token', $query['security-token']);
        $this->assertEquals('/key%2B123', $parsed_url['path']);
    }

    public function testSignerV4Header()
    {
        // case 1
        $credentials = new Credentials("ak", "sk");
        $request = new RequestCore("http://bucket.oss-cn-hangzhou.aliyuncs.com/1234%2B-/123/1.txt");
        $request->set_method("PUT");
        $bucket = "bucket";
        $object = "1234+-/123/1.txt";

        $request->add_header("x-oss-head1", "value");
        $request->add_header("abc", "value");
        $request->add_header("ZAbc", "value");
        $request->add_header("XYZ", "value");
        $request->add_header("content-type", "text/plain");
        $request->add_header("x-oss-content-sha256", "UNSIGNED-PAYLOAD");

        $request->add_header("Date", gmdate('D, d M Y H:i:s \G\M\T', 1702743657));

        $signer = new SignerV4();

        $query = array();
        $query["param1"] = "value1";
        $query["+param1"] = "value3";
        $query["|param1"] = "value4";
        $query["+param2"] = "";
        $query["|param2"] = "";
        $query["param2"] = "";

        $parsed_url = parse_url($request->request_url);
        $parsed_url['query'] = OssUtil::toQueryString($query);;
        $request->request_url = OssUtil::unparseUrl($parsed_url);

        $signingOpt = array(
            'bucket' => $bucket,
            'key' => $object,
            'region' => 'cn-hangzhou',
            'product' => 'oss',
        );
        $signer->sign($request, $credentials, $signingOpt);

        $authPat = "OSS4-HMAC-SHA256 Credential=ak/20231216/cn-hangzhou/oss/aliyun_v4_request,Signature=e21d18daa82167720f9b1047ae7e7f1ce7cb77a31e8203a7d5f4624fa0284afe";
        $this->assertEquals($authPat, $request->request_headers['Authorization']);
    }

    public function testSignerV4HeaderWithToken()
    {
        // case 1
        $credentials = new Credentials("ak", "sk", "token");
        $request = new RequestCore("http://bucket.oss-cn-hangzhou.aliyuncs.com/1234%2B-/123/1.txt");
        $request->set_method("PUT");
        $bucket = "bucket";
        $object = "1234+-/123/1.txt";

        $request->add_header("x-oss-head1", "value");
        $request->add_header("abc", "value");
        $request->add_header("ZAbc", "value");
        $request->add_header("XYZ", "value");
        $request->add_header("content-type", "text/plain");
        $request->add_header("x-oss-content-sha256", "UNSIGNED-PAYLOAD");

        $request->add_header("Date", gmdate('D, d M Y H:i:s \G\M\T', 1702784856));

        $signer = new SignerV4();

        $query = array();
        $query["param1"] = "value1";
        $query["+param1"] = "value3";
        $query["|param1"] = "value4";
        $query["+param2"] = "";
        $query["|param2"] = "";
        $query["param2"] = "";

        $parsed_url = parse_url($request->request_url);
        $parsed_url['query'] = OssUtil::toQueryString($query);;
        $request->request_url = OssUtil::unparseUrl($parsed_url);

        $signingOpt = array(
            'bucket' => $bucket,
            'key' => $object,
            'region' => 'cn-hangzhou',
            'product' => 'oss',
        );
        $signer->sign($request, $credentials, $signingOpt);

        $authPat = "OSS4-HMAC-SHA256 Credential=ak/20231217/cn-hangzhou/oss/aliyun_v4_request,Signature=b94a3f999cf85bcdc00d332fbd3734ba03e48382c36fa4d5af5df817395bd9ea";
        $this->assertEquals($authPat, $request->request_headers['Authorization']);
    }

    public function testSignerV4AdditionalHeaders()
    {
        // case 1
        $credentials = new Credentials("ak", "sk");
        $request = new RequestCore("http://bucket.oss-cn-hangzhou.aliyuncs.com/1234%2B-/123/1.txt");
        $request->set_method("PUT");
        $bucket = "bucket";
        $object = "1234+-/123/1.txt";

        $request->add_header("x-oss-head1", "value");
        $request->add_header("abc", "value");
        $request->add_header("ZAbc", "value");
        $request->add_header("XYZ", "value");
        $request->add_header("content-type", "text/plain");
        $request->add_header("x-oss-content-sha256", "UNSIGNED-PAYLOAD");

        $request->add_header("Date", gmdate('D, d M Y H:i:s \G\M\T', 1702747512));

        $signer = new SignerV4();

        $query = array();
        $query["param1"] = "value1";
        $query["+param1"] = "value3";
        $query["|param1"] = "value4";
        $query["+param2"] = "";
        $query["|param2"] = "";
        $query["param2"] = "";

        $parsed_url = parse_url($request->request_url);
        $parsed_url['query'] = OssUtil::toQueryString($query);;
        $request->request_url = OssUtil::unparseUrl($parsed_url);

        $signingOpt = array(
            'bucket' => $bucket,
            'key' => $object,
            'region' => 'cn-hangzhou',
            'product' => 'oss',
            'additionalHeaders' => array("ZAbc", "abc")
        );
        $signer->sign($request, $credentials, $signingOpt);

        $authPat = "OSS4-HMAC-SHA256 Credential=ak/20231216/cn-hangzhou/oss/aliyun_v4_request,AdditionalHeaders=abc;zabc,Signature=4a4183c187c07c8947db7620deb0a6b38d9fbdd34187b6dbaccb316fa251212f";
        $this->assertEquals($authPat, $request->request_headers['Authorization']);

        // case 1
        $credentials = new Credentials("ak", "sk");
        $request = new RequestCore("http://bucket.oss-cn-hangzhou.aliyuncs.com/1234%2B-/123/1.txt");
        $request->set_method("PUT");
        $bucket = "bucket";
        $object = "1234+-/123/1.txt";

        $request->add_header("x-oss-head1", "value");
        $request->add_header("abc", "value");
        $request->add_header("ZAbc", "value");
        $request->add_header("XYZ", "value");
        $request->add_header("content-type", "text/plain");
        $request->add_header("x-oss-content-sha256", "UNSIGNED-PAYLOAD");

        $request->add_header("Date", gmdate('D, d M Y H:i:s \G\M\T', 1702747512));

        $signer = new SignerV4();

        $query = array();
        $query["param1"] = "value1";
        $query["+param1"] = "value3";
        $query["|param1"] = "value4";
        $query["+param2"] = "";
        $query["|param2"] = "";
        $query["param2"] = "";

        $parsed_url = parse_url($request->request_url);
        $parsed_url['query'] = OssUtil::toQueryString($query);;
        $request->request_url = OssUtil::unparseUrl($parsed_url);

        $signingOpt = array(
            'bucket' => $bucket,
            'key' => $object,
            'region' => 'cn-hangzhou',
            'product' => 'oss',
            'additionalHeaders' => array("x-oss-no-exist", "ZAbc", "x-oss-head1", "abc")
        );
        $signer->sign($request, $credentials, $signingOpt);

        $authPat = "OSS4-HMAC-SHA256 Credential=ak/20231216/cn-hangzhou/oss/aliyun_v4_request,AdditionalHeaders=abc;zabc,Signature=4a4183c187c07c8947db7620deb0a6b38d9fbdd34187b6dbaccb316fa251212f";
        $this->assertEquals($authPat, $request->request_headers['Authorization']);
    }

    public function testSignerV4Presign()
    {
        // case 1
        $credentials = new Credentials("ak", "sk");
        $request = new RequestCore("http://bucket.oss-cn-hangzhou.aliyuncs.com/1234%2B-/123/1.txt");
        $request->set_method("PUT");
        $bucket = "bucket";
        $object = "1234+-/123/1.txt";

        $request->add_header("x-oss-head1", "value");
        $request->add_header("abc", "value");
        $request->add_header("ZAbc", "value");
        $request->add_header("XYZ", "value");
        $request->add_header("content-type", "application/octet-stream");
        $request->add_header("Date", gmdate('D, d M Y H:i:s \G\M\T', 1702781677));

        $signer = new SignerV4();

        $query = array();
        $query["param1"] = "value1";
        $query["+param1"] = "value3";
        $query["|param1"] = "value4";
        $query["+param2"] = "";
        $query["|param2"] = "";
        $query["param2"] = "";

        $parsed_url = parse_url($request->request_url);
        $parsed_url['query'] = OssUtil::toQueryString($query);;
        $request->request_url = OssUtil::unparseUrl($parsed_url);

        $signingOpt = array(
            'bucket' => $bucket,
            'key' => $object,
            'region' => 'cn-hangzhou',
            'product' => 'oss',
            'expiration' => 1702782276,
        );
        $signer->presign($request, $credentials, $signingOpt);
        $parsed_url = parse_url($request->request_url);
        $queryString = isset($parsed_url['query']) ? $parsed_url['query'] : '';
        $query = array();
        parse_str($queryString, $query);
        $this->assertEquals('OSS4-HMAC-SHA256', $query['x-oss-signature-version']);
        $this->assertEquals('OSS4-HMAC-SHA256', $query['x-oss-signature-version']);
        $this->assertEquals('599', $query['x-oss-expires']);
        $this->assertEquals('ak/20231217/cn-hangzhou/oss/aliyun_v4_request', $query['x-oss-credential']);
        $this->assertEquals('a39966c61718be0d5b14e668088b3fa07601033f6518ac7b523100014269c0fe', $query['x-oss-signature']);
        $this->assertFalse(isset($query['x-oss-additional-headers']));
    }

    public function testSignerV4PresignWithToken()
    {
        // case 1
        $credentials = new Credentials("ak", "sk", "token");
        $request = new RequestCore("http://bucket.oss-cn-hangzhou.aliyuncs.com/1234%2B-/123/1.txt");
        $request->set_method("PUT");
        $bucket = "bucket";
        $object = "1234+-/123/1.txt";

        $request->add_header("x-oss-head1", "value");
        $request->add_header("abc", "value");
        $request->add_header("ZAbc", "value");
        $request->add_header("XYZ", "value");
        $request->add_header("content-type", "application/octet-stream");
        $request->add_header("Date", gmdate('D, d M Y H:i:s \G\M\T', 1702785388));

        $signer = new SignerV4();

        $query = array();
        $query["param1"] = "value1";
        $query["+param1"] = "value3";
        $query["|param1"] = "value4";
        $query["+param2"] = "";
        $query["|param2"] = "";
        $query["param2"] = "";

        $parsed_url = parse_url($request->request_url);
        $parsed_url['query'] = OssUtil::toQueryString($query);;
        $request->request_url = OssUtil::unparseUrl($parsed_url);

        $signingOpt = array(
            'bucket' => $bucket,
            'key' => $object,
            'region' => 'cn-hangzhou',
            'product' => 'oss',
            'expiration' => 1702785987,
        );
        $signer->presign($request, $credentials, $signingOpt);
        $parsed_url = parse_url($request->request_url);
        $queryString = isset($parsed_url['query']) ? $parsed_url['query'] : '';
        $query = array();
        parse_str($queryString, $query);
        $this->assertEquals('OSS4-HMAC-SHA256', $query['x-oss-signature-version']);
        $this->assertEquals('599', $query['x-oss-expires']);
        $this->assertEquals('ak/20231217/cn-hangzhou/oss/aliyun_v4_request', $query['x-oss-credential']);
        $this->assertEquals('3817ac9d206cd6dfc90f1c09c00be45005602e55898f26f5ddb06d7892e1f8b5', $query['x-oss-signature']);
        $this->assertFalse(isset($query['x-oss-additional-headers']));
        //print($request->request_url);
    }

    public function testSignerV4PresignWithAdditionalHeaders()
    {
        // case 1
        $credentials = new Credentials("ak", "sk");
        $request = new RequestCore("http://bucket.oss-cn-hangzhou.aliyuncs.com/1234%2B-/123/1.txt");
        $request->set_method("PUT");
        $bucket = "bucket";
        $object = "1234+-/123/1.txt";

        $request->add_header("x-oss-head1", "value");
        $request->add_header("abc", "value");
        $request->add_header("ZAbc", "value");
        $request->add_header("XYZ", "value");
        $request->add_header("content-type", "application/octet-stream");
        $request->add_header("Date", gmdate('D, d M Y H:i:s \G\M\T', 1702783809));

        $signer = new SignerV4();

        $query = array();
        $query["param1"] = "value1";
        $query["+param1"] = "value3";
        $query["|param1"] = "value4";
        $query["+param2"] = "";
        $query["|param2"] = "";
        $query["param2"] = "";

        $parsed_url = parse_url($request->request_url);
        $parsed_url['query'] = OssUtil::toQueryString($query);;
        $request->request_url = OssUtil::unparseUrl($parsed_url);

        $signingOpt = array(
            'bucket' => $bucket,
            'key' => $object,
            'region' => 'cn-hangzhou',
            'product' => 'oss',
            'expiration' => 1702784408,
            'additionalHeaders' => array("ZAbc", "abc")
        );
        $signer->presign($request, $credentials, $signingOpt);
        $parsed_url = parse_url($request->request_url);
        $queryString = isset($parsed_url['query']) ? $parsed_url['query'] : '';
        $query = array();
        parse_str($queryString, $query);
        $this->assertEquals('OSS4-HMAC-SHA256', $query['x-oss-signature-version']);
        $this->assertEquals('20231217T033009Z', $query['x-oss-date']);
        $this->assertEquals('599', $query['x-oss-expires']);
        $this->assertEquals('ak/20231217/cn-hangzhou/oss/aliyun_v4_request', $query['x-oss-credential']);
        $this->assertEquals('6bd984bfe531afb6db1f7550983a741b103a8c58e5e14f83ea474c2322dfa2b7', $query['x-oss-signature']);
        $this->assertEquals('abc;zabc', $query['x-oss-additional-headers']);
    }
}


