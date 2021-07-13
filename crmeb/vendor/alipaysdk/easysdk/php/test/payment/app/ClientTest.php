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

    public function testPay()
    {
        $result = Factory::payment()->app()->pay("Iphone6 16G", "f4833085-0c46-4bb0-8e5f-622a02a4cffc", "0.10");
        $this->assertEquals(true, strpos($result->body, 'alipay_sdk=alipay-easysdk-php') > 0);
        $this->assertEquals(true, strpos($result->body, 'sign') > 0);
    }

    public function testPayWithOptional()
    {
        $result = Factory::payment()->app()
            ->agent("ca34ea491e7146cc87d25fca24c4cD11")
            ->optional("extend_params",$this->getHuabeiParams())
            ->pay("Iphone6 16G", "f4833085-0c46-4bb0-8e5f-622a02a4cffc", "0.10");
        $this->assertEquals(true, strpos($result->body, 'alipay_sdk=alipay-easysdk-php') > 0);
        $this->assertEquals(true, strpos($result->body, 'sign') > 0);
    }

    private function getHuabeiParams()
    {
        $extendParams = array("hb_fq_num" => "3", "hb_fq_seller_percent" => "3");
        return $extendParams;
    }

}