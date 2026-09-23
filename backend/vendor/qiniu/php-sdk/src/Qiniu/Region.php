<?php

namespace Qiniu;

use Qiniu\Http\Client;
use Qiniu\Http\Error;
use Qiniu\Http\Middleware\RetryDomainsMiddleware;
use Qiniu\Http\RequestOptions;

class Region
{

    //源站上传域名
    public $srcUpHosts;
    //CDN加速上传域名
    public $cdnUpHosts;
    //资源管理域名
    public $rsHost;
    //资源列举域名
    public $rsfHost;
    //资源处理域名
    public $apiHost;
    //IOVIP域名
    public $iovipHost;
    // TTL
    public $ttl;

    //构造一个Region对象
    public function __construct(
        $srcUpHosts = array(),
        $cdnUpHosts = array(),
        $rsHost = "rs-z0.qiniuapi.com",
        $rsfHost = "rsf-z0.qiniuapi.com",
        $apiHost = "api.qiniuapi.com",
        $iovipHost = null,
        $ttl = null
    ) {

        $this->srcUpHosts = $srcUpHosts;
        $this->cdnUpHosts = $cdnUpHosts;
        $this->rsHost = $rsHost;
        $this->rsfHost = $rsfHost;
        $this->apiHost = $apiHost;
        $this->iovipHost = $iovipHost;
        $this->ttl = $ttl;
    }

    //华东机房
    public static function regionHuadong()
    {
        $regionHuadong = new Region(
            array("up.qiniup.com"),
            array('upload.qiniup.com'),
            'rs-z0.qiniuapi.com',
            'rsf-z0.qiniuapi.com',
            'api.qiniuapi.com',
            'iovip.qbox.me'
        );
        return $regionHuadong;
    }

    //华东机房内网上传
    public static function qvmRegionHuadong()
    {
        $qvmRegionHuadong = new Region(
            array("free-qvm-z0-xs.qiniup.com"),
            'rs-z0.qiniuapi.com',
            'rsf-z0.qiniuapi.com',
            'api.qiniuapi.com',
            'iovip.qbox.me'
        );
        return $qvmRegionHuadong;
    }

    //华北机房内网上传
    public static function qvmRegionHuabei()
    {
        $qvmRegionHuabei = new Region(
            array("free-qvm-z1-zz.qiniup.com"),
            "rs-z1.qiniuapi.com",
            "rsf-z1.qiniuapi.com",
            "api-z1.qiniuapi.com",
            "iovip-z1.qbox.me"
        );
        return $qvmRegionHuabei;
    }

    //华北机房
    public static function regionHuabei()
    {
        $regionHuabei = new Region(
            array('up-z1.qiniup.com'),
            array('upload-z1.qiniup.com'),
            "rs-z1.qiniuapi.com",
            "rsf-z1.qiniuapi.com",
            "api-z1.qiniuapi.com",
            "iovip-z1.qbox.me"
        );

        return $regionHuabei;
    }

    //华南机房
    public static function regionHuanan()
    {
        $regionHuanan = new Region(
            array('up-z2.qiniup.com'),
            array('upload-z2.qiniup.com'),
            "rs-z2.qiniuapi.com",
            "rsf-z2.qiniuapi.com",
            "api-z2.qiniuapi.com",
            "iovip-z2.qbox.me"
        );
        return $regionHuanan;
    }

    //华东2 机房
    public static function regionHuadong2()
    {
        return new Region(
            array('up-cn-east-2.qiniup.com'),
            array('upload-cn-east-2.qiniup.com'),
            "rs-cn-east-2.qiniuapi.com",
            "rsf-cn-east-2.qiniuapi.com",
            "api-cn-east-2.qiniuapi.com",
            "iovip-cn-east-2.qiniuio.com"
        );
    }

    //北美机房
    public static function regionNorthAmerica()
    {
        //北美机房
        $regionNorthAmerica = new Region(
            array('up-na0.qiniup.com'),
            array('upload-na0.qiniup.com'),
            "rs-na0.qiniuapi.com",
            "rsf-na0.qiniuapi.com",
            "api-na0.qiniuapi.com",
            "iovip-na0.qbox.me"
        );
        return $regionNorthAmerica;
    }

    //新加坡机房
    public static function regionSingapore()
    {
        //新加坡机房
        $regionSingapore = new Region(
            array('up-as0.qiniup.com'),
            array('upload-as0.qiniup.com'),
            "rs-as0.qiniuapi.com",
            "rsf-as0.qiniuapi.com",
            "api-as0.qiniuapi.com",
            "iovip-as0.qbox.me"
        );
        return $regionSingapore;
    }

    /*
     * GET /v4/query?ak=<ak>&bucket=<bucket>
     * @param string $ak
     * @param string $bucket
     * @param string $ucHost|null
     * @param array $backupUcHosts
     * @param int $retryTimes
     * @param RequestOptions|null $reqOpt
     * @return Response
     **/
    public static function queryRegion(
        $ak,
        $bucket,
        $ucHost = null,
        $backupUcHosts = array(),
        $retryTimes = 2,
        $reqOpt = null
    ) {
        $region = new Region();
        if (!$ucHost) {
            $ucHost = "https://" . Config::QUERY_REGION_HOST;
        }
        $url = $ucHost . '/v4/query' . "?ak=$ak&bucket=$bucket";
        if ($reqOpt == null) {
            $reqOpt = new RequestOptions();
        }
        $reqOpt->middlewares = array(
            new RetryDomainsMiddleware(
                $backupUcHosts,
                $retryTimes
            )
        );
        $ret = Client::get($url, array(), $reqOpt);
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        $r = ($ret->body === null) ? array() : $ret->json();
        if (!is_array($r["hosts"]) || count($r["hosts"]) == 0) {
            return array(null, new Error($url, $ret));
        }

        // parse region;
        $regionHost = $r["hosts"][0];
        $region->cdnUpHosts = array_merge($region->cdnUpHosts, $regionHost['up']['domains']);
        $region->srcUpHosts = array_merge($region->srcUpHosts, $regionHost['up']['domains']);

        // set specific hosts
        $region->iovipHost = $regionHost['io']['domains'][0];
        if (isset($regionHost['rs']['domains']) && count($regionHost['rs']['domains']) > 0) {
            $region->rsHost = $regionHost['rs']['domains'][0];
        } else {
            $region->rsHost = Config::RS_HOST;
        }
        if (isset($regionHost['rsf']['domains']) && count($regionHost['rsf']['domains']) > 0) {
            $region->rsfHost = $regionHost['rsf']['domains'][0];
        } else {
            $region->rsfHost = Config::RSF_HOST;
        }
        if (isset($regionHost['api']['domains']) && count($regionHost['api']['domains']) > 0) {
            $region->apiHost = $regionHost['api']['domains'][0];
        } else {
            $region->apiHost = Config::API_HOST;
        }

        // set ttl
        $region->ttl = $regionHost['ttl'];

        return $region;
    }
}
