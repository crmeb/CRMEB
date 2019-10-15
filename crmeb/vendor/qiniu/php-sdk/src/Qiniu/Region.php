<?php
namespace Qiniu;

use Qiniu\Http\Client;
use Qiniu\Http\Error;

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

    //构造一个Region对象
    public function __construct(
        $srcUpHosts = array(),
        $cdnUpHosts = array(),
        $rsHost = "rs.qiniu.com",
        $rsfHost = "rsf.qiniu.com",
        $apiHost = "api.qiniu.com",
        $iovipHost = null
    ) {

        $this->srcUpHosts = $srcUpHosts;
        $this->cdnUpHosts = $cdnUpHosts;
        $this->rsHost = $rsHost;
        $this->rsfHost = $rsfHost;
        $this->apiHost = $apiHost;
        $this->iovipHost = $iovipHost;
    }

    //华东机房
    public static function regionHuadong()
    {
        $regionHuadong = new Region(
            array("up.qiniup.com", 'up-jjh.qiniup.com', 'up-xs.qiniup.com'),
            array('upload.qiniup.com', 'upload-jjh.qiniup.com', 'upload-xs.qiniup.com'),
            'rs.qbox.me',
            'rsf.qbox.me',
            'api.qiniu.com',
            'iovip.qbox.me'
        );
        return $regionHuadong;
    }

    //华东机房内网上传
    public static function qvmRegionHuadong()
    {
        $qvmRegionHuadong = new Region(
            array("free-qvm-z0-xs.qiniup.com"),
            'rs.qbox.me',
            'rsf.qbox.me',
            'api.qiniu.com',
            'iovip.qbox.me'
        );
        return $qvmRegionHuadong;
    }

    //华北机房内网上传
    public static function qvmRegionHuabei()
    {
        $qvmRegionHuabei = new Region(
            array("free-qvm-z1-zz.qiniup.com"),
            "rs-z1.qbox.me",
            "rsf-z1.qbox.me",
            "api-z1.qiniu.com",
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
            "rs-z1.qbox.me",
            "rsf-z1.qbox.me",
            "api-z1.qiniu.com",
            "iovip-z1.qbox.me"
        );

        return $regionHuabei;
    }

    //华南机房
    public static function regionHuanan()
    {
        $regionHuanan = new Region(
            array('up-z2.qiniup.com', 'up-dg.qiniup.com', 'up-fs.qiniup.com'),
            array('upload-z2.qiniup.com', 'upload-dg.qiniup.com', 'upload-fs.qiniup.com'),
            "rs-z2.qbox.me",
            "rsf-z2.qbox.me",
            "api-z2.qiniu.com",
            "iovip-z2.qbox.me"
        );
        return $regionHuanan;
    }

    //北美机房
    public static function regionNorthAmerica()
    {
        //北美机房
        $regionNorthAmerica = new Region(
            array('up-na0.qiniup.com'),
            array('upload-na0.qiniup.com'),
            "rs-na0.qbox.me",
            "rsf-na0.qbox.me",
            "api-na0.qiniu.com",
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
            "rs-as0.qbox.me",
            "rsf-as0.qbox.me",
            "api-as0.qiniu.com",
            "iovip-as0.qbox.me"
        );
        return $regionSingapore;
    }

    /*
     * GET /v2/query?ak=<ak>&&bucket=<bucket>
     **/
    public static function queryRegion($ak, $bucket)
    {
        $Region = new Region();
        $url = Config::API_HOST . '/v2/query' . "?ak=$ak&bucket=$bucket";
        $ret = Client::Get($url);
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        $r = ($ret->body === null) ? array() : $ret->json();
        //parse Region;

        $iovipHost = $r['io']['src']['main'][0];
        $Region->iovipHost = $iovipHost;
        $accMain = $r['up']['acc']['main'][0];
        array_push($Region->cdnUpHosts, $accMain);
        if (isset($r['up']['acc']['backup'])) {
            foreach ($r['up']['acc']['backup'] as $key => $value) {
                array_push($Region->cdnUpHosts, $value);
            }
        }
        $srcMain = $r['up']['src']['main'][0];
        array_push($Region->srcUpHosts, $srcMain);
        if (isset($r['up']['src']['backup'])) {
            foreach ($r['up']['src']['backup'] as $key => $value) {
                array_push($Region->srcUpHosts, $value);
            }
        }

        //set specific hosts
        if (strstr($Region->iovipHost, "z1") !== false) {
            $Region->rsHost = "rs-z1.qbox.me";
            $Region->rsfHost = "rsf-z1.qbox.me";
            $Region->apiHost = "api-z1.qiniu.com";
        } elseif (strstr($Region->iovipHost, "z2") !== false) {
            $Region->rsHost = "rs-z2.qbox.me";
            $Region->rsfHost = "rsf-z2.qbox.me";
            $Region->apiHost = "api-z2.qiniu.com";
        } elseif (strstr($Region->iovipHost, "na0") !== false) {
            $Region->rsHost = "rs-na0.qbox.me";
            $Region->rsfHost = "rsf-na0.qbox.me";
            $Region->apiHost = "api-na0.qiniu.com";
        } elseif (strstr($Region->iovipHost, "as0") !== false) {
            $Region->rsHost = "rs-as0.qbox.me";
            $Region->rsfHost = "rsf-as0.qbox.me";
            $Region->apiHost = "api-as0.qiniu.com";
        } else {
            $Region->rsHost = "rs.qbox.me";
            $Region->rsfHost = "rsf.qbox.me";
            $Region->apiHost = "api.qiniu.com";
        }

        return $Region;
    }
}
