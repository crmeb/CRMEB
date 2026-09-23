<?php

namespace Qiniu;

final class Config
{
    const SDK_VER = '7.14.0';

    const BLOCK_SIZE = 4194304; //4*1024*1024 分块上传块大小，该参数为接口规格，不能修改

    const RSF_HOST = 'rsf.qiniuapi.com';
    const API_HOST = 'api.qiniuapi.com';
    const RS_HOST = 'rs.qiniuapi.com'; // RS Host
    const UC_HOST = 'uc.qiniuapi.com'; // UC Host
    const QUERY_REGION_HOST = Config::UC_HOST;
    const RTCAPI_HOST = 'http://rtc.qiniuapi.com';
    const ARGUS_HOST = 'ai.qiniuapi.com';
    const CASTER_HOST = 'pili-caster.qiniuapi.com';
    const SMS_HOST = "https://sms.qiniuapi.com";
    const RTCAPI_VERSION = 'v3';
    const SMS_VERSION = 'v1';

    // Zone 空间对应的存储区域
    public $region;
    //BOOL 是否使用https域名
    public $useHTTPS;
    //BOOL 是否使用CDN加速上传域名
    public $useCdnDomains;
    /**
     * @var Region
     */
    public $zone;
    // Zone Cache
    private $regionCache;
    // UC Host
    private $ucHost;
    private $queryRegionHost;
    // backup UC Hosts
    private $backupQueryRegionHosts;
    // backup UC Hosts max retry time
    public $backupUcHostsRetryTimes;

    // 构造函数
    public function __construct(?Region $z = null)
    {
        $this->zone = $z;
        $this->useHTTPS = false;
        $this->useCdnDomains = false;
        $this->regionCache = array();
        $this->ucHost = Config::UC_HOST;
        $this->queryRegionHost = Config::QUERY_REGION_HOST;
        $this->backupQueryRegionHosts = array(
            "kodo-config.qiniuapi.com",
            "uc.qbox.me",
        );
        $this->backupUcHostsRetryTimes = 2;
    }

    public function setUcHost($ucHost)
    {
        $this->ucHost = $ucHost;
        $this->setQueryRegionHost($ucHost);
    }

    public function getUcHost()
    {
        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return $scheme . $this->ucHost;
    }

    public function setQueryRegionHost($host, $backupHosts = array())
    {
        $this->queryRegionHost = $host;
        $this->backupQueryRegionHosts = $backupHosts;
    }

    public function getQueryRegionHost()
    {
        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return $scheme . $this->queryRegionHost;
    }

    public function setBackupQueryRegionHosts($hosts = array())
    {
        $this->backupQueryRegionHosts = $hosts;
    }

    public function getBackupQueryRegionHosts()
    {
        return $this->backupQueryRegionHosts;
    }

