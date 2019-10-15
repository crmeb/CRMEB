<?php
namespace Qiniu;

final class Config
{
    const SDK_VER = '7.2.9';

    const BLOCK_SIZE = 4194304; //4*1024*1024 分块上传块大小，该参数为接口规格，不能修改

    const RSF_HOST = 'rsf.qiniu.com';
    const API_HOST = 'api.qiniu.com';
    const RS_HOST = 'rs.qiniu.com';      //RS Host
    const UC_HOST = 'uc.qbox.me';              //UC Host
    const RTCAPI_HOST = 'http://rtc.qiniuapi.com';
    const ARGUS_HOST = 'argus.atlab.ai';
    const CASTER_HOST = 'pili-caster.qiniuapi.com';
    const SMS_HOST="https://sms.qiniuapi.com";
    const RTCAPI_VERSION = 'v3';
    const SMS_VERSION='v1';

    // Zone 空间对应的存储区域
    public $region;
    //BOOL 是否使用https域名
    public $useHTTPS;
    //BOOL 是否使用CDN加速上传域名
    public $useCdnDomains;
    // Zone Cache
    private $regionCache;

    // 构造函数
    public function __construct(Region $z = null)
    {
        $this->zone = $z;
        $this->useHTTPS = false;
        $this->useCdnDomains = false;
        $this->regionCache = array();
    }

    public function getUpHost($accessKey, $bucket)
    {
        $region = $this->getRegion($accessKey, $bucket);
        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        $host = $region->srcUpHosts[0];
        if ($this->useCdnDomains === true) {
            $host = $region->cdnUpHosts[0];
        }

        return $scheme . $host;
    }

    public function getUpBackupHost($accessKey, $bucket)
    {
        $region = $this->getRegion($accessKey, $bucket);
        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        $host = $region->cdnUpHosts[0];
        if ($this->useCdnDomains === true) {
            $host = $region->srcUpHosts[0];
        }

        return $scheme . $host;
    }

    public function getRsHost($accessKey, $bucket)
    {
        $region = $this->getRegion($accessKey, $bucket);

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return $scheme . $region->rsHost;
    }

    public function getRsfHost($accessKey, $bucket)
    {
        $region = $this->getRegion($accessKey, $bucket);

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return $scheme . $region->rsfHost;
    }

    public function getIovipHost($accessKey, $bucket)
    {
        $region = $this->getRegion($accessKey, $bucket);

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return $scheme . $region->iovipHost;
    }

    public function getApiHost($accessKey, $bucket)
    {
        $region = $this->getRegion($accessKey, $bucket);

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return $scheme . $region->apiHost;
    }

    private function getRegion($accessKey, $bucket)
    {
        $cacheId = "$accessKey:$bucket";

        if (isset($this->regionCache[$cacheId])) {
            $region = $this->regionCache[$cacheId];
        } elseif (isset($this->zone)) {
            $region = $this->zone;
            $this->regionCache[$cacheId] = $region;
        } else {
            $region = Zone::queryZone($accessKey, $bucket);
            $this->regionCache[$cacheId] = $region;
        }
        return $region;
    }
}
