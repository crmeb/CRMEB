<?php


namespace Alipay\EasySDK\Test\member\identification;


use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Member\Identification\Models\IdentityParam;
use Alipay\EasySDK\Member\Identification\Models\MerchantConfig;
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

    public function testInit()
    {
        $identityParam = new IdentityParam();
        $identityParam->identityType = "CERT_INFO";
        $identityParam->certType = "IDENTITY_CARD";
        $identityParam->certName = "张三";
        $identityParam->certNo = "5139011988090987631";

        $merchantConfig = new MerchantConfig();
        $merchantConfig->returnUrl = "www.taobao.com";

        $result = Factory::member()->identification()->init(microtime(),'FACE',$identityParam,$merchantConfig);
        $this->assertEquals('10000', $result->code);
        $this->assertEquals('Success', $result->msg);
    }

    public function testCertify()
    {
        $result = Factory::member()->identification()->certify("16cbbf40de9829e337d51818a76eacc2");
        $this->assertEquals(true, strpos($result->body,'sign')>0);
        $this->assertEquals(true, strpos($result->body,'gateway.do')>0);
    }

    public function testQuery()
    {
        $result = Factory::member()->identification()->query("16cbbf40de9829e337d51818a76eacc2");
        $this->assertEquals('40004', $result->code);
        $this->assertEquals('Business Failed', $result->msg);
        $this->assertEquals('CERTIFY_ID_EXPIRED',$result->subCode);
        $this->assertEquals('认证已失效',$result->subMsg);

    }

}