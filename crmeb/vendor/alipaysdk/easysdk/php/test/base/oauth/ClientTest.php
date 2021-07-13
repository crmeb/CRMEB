<?php


namespace Alipay\EasySDK\Test\base\oauth;


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

    public function testGetTokenWhenGrantTypeIsAuthorizationCode()
    {
        $result = Factory::base()->oauth()->getToken('ee4b3c871f7c4f30a82251908458VB64');
        $this->assertEquals('40002', $result->code);
        $this->assertEquals('Invalid Arguments', $result->msg);
        $this->assertEquals('isv.code-invalid', $result->subCode);
        $this->assertEquals('授权码code无效', $result->subMsg);
    }

    public function testGetTokenWhenGrantTypeIsRefreshToken()
    {
        $result = Factory::base()->oauth()->refreshToken('1234567890');
        $this->assertEquals('40002', $result->code);
        $this->assertEquals('Invalid Arguments', $result->msg);
        $this->assertEquals('isv.refresh-token-invalid', $result->subCode);
        $this->assertEquals('刷新令牌refresh_token无效', $result->subMsg);
    }
}