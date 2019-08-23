<?php
namespace Qiniu;

final class Config
{
    const SDK_VER = '7.2.5';

    const BLOCK_SIZE = 4194304; //4*1024*1024 分块上传块大小，该参数为接口规格，不能修改

    const RSF_HOST = 'rsf.qiniu.com';
    const API_HOST = 'api.qiniu.com';
    const RS_HOST = 'rs.qiniu.com';      //RS Host
    const UC_HOST = 'https://api.qiniu.com';              //UC Host
    const RTCAPI_HOST = 'http://rtc.qiniuapi.com';
    const RTCAPI_VERSION = 'v3';

    // Zone 空间对应的机房
    public $zone;
    //BOOL 是否使用https域名
    public $useHTTPS;
    //BOOL 是否使用CDN加速上传域名
    public $useCdnDomains;
    // Zone Cache
    private $zoneCache;

    // 构造函数
    public function __construct(Zone $z = null)
    {
        $this->zone = $z;
        $this->useHTTPS = false;
        $this->useCdnDomains = false;
        $this->zoneCache = array();
    }

    public function getUpHost($accessKey, $bucket)
    {
        $zone = $this->getZone($accessKey, $bucket);
        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        $host = $zone->srcUpHosts[0];
        if ($this->useCdnDomains === true) {
            $host = $zone->cdnUpHosts[0];
        }

        return $scheme . $host;
    }

    public function getUpBackupHost($accessKey, $bucket)
    {
        $zone = $this->getZone($accessKey, $bucket);
        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        $host = $zone->cdnUpHosts[0];
        if ($this->useCdnDomains === true) {
            $host = $zone->srcUpHosts[0];
        }

        return $scheme . $host;
    }

    public function getRsHost($accessKey, $bucket)
    {
        $zone = $this->getZone($accessKey, $bucket);

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return $scheme . $zone->rsHost;
    }

    public function getRsfHost($accessKey, $bucket)
    {
        $zone = $this->getZone($accessKey, $bucket);

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return $scheme . $zone->rsfHost;
    }

    public function getIovipHost($accessKey, $bucket)
    {
        $zone = $this->getZone($accessKey, $bucket);

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return $scheme . $zone->iovipHost;
    }

    public function getApiHost($accessKey, $bucket)
    {
        $zone = $this->getZone($accessKey, $bucket);

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return $scheme . $zone->apiHost;
    }

    private function getZone($accessKey, $bucket)
    {
        $cacheId = "$accessKey:$bucket";

        if (isset($this->zoneCache[$cacheId])) {
            $zone = $this->zoneCache[$cacheId];
        } elseif (isset($this->zone)) {
            $zone = $this->zone;
            $this->zoneCache[$cacheId] = $zone;
        } else {
            $zone = Zone::queryZone($accessKey, $bucket);
            $this->zoneCache[$cacheId] = $zone;
        }
        return $zone;
    }
}
