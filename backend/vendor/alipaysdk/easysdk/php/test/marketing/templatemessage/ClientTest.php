<?php


namespace Alipay\EasySDK\Test\marketing\templatemessage;


use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Test\TestAccount;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testSend(){
        $account = new TestAccount();
        Factory::setOptions($account->getTestAccount());
        $result = Factory::marketing()->templateMessage()->send("2088102122458832",
            "2017010100000000580012345678",
            "MDI4YzIxMDE2M2I5YTQzYjUxNWE4MjA4NmU1MTIyYmM=",
            "page/component/index",
            "{\"keyword1\": {\"value\" : \"12:00\"},\"keyword2\": {\"value\" : \"20180808\"},\"keyword3\": {\"value\" : \"支付宝\"}}");
        $this->assertEquals('40004',$result->code);
        $this->assertEquals('Business Failed',$result->msg);
        $this->assertEquals('USER_TEMPLATE_ILLEGAL',$result->subCode);
        $this->assertEquals('模板非法',$result->subMsg);
    }

}