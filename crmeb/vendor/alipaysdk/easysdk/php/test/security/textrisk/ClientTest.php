<?php


namespace Alipay\EasySDK\Test\security\textrisk;


use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Test\TestAccount;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testDetectContent(){
        $account = new TestAccount();
        Factory::setOptions($account->getTestAccount());
        $result = Factory::security()->textRisk()->detect("test");
        $this->assertEquals('10000', $result->code);
        $this->assertEquals('Success', $result->msg);
    }
}