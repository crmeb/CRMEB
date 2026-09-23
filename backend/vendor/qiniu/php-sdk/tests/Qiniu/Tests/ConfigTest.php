<?php

namespace Qiniu\Tests {

    use PHPUnit\Framework\TestCase;

    use Qiniu\Config;

    class ConfigTest extends TestCase
    {
        protected $accessKey;
        protected $bucketName;

        /**
         * @before
         */
        protected function setUpAkAndBucket()
        {
            global $accessKey;
            $this->accessKey = $accessKey;
            global $bucketName;
            $this->bucketName = $bucketName;
        }

        public function testGetApiHost()
        {
            $conf = new Config();
            $hasException = false;
            $apiHost = '';
            try {
                $apiHost = $conf->getApiHost($this->accessKey, $this->bucketName);
            } catch (\Exception $e) {
                $hasException = true;
            }
            $this->assertFalse($hasException);
        }

        public function testGetApiHostErrored()
        {
            $conf = new Config();
            $hasException = false;
            try {
                $conf->getApiHost($this->accessKey, "fakebucket");
            } catch (\Exception $e) {
                $hasException = true;
            }
            $this->assertTrue($hasException);
        }

        public function testGetApiHostV2()
        {
            $conf = new Config();
            list($apiHost, $err) = $conf->getApiHostV2($this->accessKey, $this->bucketName);
            $this->assertNull($err);
        }

        public function testGetApiHostV2Errored()
        {
            $conf = new Config();
            list($apiHost, $err) = $conf->getApiHostV2($this->accessKey, "fakebucket");
            $this->assertNotNull($err->code());
            $this->assertEquals(631, $err->code());
            $this->assertNull($apiHost);
        }

        public function testSetUcHost()
        {
            $conf = new Config();
            $this->assertEquals('http://' . Config::UC_HOST, $conf->getUcHost());
            $conf->setUcHost("uc.example.com");
            $this->assertEquals("http://uc.example.com", $conf->getUcHost());

            $conf = new Config();
            $conf->useHTTPS = true;
            $this->assertEquals('https://' . Config::UC_HOST, $conf->getUcHost());
            $conf->setUcHost("uc.example.com");
            $this->assertEquals("https://uc.example.com", $conf->getUcHost());
        }

        public function testGetRegionWithCustomDomain()
        {
            $conf = new Config();
            $conf->setQueryRegionHost(
                "uc.qbox.me"
            );
            list(, $err) = $conf->getRsHostV2($this->accessKey, $this->bucketName);
            $this->assertNull($err);
        }

        public function testGetRegionWithBackupDomains()
        {
            $conf = new Config();
            $conf->setQueryRegionHost(
                "fake-uc.phpsdk.qiniu.com",
                array(
                    "unavailable-uc.phpsdk.qiniu.com",
                    Config::UC_HOST // real uc
                )
            );
            list(, $err) = $conf->getRsHostV2($this->accessKey, $this->bucketName);
            $this->assertNull($err);
        }

        public function testGetRegionWithUcAndBackupDomains()
        {
            $conf = new Config();
            $conf->setUcHost("fake-uc.phpsdk.qiniu.com");
            $conf->setBackupQueryRegionHosts(
                array(
                    "unavailable-uc.phpsdk.qiniu.com",
                    Config::UC_HOST // real uc
                )
            );
            list(, $err) = $conf->getRsHostV2($this->accessKey, $this->bucketName);
            $this->assertNull($err);
        }
    }
}
