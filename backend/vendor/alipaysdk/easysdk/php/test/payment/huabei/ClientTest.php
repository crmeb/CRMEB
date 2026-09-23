<?php


namespace Alipay\EasySDK\Test\payment\huabei;


use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Payment\Huabei\Models\HuabeiConfig;
use Alipay\EasySDK\Test\TestAccount;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testCreate()
    {
        $account = new TestAccount();
        Factory::setOptions($account->getTestAccount());
        $config = new HuabeiConfig();
        $config->hbFqNum = '3';
        $config->hbFqSellerPercent = '0';
        $result = Factory::payment()->huabei()->create("Iphone6 16G",  microtime(), "0.10", "2088002656718920", $config);
        $this->assertEquals('10000', $result->code);
        $this->assertEquals('Success', $result->msg);
    }
}