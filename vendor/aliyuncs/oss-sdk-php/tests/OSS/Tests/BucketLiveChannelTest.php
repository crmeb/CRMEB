<?php

namespace OSS\Tests;

require_once __DIR__ . '/Common.php';

use OSS\Model\LiveChannelConfig;
use OSS\Core\OssException;

class BucketLiveChannelTest extends \PHPUnit_Framework_TestCase
{
    private $bucketName;
    private $client;

    public function setUp()
    {
        $this->client = Common::getOssClient();
        $this->bucketName = 'php-sdk-test-rtmp-bucket-name-' . strval(rand(0, 10000));
        $this->client->createBucket($this->bucketName);
        Common::waitMetaSync();
    }

    public function tearDown()
    {
    ////to delete created bucket
    //1. delele live channel
        $list = $this->client->listBucketLiveChannels($this->bucketName);
        if (count($list->getChannelList()) != 0)
        {
            foreach($list->getChannelList() as $list)
            {
                $this->client->deleteBucketLiveChannel($this->bucketName, $list->getName());
            }
        }
    //2. delete exsited object
        $prefix = 'live-test/';
        $delimiter = '/';
        $nextMarker = '';
        $maxkeys = 1000;
        $options = array(
            'delimiter' => $delimiter,
            'prefix' => $prefix,
            'max-keys' => $maxkeys,
            'marker' => $nextMarker,
        );

        try {
            $listObjectInfo = $this->client->listObjects($this->bucketName, $options);
        } catch (OssException $e) {
            printf($e->getMessage() . "\n");
            return;
        }

        $objectList = $listObjectInfo->getObjectList(); // 文件列表
        if (!empty($objectList))
        {   
            foreach($objectList as $objectInfo)
                $this->client->deleteObject($this->bucketName, $objectInfo->getKey());     
        }
    //3. delete the bucket
        $this->client->deleteBucket($this->bucketName);
    }

    public function testPutLiveChannel()
    {
        $config = new LiveChannelConfig(array(
            'description' => 'live channel 1',
            'type' => 'HLS',
            'fragDuration' => 10,
            'fragCount' => 5,
            'playListName' => 'hello.m3u8'
        ));
        $info = $this->client->putBucketLiveChannel($this->bucketName, 'live-1', $config);
        $this->client->deleteBucketLiveChannel($this->bucketName, 'live-1');

        $this->assertEquals('live-1', $info->getName());
        $this->assertEquals('live channel 1', $info->getDescription());
        $this->assertEquals(1, count($info->getPublishUrls()));
        $this->assertEquals(1, count($info->getPlayUrls()));
    }

    public function testPutLiveChannelWithDefaultParams()
    {
        $config = new LiveChannelConfig(array(
            'description' => 'live channel 1',
            'type' => 'HLS',
        ));
        $info = $this->client->putBucketLiveChannel($this->bucketName, 'live-1', $config);
        $this->client->deleteBucketLiveChannel($this->bucketName, 'live-1');

        $this->assertEquals('live-1', $info->getName());
        $this->assertEquals('live channel 1', $info->getDescription());
        $this->assertEquals(1, count($info->getPublishUrls()));
        $this->assertEquals(1, count($info->getPlayUrls()));
    }

    public function testListLiveChannels()
    {
       $config = new LiveChannelConfig(array(
            'description' => 'live channel 1',
            'type' => 'HLS',
            'fragDuration' => 10,
            'fragCount' => 5,
            'playListName' => 'hello.m3u8'
        ));
        $this->client->putBucketLiveChannel($this->bucketName, 'live-1', $config);

        $config = new LiveChannelConfig(array(
            'description' => 'live channel 2',
            'type' => 'HLS',
            'fragDuration' => 10,
            'fragCount' => 5,
            'playListName' => 'hello.m3u8'
        ));
        $this->client->putBucketLiveChannel($this->bucketName, 'live-2', $config);

        $list = $this->client->listBucketLiveChannels($this->bucketName);

        $this->assertEquals($this->bucketName, $list->getBucketName());
        $this->assertEquals(false, $list->getIsTruncated());
        $channels = $list->getChannelList();
        $this->assertEquals(2, count($channels));

        $chan1 = $channels[0];
        $this->assertEquals('live-1', $chan1->getName());
        $this->assertEquals('live channel 1', $chan1->getDescription());
        $this->assertEquals(1, count($chan1->getPublishUrls()));
        $this->assertEquals(1, count($chan1->getPlayUrls()));

        $chan2 = $channels[1];
        $this->assertEquals('live-2', $chan2->getName());
        $this->assertEquals('live channel 2', $chan2->getDescription());
        $this->assertEquals(1, count($chan2->getPublishUrls()));
        $this->assertEquals(1, count($chan2->getPlayUrls()));

        $list = $this->client->listBucketLiveChannels($this->bucketName, array(
            'prefix' => 'live-',
            'marker' => 'live-1',
            'max-keys' => 10
        ));
        $channels = $list->getChannelList();
        $this->assertEquals(1, count($channels));
        $chan2 = $channels[0];
        $this->assertEquals('live-2', $chan2->getName());
        $this->assertEquals('live channel 2', $chan2->getDescription());
        $this->assertEquals(1, count($chan2->getPublishUrls()));
        $this->assertEquals(1, count($chan2->getPlayUrls()));

        $this->client->deleteBucketLiveChannel($this->bucketName, 'live-1');
        $this->client->deleteBucketLiveChannel($this->bucketName, 'live-2');
        $list = $this->client->listBucketLiveChannels($this->bucketName, array(
            'prefix' => 'live-'
        ));
        $this->assertEquals(0, count($list->getChannelList()));
   }