    public function getUpHost($accessKey, $bucket, $reqOpt = null)
    {
        $region = $this->getRegion($accessKey, $bucket, $reqOpt);
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

    public function getUpHostV2($accessKey, $bucket, $reqOpt = null)
    {
        list($region, $err) = $this->getRegionV2($accessKey, $bucket, $reqOpt);
        if ($err != null) {
            return array(null, $err);
        }

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        $host = $region->srcUpHosts[0];
        if ($this->useCdnDomains === true) {
            $host = $region->cdnUpHosts[0];
        }

        return array($scheme . $host, null);
    }

    public function getUpBackupHost($accessKey, $bucket, $reqOpt = null)
    {
        $region = $this->getRegion($accessKey, $bucket, $reqOpt);
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

    public function getUpBackupHostV2($accessKey, $bucket, $reqOpt = null)
    {
        list($region, $err) = $this->getRegionV2($accessKey, $bucket, $reqOpt);
        if ($err != null) {
            return array(null, $err);
        }

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        $host = $region->cdnUpHosts[0];
        if ($this->useCdnDomains === true) {
            $host = $region->srcUpHosts[0];
        }

        return array($scheme . $host, null);
    }

    public function getRsHost($accessKey, $bucket, $reqOpt = null)
    {
        $region = $this->getRegion($accessKey, $bucket, $reqOpt);

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return $scheme . $region->rsHost;
    }

    public function getRsHostV2($accessKey, $bucket, $reqOpt = null)
    {
        list($region, $err) = $this->getRegionV2($accessKey, $bucket, $reqOpt);
        if ($err != null) {
            return array(null, $err);
        }

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return array($scheme . $region->rsHost, null);
    }

    public function getRsfHost($accessKey, $bucket, $reqOpt = null)
    {
        $region = $this->getRegion($accessKey, $bucket, $reqOpt);

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return $scheme . $region->rsfHost;
    }

    public function getRsfHostV2($accessKey, $bucket, $reqOpt = null)
    {
        list($region, $err) = $this->getRegionV2($accessKey, $bucket, $reqOpt);
        if ($err != null) {
            return array(null, $err);
        }

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return array($scheme . $region->rsfHost, null);
    }

    public function getIovipHost($accessKey, $bucket, $reqOpt = null)
    {
        $region = $this->getRegion($accessKey, $bucket, $reqOpt);

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return $scheme . $region->iovipHost;
    }

    public function getIovipHostV2($accessKey, $bucket, $reqOpt = null)
    {
        list($region, $err) = $this->getRegionV2($accessKey, $bucket, $reqOpt);
        if ($err != null) {
            return array(null, $err);
        }

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return array($scheme . $region->iovipHost, null);
    }

    public function getApiHost($accessKey, $bucket, $reqOpt = null)
    {
        $region = $this->getRegion($accessKey, $bucket, $reqOpt);

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return $scheme . $region->apiHost;
    }

    public function getApiHostV2($accessKey, $bucket, $reqOpt = null)
    {
        list($region, $err) = $this->getRegionV2($accessKey, $bucket, $reqOpt);
        if ($err != null) {
            return array(null, $err);
        }

        if ($this->useHTTPS === true) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }

        return array($scheme . $region->apiHost, null);
    }


    /**
     * 从缓存中获取区域
     *
     * @param string $cacheId 缓存 ID
     * @return null|Region
     */
    private function getRegionCache($cacheId)
    {
        if (isset($this->regionCache[$cacheId]) &&
            isset($this->regionCache[$cacheId]["deadline"]) &&
            time() < $this->regionCache[$cacheId]["deadline"]) {
            return $this->regionCache[$cacheId]["region"];
        }

        return null;
    }

    /**
     * 将区域设置到缓存中
     *
     * @param string $cacheId 缓存 ID
     * @param Region $region 缓存 ID
     * @return void
     */
    private function setRegionCache($cacheId, $region)
    {
        $this->regionCache[$cacheId] = array(
            "region" => $region,
        );
        if (isset($region->ttl)) {
            $this->regionCache[$cacheId]["deadline"] = time() + $region->ttl;
        }
    }

    /**
     * 从缓存中获取区域
     *
     * @param string $accessKey
     * @param string $bucket
     * @return Region
     *
     * @throws \Exception
     */
    private function getRegion($accessKey, $bucket, $reqOpt = null)
    {
        if (isset($this->zone)) {
            return $this->zone;
        }

        $cacheId = "$accessKey:$bucket";
        $regionCache = $this->getRegionCache($cacheId);
        if ($regionCache) {
            return $regionCache;
        }

        $region = Zone::queryZone(
            $accessKey,
            $bucket,
            $this->getQueryRegionHost(),
            $this->getBackupQueryRegionHosts(),
            $this->backupUcHostsRetryTimes,
            $reqOpt
        );
        if (is_array($region)) {
            list($region, $err) = $region;
            if ($err != null) {
                throw new \Exception($err->message());
            }
        }

        $this->setRegionCache($cacheId, $region);
        return $region;
    }

    private function getRegionV2($accessKey, $bucket, $reqOpt = null)
    {
        if (isset($this->zone)) {
            return array($this->zone, null);
        }

        $cacheId = "$accessKey:$bucket";
        $regionCache = $this->getRegionCache($cacheId);
        if (isset($regionCache)) {
            return array($regionCache, null);
        }

        $region = Zone::queryZone(
            $accessKey,
            $bucket,
            $this->getQueryRegionHost(),
            $this->getBackupQueryRegionHosts(),
            $this->backupUcHostsRetryTimes,
            $reqOpt
        );
        if (is_array($region)) {
            list($region, $err) = $region;
            return array($region, $err);
        }

        $this->setRegionCache($cacheId, $region);
        return array($region, null);
    }
}
