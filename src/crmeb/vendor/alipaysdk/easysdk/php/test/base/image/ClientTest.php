<?php


namespace Alipay\EasySDK\Test\base\image;


use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Test\TestAccount;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testUpload(){
        $account = new TestAccount();
        $responseChecker = new ResponseChecker();
        Factory::setOptions($account->getTestAccount());
        $filePath = $account->getResourcesPath(). '/resources/fixture/sample.png';
        $result =  Factory::base()->image()->upload("测试图片", $filePath);
        $this->assertEquals(true, $responseChecker->success($result));
        $this->assertEquals('Success', $result->msg);
    }
}