<?php
namespace Qiniu\Tests;

use PHPUnit\Framework\TestCase;

use Qiniu;
use Qiniu\Zone;

class ZoneTest extends TestCase
{
    protected $zone;
    protected $zoneHttps;
    protected $ak;

    protected $bucketName;
    protected $bucketNameBC;
    protected $bucketNameNA;
    protected $bucketNameFS;
    protected $bucketNameAS;


    /**
     * @before
     */
    protected function setUpZoneAndBucket()
    {
        global $bucketName;
        $this->bucketName = $bucketName;

        global $bucketNameBC;
        $this->bucketNameBC = $bucketNameBC;

        global $bucketNameNA;
        $this->bucketNameNA = $bucketNameNA;

        global $bucketNameFS;
        $this->bucketNameFS = $bucketNameFS;

        global $bucketNameAS;
        $this->bucketNameAS = $bucketNameAS;

        global $accessKey;
        $this->ak = $accessKey;

        $this->zone = new Zone();
        $this->zoneHttps = new Zone('https');
    }

    public function testUpHosts()
    {
        list($ret, $err) = Zone::queryZone($this->ak, 'fakebucket');
        $this->assertNull($ret);
        $this->assertNotNull($err);

        $zone = Zone::queryZone($this->ak, $this->bucketName);
        $this->assertContains('upload.qiniup.com', $zone->cdnUpHosts);

        $zone = Zone::queryZone($this->ak, $this->bucketNameBC);
        $this->assertContains('upload-z1.qiniup.com', $zone->cdnUpHosts);

        $zone = Zone::queryZone($this->ak, $this->bucketNameFS);
        $this->assertContains('upload-z2.qiniup.com', $zone->cdnUpHosts);

        $zone = Zone::queryZone($this->ak, $this->bucketNameNA);
        $this->assertContains('upload-na0.qiniup.com', $zone->cdnUpHosts);

        $zone = Zone::queryZone($this->ak, $this->bucketNameAS);
        $this->assertContains('upload-as0.qiniup.com', $zone->cdnUpHosts);
    }

    public function testIoHosts()
    {
        $zone = Zone::queryZone($this->ak, $this->bucketName);
        $this->assertEquals($zone->iovipHost, 'iovip.qbox.me');

        $zone = Zone::queryZone($this->ak, $this->bucketNameBC);
        $this->assertEquals($zone->iovipHost, 'iovip-z1.qbox.me');

        $zone = Zone::queryZone($this->ak, $this->bucketNameFS);
        $this->assertEquals($zone->iovipHost, 'iovip-z2.qbox.me');

        $zone = Zone::queryZone($this->ak, $this->bucketNameNA);
        $this->assertEquals($zone->iovipHost, 'iovip-na0.qbox.me');

        $zone = Zone::queryZone($this->ak, $this->bucketNameAS);
        $this->assertEquals($zone->iovipHost, 'iovip-as0.qbox.me');
    }

    public function testZonez0()
    {
        $zone = Zone::zonez0();
        $this->assertContains('upload.qiniup.com', $zone->cdnUpHosts);
    }

    public function testZonez1()
    {
        $zone = Zone::zonez1();
        $this->assertContains('upload-z1.qiniup.com', $zone->cdnUpHosts);
    }

    public function testZonez2()
    {
        $zone = Zone::zonez2();
        $this->assertContains('upload-z2.qiniup.com', $zone->cdnUpHosts);
    }

    public function testZoneCnEast2()
    {
        $zone = Zone::zoneCnEast2();
        $this->assertContains('upload-cn-east-2.qiniup.com', $zone->cdnUpHosts);
    }

    public function testZoneNa0()
    {
        $zone = Zone::zoneNa0();
        $this->assertContains('upload-na0.qiniup.com', $zone->cdnUpHosts);
    }

    public function testZoneAs0()
    {
        $zone = Zone::zoneAs0();
        $this->assertContains('upload-as0.qiniup.com', $zone->cdnUpHosts);
    }

    public function testQvmZonez0()
    {
        $zone = Zone::qvmZonez0();
        $this->assertContains('free-qvm-z0-xs.qiniup.com', $zone->srcUpHosts);
    }

    public function testQvmZonez1()
    {
        $zone = Zone::qvmZonez1();
        $this->assertContains('free-qvm-z1-zz.qiniup.com', $zone->srcUpHosts);
    }
}
