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

    public function testDecrypt(){
        $result = Factory::util()->aes()->decrypt("ILpoMowjIQjfYMR847rnFQ==");
        $this->assertEquals(true, $result == 'test1234567');

    }

    public function testEncrypt(){
        $result = Factory::util()->aes()->encrypt("test1234567");
        $this->assertEquals(true, $result == 'ILpoMowjIQjfYMR847rnFQ==');
    }
}
