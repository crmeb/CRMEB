<?php

namespace Alipay\EasySDK\Test\Base;

use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;
use Alipay\EasySDK\Test\TestAccount;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testCreate(){
        $account = new TestAccount();
        $responseChecker = new ResponseChecker();
        Factory::setOptions($account->getTestAccount());
        $result = Factory::base()->qrcode()->create('https://opendocs.alipay.com','ageIndex=1','文档站点');
        $this->assertEquals(true, $responseChecker->success($result));
        $this->assertEquals('Success', $result->msg);
    }
}