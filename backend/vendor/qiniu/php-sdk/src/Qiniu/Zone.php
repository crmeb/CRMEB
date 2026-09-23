<?php
namespace Qiniu;

use Qiniu\Region;

class Zone extends Region
{
    public static function zonez0()
    {
        return parent::regionHuadong();
    }

    public static function zonez1()
    {
        return parent::regionHuabei();
    }

    public static function zonez2()
    {
        return parent::regionHuanan();
    }

    public static function zoneCnEast2()
    {
        return parent::regionHuadong2();
    }

    public static function zoneAs0()
    {
        return parent::regionSingapore();
    }

    public static function zoneNa0()
    {
        return parent::regionNorthAmerica();
    }

    public static function qvmZonez0()
    {
        return parent::qvmRegionHuadong();
    }

    public static function qvmZonez1()
    {
        return parent::qvmRegionHuabei();
    }

    public static function queryZone(
        $ak,
        $bucket,
        $ucHost = null,
        $backupUcHosts = array(),
        $retryTimes = 2,
        $reqOpt = null
    ) {
        return parent::queryRegion($ak, $bucket, $ucHost, $backupUcHosts, $retryTimes, $reqOpt);
    }
}
