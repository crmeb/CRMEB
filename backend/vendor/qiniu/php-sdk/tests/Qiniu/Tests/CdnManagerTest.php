<?php
/**
 * Created by IntelliJ IDEA.
 * User: wf
 * Date: 2017/6/21
 * Time: AM8:46
 */

namespace Qiniu\Tests;

use PHPUnit\Framework\TestCase;

use Qiniu\Cdn\CdnManager;
use Qiniu\Http\Client;

class CdnManagerTest extends TestCase
{
    protected $cdnManager;
    protected $encryptKey;
    protected $testStartDate;
    protected $testEndDate;
    protected $testGranularity;
    protected $testLogDate;
    protected $refreshUrl;
    protected $refreshDirs;
    protected $customDomain;
    protected $customDomain2;

    /**
     * @before
     */
    protected function setUpCdnManager()
    {
        global $testAuth;
        $this->cdnManager = new CdnManager($testAuth);

        global $timestampAntiLeechEncryptKey;
        $this->encryptKey = $timestampAntiLeechEncryptKey;

        global $testStartDate;
        $this->testStartDate = $testStartDate;

        global $testEndDate;
        $this->testEndDate = $testEndDate;

        global $testGranularity;
        $this->testGranularity = $testGranularity;

        global $testLogDate;
        $this->testLogDate = $testLogDate;

        global $customDomain;
        $this->refreshUrl = $customDomain . '/sdktest.png';
        $this->refreshDirs = $customDomain;
        $this->customDomain = $customDomain;

        global $customDomain2;
        $this->customDomain2 = $customDomain2;
    }

    public function testRefreshUrls()
    {
        list($ret, $err) = $this->cdnManager->refreshUrls(array($this->refreshUrl));
        $this->assertNull($err);
        $this->assertNotNull($ret);
    }

    public function testRefreshDirs()
    {
        list($ret, $err) = $this->cdnManager->refreshDirs(array($this->refreshDirs));
        $this->assertNull($err);
        $this->assertNotNull($ret);
    }

    public function testRefreshUrlsAndDirs()
    {
        list($ret, $err) = $this->cdnManager->refreshUrlsAndDirs(array($this->refreshUrl), array($this->refreshDirs));
        $this->assertNull($err);
        $this->assertNotNull($ret);
    }

    public function testGetCdnRefreshList()
    {
        list($ret, $err) = $this->cdnManager->getCdnRefreshList(null, null, null, 'success');
        $this->assertNull($err);
        $this->assertNotNull($ret);
    }

    public function testPrefetchUrls()
    {
        list($ret, $err) = $this->cdnManager->prefetchUrls(array($this->refreshUrl));
        $this->assertNull($err);
        $this->assertNotNull($ret);
    }

    public function testGetCdnPrefetchList()
    {
        list($ret, $err) = $this->cdnManager->getCdnPrefetchList(null, null, 'success');
        $this->assertNull($err);
        $this->assertNotNull($ret);
    }

    public function testGetBandwidthData()
    {
        list($ret, $err) = $this->cdnManager->getBandwidthData(
            array($this->customDomain2),
            $this->testStartDate,
            $this->testEndDate,
            $this->testGranularity
        );
        $this->assertNull($err);
        $this->assertNotNull($ret);
    }

    public function testGetFluxData()
    {
        list($ret, $err) = $this->cdnManager->getFluxData(
            array($this->customDomain2),
            $this->testStartDate,
            $this->testEndDate,
            $this->testGranularity
        );
        $this->assertNull($err);
        $this->assertNotNull($ret);
    }

    public function testGetCdnLogList()
    {
        $domain = getenv('QINIU_TEST_DOMAIN');
        list($ret, $err) = $this->cdnManager->getCdnLogList(array($domain), $this->testLogDate);
        $this->assertNull($err);
        $this->assertNotNull($ret);
    }

    public function testCreateTimestampAntiLeechUrl()
    {
        $signUrl = $this->cdnManager->createTimestampAntiLeechUrl($this->refreshUrl, $this->encryptKey, 3600);
        $response = Client::get($signUrl);
        $this->assertNull($response->error);
        $this->assertEquals($response->statusCode, 200);

        $signUrl = $this->cdnManager->createTimestampAntiLeechUrl(
            $this->refreshUrl . '?qiniu',
            $this->encryptKey,
            3600
        );
        $response = Client::get($signUrl);
        $this->assertNull($response->error);
        $this->assertEquals($response->statusCode, 200);
    }
}
