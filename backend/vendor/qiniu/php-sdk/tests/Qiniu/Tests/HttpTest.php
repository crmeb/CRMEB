<?php
namespace Qiniu\Tests;

use PHPUnit\Framework\TestCase;

use Qiniu\Http\Client;
use Qiniu\Http\RequestOptions;
use Qiniu\Http\Response;

class HttpTest extends TestCase
{
    public function testGet()
    {
        $response = Client::get('qiniu.com');
        $this->assertEquals(200, $response->statusCode);
        $this->assertNotNull($response->body);
        $this->assertNull($response->error);
    }

    public function testGetQiniu()
    {
        $response = Client::get('upload.qiniu.com');
        $this->assertEquals(405, $response->statusCode);
        $this->assertNotNull($response->body);
        $this->assertNotNull($response->xReqId());
        $this->assertNotNull($response->xLog());
        $this->assertNotNull($response->error);
    }

    public function testGetTimeout()
    {
        $reqOpt = new RequestOptions();
        $reqOpt->timeout = 1;
        $response = Client::get('localhost:9000/timeout.php', array(), $reqOpt);
        $this->assertEquals(-1, $response->statusCode);
    }

    public function testGetRedirect()
    {
        $response = Client::get('localhost:9000/redirect.php');
        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals('application/json;charset=UTF-8', $response->normalizedHeaders['Content-Type']);
        $respData = $response->json();
        $this->assertEquals('ok', $respData['msg']);
    }

    public function testDelete()
    {
        $response = Client::delete('uc.qbox.me/bucketTagging', array());
        $this->assertEquals(401, $response->statusCode);
        $this->assertNotNull($response->body);
        $this->assertNotNull($response->error);
    }

    public function testDeleteQiniu()
    {
        $response = Client::delete('uc.qbox.me/bucketTagging', array());
        $this->assertEquals(401, $response->statusCode);
        $this->assertNotNull($response->body);
        $this->assertNotNull($response->xReqId());
        $this->assertNotNull($response->xLog());
        $this->assertNotNull($response->error);
    }

    public function testDeleteTimeout()
    {
        $reqOpt = new RequestOptions();
        $reqOpt->timeout = 1;
        $response = Client::delete('localhost:9000/timeout.php', array(), $reqOpt);
        $this->assertEquals(-1, $response->statusCode);
    }


    public function testPost()
    {
        $response = Client::post('qiniu.com', null);
        $this->assertEquals(200, $response->statusCode);
        $this->assertNotNull($response->body);
        $this->assertNull($response->error);
    }

    public function testPostQiniu()
    {
        $response = Client::post('upload.qiniu.com', null);
        $this->assertEquals(400, $response->statusCode);
        $this->assertNotNull($response->body);
        $this->assertNotNull($response->xReqId());
        $this->assertNotNull($response->xLog());
        $this->assertNotNull($response->error);
    }

    public function testPostTimeout()
    {
        $reqOpt = new RequestOptions();
        $reqOpt->timeout = 1;
        $response = Client::post('localhost:9000/timeout.php', null, array(), $reqOpt);
        $this->assertEquals(-1, $response->statusCode);
    }

    public function testSocks5Proxy()
    {
        $reqOpt = new RequestOptions();
        $reqOpt->proxy = 'socks5://localhost:8080';
        $response = Client::post('qiniu.com', null, array(), $reqOpt);
        $this->assertEquals(-1, $response->statusCode);

        $reqOpt->proxy_user_password = 'user:pass';
        $response = Client::post('qiniu.com', null, array(), $reqOpt);
        $this->assertEquals(200, $response->statusCode);
    }

    public function testPut()
    {
        $response = Client::PUT('uc.qbox.me/bucketTagging', null);
        $this->assertEquals(401, $response->statusCode);
        $this->assertNotNull($response->body);
        $this->assertNotNull($response->error);
    }

    public function testPutQiniu()
    {
        $response = Client::put('uc.qbox.me/bucketTagging', null);
        $this->assertEquals(401, $response->statusCode);
        $this->assertNotNull($response->body);
        $this->assertNotNull($response->xReqId());
        $this->assertNotNull($response->xLog());
        $this->assertNotNull($response->error);
    }


    public function testPutTimeout()
    {
        $reqOpt = new RequestOptions();
        $reqOpt->timeout = 1;
        $response = Client::put('localhost:9000/timeout.php', null, array(), $reqOpt);
        $this->assertEquals(-1, $response->statusCode);
    }

    public function testNeedRetry()
    {
        $testCases = array_merge(
            array(array(-1, true)),
            array_map(function ($i) {
                return array($i, false);
            }, range(100, 499)),
            array_map(function ($i) {
                if (in_array($i, array(
                    501, 509, 573, 579, 608, 612, 614, 616, 618, 630, 631, 632, 640, 701
                ))) {
                    return array($i, false);
                }
                return array($i, true);
            }, range(500, 799))
        );
        $resp = new Response(-1, 222, array(), '{"msg": "mock"}', null);
        foreach ($testCases as $testCase) {
            list($code, $expectNeedRetry) = $testCase;
            $resp->statusCode = $code;
            $msg = $resp->statusCode . ' need' . ($expectNeedRetry ? '' : ' NOT') . ' retry';
            $this->assertEquals($expectNeedRetry, $resp->needRetry(), $msg);
        }
    }
}
