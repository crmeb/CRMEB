<?php

namespace OSS\Tests;


use OSS\Model\CorsConfig;
use OSS\Model\CorsRule;
use OSS\Core\OssException;

class CorsConfigTest extends \PHPUnit_Framework_TestCase
{
    private $validXml = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<CORSConfiguration>
<CORSRule>
<AllowedOrigin>http://www.b.com</AllowedOrigin>
<AllowedOrigin>http://www.a.com</AllowedOrigin>
<AllowedOrigin>http://www.a.com</AllowedOrigin>
<AllowedMethod>GET</AllowedMethod>
<AllowedMethod>PUT</AllowedMethod>
<AllowedMethod>POST</AllowedMethod>
<AllowedHeader>x-oss-test</AllowedHeader>
<AllowedHeader>x-oss-test2</AllowedHeader>
<AllowedHeader>x-oss-test2</AllowedHeader>
<AllowedHeader>x-oss-test3</AllowedHeader>
<ExposeHeader>x-oss-test1</ExposeHeader>
<ExposeHeader>x-oss-test1</ExposeHeader>
<ExposeHeader>x-oss-test2</ExposeHeader>
<MaxAgeSeconds>10</MaxAgeSeconds>
</CORSRule>
<CORSRule>
<AllowedOrigin>http://www.b.com</AllowedOrigin>
<AllowedMethod>GET</AllowedMethod>
<AllowedHeader>x-oss-test</AllowedHeader>
<ExposeHeader>x-oss-test1</ExposeHeader>
<MaxAgeSeconds>110</MaxAgeSeconds>
</CORSRule>
</CORSConfiguration>
BBBB;

    private $validXml2 = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<CORSConfiguration>
<CORSRule>
<AllowedOrigin>http://www.b.com</AllowedOrigin>
<AllowedOrigin>http://www.a.com</AllowedOrigin>
<AllowedOrigin>http://www.a.com</AllowedOrigin>
<AllowedMethod>GET</AllowedMethod>
<AllowedMethod>PUT</AllowedMethod>
<AllowedMethod>POST</AllowedMethod>
<AllowedHeader>x-oss-test</AllowedHeader>
<AllowedHeader>x-oss-test2</AllowedHeader>
<AllowedHeader>x-oss-test2</AllowedHeader>
<AllowedHeader>x-oss-test3</AllowedHeader>
<ExposeHeader>x-oss-test1</ExposeHeader>
<ExposeHeader>x-oss-test1</ExposeHeader>
<ExposeHeader>x-oss-test2</ExposeHeader>
<MaxAgeSeconds>10</MaxAgeSeconds>
</CORSRule>
</CORSConfiguration>
BBBB;

    public function testParseValidXml()
    {
        $corsConfig = new CorsConfig();
        $corsConfig->parseFromXml($this->validXml);
        $this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($corsConfig->serializeToXml()));
        $this->assertNotNull($corsConfig->getRules());
        $rules = $corsConfig->getRules();
        $this->assertNotNull($rules[0]->getAllowedHeaders());
        $this->assertNotNull($rules[0]->getAllowedMethods());
        $this->assertNotNull($rules[0]->getAllowedOrigins());
        $this->assertNotNull($rules[0]->getExposeHeaders());
        $this->assertNotNull($rules[0]->getMaxAgeSeconds());
    }

    public function testParseValidXml2()
    {
        $corsConfig = new CorsConfig();
        $corsConfig->parseFromXml($this->validXml2);
        $this->assertEquals($this->cleanXml($this->validXml2), $this->cleanXml($corsConfig->serializeToXml()));
    }

    public function testCreateCorsConfigFromMoreThan10Rules()
    {
        $corsConfig = new CorsConfig();
        $rule = new CorsRule();
        for ($i = 0; $i < CorsConfig::OSS_MAX_RULES; $i += 1) {
            $corsConfig->addRule($rule);
        }
        try {
            $corsConfig->addRule($rule);
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals($e->getMessage(), "num of rules in the config exceeds self::OSS_MAX_RULES: " . strval(CorsConfig::OSS_MAX_RULES));
        }
    }

    public function testCreateCorsConfigParamAbsent()
    {
        $corsConfig = new CorsConfig();
        $rule = new CorsRule();
        $corsConfig->addRule($rule);

        try {
            $xml = $corsConfig->serializeToXml();
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals($e->getMessage(), "maxAgeSeconds is not set in the Rule");
        }
    }

    public function testCreateCorsConfigFromScratch()
    {
        $corsConfig = new CorsConfig();
        $rule = new CorsRule();
        $rule->addAllowedHeader("x-oss-test");
        $rule->addAllowedHeader("x-oss-test2");
        $rule->addAllowedHeader("x-oss-test2");
        $rule->addAllowedHeader("x-oss-test3");
        $rule->addAllowedOrigin("http://www.b.com");
        $rule->addAllowedOrigin("http://www.a.com");
        $rule->addAllowedOrigin("http://www.a.com");
        $rule->addAllowedMethod("GET");
        $rule->addAllowedMethod("PUT");
        $rule->addAllowedMethod("POST");
        $rule->addExposeHeader("x-oss-test1");
        $rule->addExposeHeader("x-oss-test1");
        $rule->addExposeHeader("x-oss-test2");
        $rule->setMaxAgeSeconds(10);
        $corsConfig->addRule($rule);
        $this->assertEquals($this->cleanXml($this->validXml2), $this->cleanXml($corsConfig->serializeToXml()));
        $this->assertEquals($this->cleanXml($this->validXml2), $this->cleanXml(strval($corsConfig)));
    }

    private function cleanXml($xml)
    {
        return str_replace("\n", "", str_replace("\r", "", $xml));
    }
}