    public function testDeleteLiveChannel()
    {
        $channelName = 'live-to-delete';
        $config = new LiveChannelConfig(array(
            'description' => 'live channel to delete',
            'type' => 'HLS',
            'fragDuration' => 10,
            'fragCount' => 5,
            'playListName' => 'hello.m3u8'
        ));
        $this->client->putBucketLiveChannel($this->bucketName, $channelName, $config);

        $this->client->deleteBucketLiveChannel($this->bucketName, $channelName);
        $list = $this->client->listBucketLiveChannels($this->bucketName, array(
            'prefix' => $channelName
        ));

        $this->assertEquals(0, count($list->getChannelList()));
    }

    public function testSignRtmpUrl()
    {
        $channelName = '90475';
        $bucket = 'douyu';
        $now = time();
        $url = $this->client->signRtmpUrl($bucket, $channelName, 900, array(
            'params' => array(
                'playlistName' => 'playlist.m3u8'
            )
        ));

        $ret = parse_url($url);
        $this->assertEquals('rtmp', $ret['scheme']);
        parse_str($ret['query'], $query);

        $this->assertTrue(isset($query['OSSAccessKeyId']));
        $this->assertTrue(isset($query['Signature']));
        $this->assertTrue(intval($query['Expires']) - ($now + 900) < 3);
        $this->assertEquals('playlist.m3u8', $query['playlistName']);
    }

    public function testLiveChannelInfo()
    {
        $channelName = 'live-to-put-status';
        $config = new LiveChannelConfig(array(
            'description' => 'test live channel info',
            'type' => 'HLS',
            'fragDuration' => 10,
            'fragCount' => 5,
            'playListName' => 'hello.m3u8'
        ));
        $this->client->putBucketLiveChannel($this->bucketName, $channelName, $config);

        $info = $this->client->getLiveChannelInfo($this->bucketName, $channelName);
        $this->assertEquals('test live channel info', $info->getDescription());
        $this->assertEquals('enabled', $info->getStatus());
        $this->assertEquals('HLS', $info->getType());
        $this->assertEquals(10, $info->getFragDuration());
        $this->assertEquals(5, $info->getFragCount());
        $this->assertEquals('playlist.m3u8', $info->getPlayListName());

        $this->client->deleteBucketLiveChannel($this->bucketName, $channelName);
        $list = $this->client->listBucketLiveChannels($this->bucketName, array(
            'prefix' => $channelName
        ));
        $this->assertEquals(0, count($list->getChannelList()));
    }

    public function testPutLiveChannelStatus()
    {
        $channelName = 'live-to-put-status';
        $config = new LiveChannelConfig(array(
            'description' => 'test live channel info',
            'type' => 'HLS',
            'fragDuration' => 10,
            'fragCount' => 5,
            'playListName' => 'hello.m3u8'
        ));
        $this->client->putBucketLiveChannel($this->bucketName, $channelName, $config);
       
        $info = $this->client->getLiveChannelInfo($this->bucketName, $channelName);
        $this->assertEquals('test live channel info', $info->getDescription());
        $this->assertEquals('enabled', $info->getStatus());
        $this->assertEquals('HLS', $info->getType());
        $this->assertEquals(10, $info->getFragDuration());
        $this->assertEquals(5, $info->getFragCount());
        $this->assertEquals('playlist.m3u8', $info->getPlayListName());
        $status = $this->client->getLiveChannelStatus($this->bucketName, $channelName);
        $this->assertEquals('Idle', $status->getStatus());


        $resp = $this->client->putLiveChannelStatus($this->bucketName, $channelName, "disabled"); 
        $info = $this->client->getLiveChannelInfo($this->bucketName, $channelName);
        $this->assertEquals('test live channel info', $info->getDescription());
        $this->assertEquals('disabled', $info->getStatus());
        $this->assertEquals('HLS', $info->getType());
        $this->assertEquals(10, $info->getFragDuration());
        $this->assertEquals(5, $info->getFragCount());
        $this->assertEquals('playlist.m3u8', $info->getPlayListName());

        $status = $this->client->getLiveChannelStatus($this->bucketName, $channelName);
        //getLiveChannelInfo
        $this->assertEquals('Disabled', $status->getStatus());

        $this->client->deleteBucketLiveChannel($this->bucketName, $channelName);
        $list = $this->client->listBucketLiveChannels($this->bucketName, array(
            'prefix' => $channelName
        ));
        $this->assertEquals(0, count($list->getChannelList()));

    }
    public function testLiveChannelHistory()
    {
        $channelName = 'live-test-history';
        $config = new LiveChannelConfig(array(
            'description' => 'test live channel info',
            'type' => 'HLS',
            'fragDuration' => 10,
            'fragCount' => 5,
            'playListName' => 'hello.m3u8'
        ));
        $this->client->putBucketLiveChannel($this->bucketName, $channelName, $config);
        
        $history = $this->client->getLiveChannelHistory($this->bucketName, $channelName);
        $this->assertEquals(0, count($history->getLiveRecordList()));
    }
}
