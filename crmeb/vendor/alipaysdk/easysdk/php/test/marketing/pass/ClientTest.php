<?php


namespace Alipay\EasySDK\Test\marketing\pass;


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

    public function testCreateTemplate()
    {
        $result = Factory::marketing()->pass()->createTemplate("1234567890", $this->getTplContent());
        $this->assertEquals('10000', $result->code);
        $this->assertEquals('Success', $result->msg);
    }

    public function testUpdateTemplate()
    {
        $result = Factory::marketing()->pass()->updateTemplate("2020012014534017917956080", $this->getTplContent());
        $this->assertEquals('10000', $result->code);
        $this->assertEquals('Success', $result->msg);
    }

    public function testAddInstance()
    {
        $result = Factory::marketing()->pass()->addInstance("2020012014534017917956080", "{}",
            "1", "{\"partner_id\":\"2088102114633762\",\"out_trade_no\":\"1234567\"}");
        $this->assertEquals('40004', $result->code);
        $this->assertEquals('Business Failed', $result->msg);
        $this->assertEquals('KP.AE_ALIPASS_APPID_NOSUPPORT', $result->subCode);
        $this->assertEquals('该AppId不支持', $result->subMsg);
    }

    public function testUpdateInstance()
    {
        $result = Factory::marketing()->pass()->updateInstance("209919213",
            "2088918273", "{}", "USED", "8612231273", "wave");
        $this->assertEquals('40004', $result->code);
        $this->assertEquals('Business Failed', $result->msg);
        $this->assertEquals('KP.AE_ALIPASS_NOTEXIST', $result->subCode);
        $this->assertEquals('卡券不存在', $result->subMsg);
    }

    private function getTplContent()
    {
        return '{"logo": "http://img01.taobaocdn.com/top/i1/LB1NDJuQpXXXXbYXFXXXXXXXXXX","strip": null,"icon": null,"content": {
		"evoucherInfo": {
			"goodsId": "",
			"title": "test",
			"type": "boardingPass",
			"product": "air",
			"startDate": "2020-01-20 13:45:56",
			"endDate": "2020-01-25 13:45:56",
			"operation": [{
				"message": {
					"img": "http://img01.taobaocdn.com/top/i1/LB1NDJuQpXXXXbYXFXXXXXXXXXX",
					"target": ""
				},
				"format": "img",
				"messageEncoding": "utf-8",
				"altText": ""
			}],
			"einfo": {
				"logoText": "test",
				"headFields": [{"key": "test","label": "测试","value": "","type": "text"}],
				"primaryFields": [{"key": "from","label": "测试","value": "","type": "text"},{"key": "to","label": "测试","value": "","type": "text"}],
				"secondaryFields": [{"key": "fltNo","label": "航班号","value": "CA123","type": "text"}],
				"auxiliaryFields": [{"key": "test","label": "测试","value": "","type": "text"}],
				"backFields": []
			},
			"locations": []
		},
		"merchant": {"mname": "君泓","mtel": "","minfo": ""},
		"platform": {
			"channelID": "2088201564809153",
			"webServiceUrl": "https://alipass.alipay.com/builder/syncRecord.htm?tempId=2020012013442621326446216"
		},
		"style": {"backgroundColor": "RGB(26,150,219)"},
		"fileInfo": {
			"formatVersion": "2",
			"canShare": true,
			"canBuy": false,
			"canPresent": true,
			"serialNumber": "2020012013520759738677158",
			"supportTaxi": "true",
			"taxiSchemaUrl": ""
		},
		"appInfo": {"app": {"android_appid": "","ios_appid": "","android_launch": "","ios_launch": "","android_download": "","ios_download": ""},"label": "测试","message": ""},
		"source": "alipassprod",
		"alipayVerify": ["qrcode"]}}';
    }
}