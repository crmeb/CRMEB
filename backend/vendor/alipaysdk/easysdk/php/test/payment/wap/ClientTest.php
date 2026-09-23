<?php

use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Test\TestAccount;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $account = new TestAccount();
        Factory::setOptions($account->getTestAccount());
    }
    public function testPay(){
        $create =Factory::payment()->common()->create("Iphone6 16G",
            microtime(), "88.88", "2088002656718920");
        $result = Factory::payment()->wap()->pay("Iphone6 16G",$create->outTradeNo,"0.10","https://www.taobao.com","https://www.taobao.com");
        $this->assertEquals(true, strpos($result->body,'return_url')>0);
        $this->assertEquals(true, strpos($result->body,'sign')>0);
    }

    public function testPayWithOptional(){
        $create =Factory::payment()->common()->create("Iphone6 16G",
            microtime(), "88.88", "2088002656718920");
        $result = Factory::payment()->wap()
            ->agent("ca34ea491e7146cc87d25fca24c4cD11")
            ->batchOptional($this->getOptionalArgs())
            ->pay("Iphone6 16G",$create->outTradeNo,"0.10","https://www.taobao.com","https://www.taobao.com");
        $this->assertEquals(true, strpos($result->body,'return_url')>0);
        $this->assertEquals(true, strpos($result->body,'sign')>0);
    }

    private function getOptionalArgs(){
        $optionalArgs = array(
            "timeout_express" => "10m",
            "body" => "Iphone6 16G"
        );
        return $optionalArgs;
    }
}