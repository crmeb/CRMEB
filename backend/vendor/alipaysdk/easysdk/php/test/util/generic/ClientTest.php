<?php


namespace Alipay\EasySDK\Test\util\generic;


use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Test\TestAccount;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $account = new TestAccount();
        Factory::setOptions($account->getTestAccount());
    }

    public function testSDKExecute()
    {
        $bizParams = array(
            "subject" => "Iphone6 16G",
            "out_trade_no" => "f4833085-0c46-4bb0-8e5f-622a02a4cffc",
            "total_amount" => "0.10"
        );

        $textParams = array();

        $result = Factory::util()->generic()->sdkExecute("alipay.trade.app.pay", $textParams, $bizParams);
        $this->assertEquals(true, strpos($result->body, 'alipay_sdk=alipay-easysdk-php') > 0);
        $this->assertEquals(true, strpos($result->body, 'sign') > 0);
    }

    public function testFileExecute()
    {
        $textParams = array(
            "image_type" => "png",
            "image_name" => "海底捞",
            "image_pids" => "22088021822217233"
        );
        $account = new TestAccount();
        $filePath = $account->getResourcesPath(). '/resources/fixture/sample.png';

        $fileParams = array(
            "image_content" => $filePath
        );

        $result = Factory::util()->generic()->fileExecute("alipay.offline.material.image.upload", $textParams, null, $fileParams);
        $responseChecker = new ResponseChecker();
        $this->assertEquals(true, $responseChecker->success($result));
        $this->assertEquals('Success', $result->msg);
    }

    public function testExecuteWithoutAppAuthToken()
    {
        $result = Factory::util()->generic()->execute("alipay.trade.create", null, $this->getBizParams(microtime()));
        $this->assertEquals('10000', $result->code);
        $this->assertEquals('Success', $result->msg);
    }

    public function testExecuteWithAppAuthToken()
    {
        $result = Factory::util()->generic()->execute("alipay.trade.create", $this->getTextParams(), $this->getBizParams(microtime()));
        $this->assertEquals('20001', $result->code);
        $this->assertEquals('Insufficient Token Permissions', $result->msg);
        $this->assertEquals('aop.invalid-app-auth-token', $result->subCode);
        $this->assertEquals('无效的应用授权令牌', $result->subMsg);
    }

    //设置系统参数（OpenAPI中非biz_content里的参数）
    private function getTextParams()
    {
        return array("app_auth_token" => "201712BB_D0804adb2e743078d1822d536956X34");
    }

    private function getBizParams($outTradeNo)
    {
        $bizParams = array(
            "subject" => "Iphone6 16G",
            "out_trade_no" => $outTradeNo,
            "total_amount" => "0.10",
            "buyer_id" => "2088002656718920",
            "extend_params" => $this->getHuabeiParams()
        );
        return $bizParams;
    }

    private function getHuabeiParams()
    {
        $extendParams = array("hb_fq_num" => "3", "hb_fq_seller_percent" => "3");
        return $extendParams;
    }
}