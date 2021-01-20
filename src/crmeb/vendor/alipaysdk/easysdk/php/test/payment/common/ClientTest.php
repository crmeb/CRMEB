<?php


namespace Alipay\EasySDK\Test\payment\common;


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

    public function testCrate()
    {
        $result = Factory::payment()->common()->create("Iphone6 16G",
            microtime(), "88.88", "2088002656718920");
        $this->assertEquals('10000', $result->code);
        $this->assertEquals('Success', $result->msg);
        return $result->outTradeNo;
    }

    public function testCreateWithOptional(){
        $result = Factory::payment()->common()
            ->optional("goods_detail", $this->getGoodsDetail())
            ->create("Iphone6 16G",microtime(), "0.01", "2088002656718920");
        $this->assertEquals('10000', $result->code);
        $this->assertEquals('Success', $result->msg);
    }

    private function getGoodsDetail(){
        $goodDetail = array(
            "goods_id" => "apple-01",
            "goods_name" => "iPhone6 16G",
            "quantity" => 1,
            "price" => "0.01"
        );
        $goodsDetail[0] = $goodDetail;
        return $goodsDetail;
    }

    public function testQuery()
    {
        $result = Factory::payment()->common()->query('6f149ddb-ab8c-4546-81fb-5880b4aaa318');
        $this->assertEquals('10000', $result->code);
        $this->assertEquals('Success', $result->msg);
    }

    public function testCancel()
    {
        $result = Factory::payment()->common()->cancel($this->testCrate());
        $this->assertEquals('10000', $result->code);
        $this->assertEquals('Success', $result->msg);
    }

    public function testClose()
    {
        $result = Factory::payment()->common()->close($this->testCrate());
        $this->assertEquals('10000', $result->code);
        $this->assertEquals('Success', $result->msg);
    }

    public function testRefund()
    {
        $result = Factory::payment()->common()->refund($this->testCrate(), '0.01');
        $this->assertEquals('40004', $result->code);
        $this->assertEquals('Business Failed', $result->msg);
        $this->assertEquals('ACQ.TRADE_STATUS_ERROR', $result->subCode);
        $this->assertEquals('交易状态不合法', $result->subMsg);
    }

    public function testRefundQuery()
    {
        $result = Factory::payment()->common()->queryRefund($this->testCrate(), "20200401010101001");
        $this->assertEquals('10000', $result->code);
        $this->assertEquals('Success', $result->msg);
    }

    public function testDownloadBill()
    {
        $result = Factory::payment()->common()->downloadBill("trade", "2020-01");
        $this->assertEquals('10000', $result->code);
        $this->assertEquals('Success', $result->msg);
    }


}