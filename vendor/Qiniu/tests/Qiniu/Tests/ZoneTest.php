<?php
namespace Qiniu\Tests;

use Qiniu;
use Qiniu\Zone;

class ZoneTest extends \PHPUnit_Framework_TestCase
{
    protected $zone;
    protected $zoneHttps;
    protected $ak;

    protected $bucketName;
    protected $bucketNameBC;
    protected $bucketNameNA;


    protected function setUp()
    {
        global $bucketName;
        $this->bucketName = $bucketName;

        global $bucketNameBC;
        $this->bucketNameBC = $bucketNameBC;

        global $bucketNameNA;
        $this->bucketNameNA = $bucketNameNA;

        global $accessKey;
        $this->ak = $accessKey;

        $this->zone = new Zone();
        $this->zoneHttps = new Zone('https');
    }

    public function testUpHosts()
    {
        $zone = Zone::queryZone($this->ak, $this->bucketName);
        $this->assertContains('upload.qiniup.com', $zone->cdnUpHosts);

        $zone = Zone::queryZone($this->ak, $this->bucketNameBC);
        $this->assertContains('upload-z1.qiniup.com', $zone->cdnUpHosts);

        $zone = Zone::queryZone($this->ak, $this->bucketNameNA);
        $this->assertContains('upload-na0.qiniup.com', $zone->cdnUpHosts);
    }

    public function testIoHosts()
    {
        $zone = Zone::queryZone($this->ak, $this->bucketName);
        $this->assertEquals($zone->iovipHost, 'iovip.qbox.me');

        $zone = Zone::queryZone($this->ak, $this->bucketNameBC);
        $this->assertEquals($zone->iovipHost, 'iovip-z1.qbox.me');

        $zone = Zone::queryZone($this->ak, $this->bucketNameNA);
        $this->assertEquals($zone->iovipHost, 'iovip-na0.qbox.me');
    }
}
