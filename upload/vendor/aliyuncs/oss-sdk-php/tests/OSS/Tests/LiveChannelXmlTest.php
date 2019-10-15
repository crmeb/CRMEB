<?php

namespace OSS\Tests;

require_once __DIR__ . '/Common.php';

use OSS\Model\LiveChannelInfo;
use OSS\Model\LiveChannelListInfo;
use OSS\Model\LiveChannelConfig;
use OSS\Model\GetLiveChannelStatus;
use OSS\Model\GetLiveChannelHistory;

class LiveChannelXmlTest extends \PHPUnit_Framework_TestCase
{
    private $config = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<LiveChannelConfiguration>
  <Description>xxx</Description>
  <Status>enabled</Status>
  <Target>
     <Type>hls</Type>
     <FragDuration>1000</FragDuration>
     <FragCount>5</FragCount>
     <PlayListName>hello.m3u8</PlayListName>
  </Target>
</LiveChannelConfiguration>
BBBB;

    private $info = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<CreateLiveChannelResult>
  <Name>live-1</Name>
  <Description>xxx</Description>
  <PublishUrls>
    <Url>rtmp://bucket.oss-cn-hangzhou.aliyuncs.com/live/213443245345</Url>
  </PublishUrls>
  <PlayUrls>
    <Url>http://bucket.oss-cn-hangzhou.aliyuncs.com/213443245345/播放列表.m3u8</Url>
  </PlayUrls>
  <Status>enabled</Status>
  <LastModified>2015-11-24T14:25:31.000Z</LastModified>
</CreateLiveChannelResult>
BBBB;

    private $list = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<ListLiveChannelResult>
<Prefix>xxx</Prefix>
  <Marker>yyy</Marker>
  <MaxKeys>100</MaxKeys>
  <IsTruncated>false</IsTruncated>
  <NextMarker>121312132</NextMarker>
  <LiveChannel>
    <Name>12123214323431</Name>
    <Description>xxx</Description>
    <PublishUrls>
      <Url>rtmp://bucket.oss-cn-hangzhou.aliyuncs.com/live/1</Url>
    </PublishUrls>
    <PlayUrls>
      <Url>http://bucket.oss-cn-hangzhou.aliyuncs.com/1/播放列表.m3u8</Url>
    </PlayUrls>
    <Status>enabled</Status>
    <LastModified>2015-11-24T14:25:31.000Z</LastModified>
  </LiveChannel>
  <LiveChannel>
    <Name>432423432423</Name>
    <Description>yyy</Description>
    <PublishUrls>
      <Url>rtmp://bucket.oss-cn-hangzhou.aliyuncs.com/live/2</Url>
    </PublishUrls>
    <PlayUrls>
      <Url>http://bucket.oss-cn-hangzhou.aliyuncs.com/2/播放列表.m3u8</Url>
    </PlayUrls>
    <Status>enabled</Status>
    <LastModified>2016-11-24T14:25:31.000Z</LastModified>
  </LiveChannel>
</ListLiveChannelResult>
BBBB;

    private $status = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<LiveChannelStat>
    <Status>Live</Status>
    <ConnectedTime>2016-10-20T14:25:31.000Z</ConnectedTime>
    <RemoteAddr>10.1.2.4:47745</RemoteAddr>
    <Video>
        <Width>1280</Width>
        <Height>536</Height>
        <FrameRate>24</FrameRate>
        <Bandwidth>72513</Bandwidth>
        <Codec>H264</Codec>
    </Video>
        <Audio>
        <Bandwidth>6519</Bandwidth>
        <SampleRate>44100</SampleRate>
        <Codec>AAC</Codec>
    </Audio>
</LiveChannelStat>
BBBB;

    private $history = <<<BBBB
<?xml version="1.0" encoding="utf-8"?>
<LiveChannelHistory>
    <LiveRecord>
        <StartTime>2013-11-24T14:25:31.000Z</StartTime>
        <EndTime>2013-11-24T15:25:31.000Z</EndTime>
        <RemoteAddr>10.101.194.148:56861</RemoteAddr>
    </LiveRecord>
    <LiveRecord>
        <StartTime>2014-11-24T14:25:31.000Z</StartTime>
        <EndTime>2014-11-24T15:25:31.000Z</EndTime>
        <RemoteAddr>10.101.194.148:56862</RemoteAddr>
    </LiveRecord>
    <LiveRecord>
        <StartTime>2015-11-24T14:25:31.000Z</StartTime>
        <EndTime>2015-11-24T15:25:31.000Z</EndTime>
        <RemoteAddr>10.101.194.148:56863</RemoteAddr>
    </LiveRecord>
</LiveChannelHistory>
BBBB;

    public function testLiveChannelStatus()
    {
        $stat = new GetLiveChannelStatus();
        $stat->parseFromXml($this->status);
  
        $this->assertEquals('Live', $stat->getStatus());
        $this->assertEquals('2016-10-20T14:25:31.000Z', $stat->getConnectedTime());
        $this->assertEquals('10.1.2.4:47745', $stat->getRemoteAddr());

        $this->assertEquals(1280, $stat->getVideoWidth());
        $this->assertEquals(536, $stat->getVideoHeight());
        $this->assertEquals(24, $stat->getVideoFrameRate());
        $this->assertEquals(72513, $stat->getVideoBandwidth());
        $this->assertEquals('H264', $stat->getVideoCodec());
        $this->assertEquals(6519, $stat->getAudioBandwidth());
        $this->assertEquals(44100, $stat->getAudioSampleRate());
        $this->assertEquals('AAC', $stat->getAudioCodec());

    }

    public function testLiveChannelHistory()
    {
        $history = new GetLiveChannelHistory();
        $history->parseFromXml($this->history);

        $recordList = $history->getLiveRecordList();
        $this->assertEquals(3, count($recordList));

        $list0 = $recordList[0];
        $this->assertEquals('2013-11-24T14:25:31.000Z', $list0->getStartTime());
        $this->assertEquals('2013-11-24T15:25:31.000Z', $list0->getEndTime());
        $this->assertEquals('10.101.194.148:56861', $list0->getRemoteAddr());
   
        $list1 = $recordList[1];
        $this->assertEquals('2014-11-24T14:25:31.000Z', $list1->getStartTime());
        $this->assertEquals('2014-11-24T15:25:31.000Z', $list1->getEndTime());
        $this->assertEquals('10.101.194.148:56862', $list1->getRemoteAddr());

        $list2 = $recordList[2];
        $this->assertEquals('2015-11-24T14:25:31.000Z', $list2->getStartTime());
        $this->assertEquals('2015-11-24T15:25:31.000Z', $list2->getEndTime());
        $this->assertEquals('10.101.194.148:56863', $list2->getRemoteAddr());

    }

    public function testLiveChannelConfig()
    {
        $config = new LiveChannelConfig(array('name' => 'live-1'));
        $config->parseFromXml($this->config);

        $this->assertEquals('xxx', $config->getDescription());
        $this->assertEquals('enabled', $config->getStatus());
        $this->assertEquals('hls', $config->getType());
        $this->assertEquals(1000, $config->getFragDuration());
        $this->assertEquals(5, $config->getFragCount());
        $this->assertEquals('hello.m3u8', $config->getPlayListName());

        $xml = $config->serializeToXml();
        $config2 = new LiveChannelConfig(array('name' => 'live-2'));
        $config2->parseFromXml($xml);
        $this->assertEquals('xxx', $config2->getDescription());
        $this->assertEquals('enabled', $config2->getStatus());
        $this->assertEquals('hls', $config2->getType());
        $this->assertEquals(1000, $config2->getFragDuration());
        $this->assertEquals(5, $config2->getFragCount());
        $this->assertEquals('hello.m3u8', $config2->getPlayListName());
    }

    public function testLiveChannelInfo()
    {
        $info = new LiveChannelInfo(array('name' => 'live-1'));
        $info->parseFromXml($this->info);

        $this->assertEquals('live-1', $info->getName());
        $this->assertEquals('xxx', $info->getDescription());
        $this->assertEquals('enabled', $info->getStatus());
        $this->assertEquals('2015-11-24T14:25:31.000Z', $info->getLastModified());
        $pubs = $info->getPublishUrls();
        $this->assertEquals(1, count($pubs));
        $this->assertEquals('rtmp://bucket.oss-cn-hangzhou.aliyuncs.com/live/213443245345', $pubs[0]);

        $plays = $info->getPlayUrls();
        $this->assertEquals(1, count($plays));
        $this->assertEquals('http://bucket.oss-cn-hangzhou.aliyuncs.com/213443245345/播放列表.m3u8', $plays[0]);
    }

    public function testLiveChannelList()
    {
        $list = new LiveChannelListInfo();
        $list->parseFromXml($this->list);

        $this->assertEquals('xxx', $list->getPrefix());
        $this->assertEquals('yyy', $list->getMarker());
        $this->assertEquals(100, $list->getMaxKeys());
        $this->assertEquals(false, $list->getIsTruncated());
        $this->assertEquals('121312132', $list->getNextMarker());

        $channels = $list->getChannelList();
        $this->assertEquals(2, count($channels));

        $chan1 = $channels[0];
        $this->assertEquals('12123214323431', $chan1->getName());
        $this->assertEquals('xxx', $chan1->getDescription());
        $this->assertEquals('enabled', $chan1->getStatus());
        $this->assertEquals('2015-11-24T14:25:31.000Z', $chan1->getLastModified());
        $pubs = $chan1->getPublishUrls();
        $this->assertEquals(1, count($pubs));
        $this->assertEquals('rtmp://bucket.oss-cn-hangzhou.aliyuncs.com/live/1', $pubs[0]);

        $plays = $chan1->getPlayUrls();
        $this->assertEquals(1, count($plays));
        $this->assertEquals('http://bucket.oss-cn-hangzhou.aliyuncs.com/1/播放列表.m3u8', $plays[0]);

        $chan2 = $channels[1];
        $this->assertEquals('432423432423', $chan2->getName());
        $this->assertEquals('yyy', $chan2->getDescription());
        $this->assertEquals('enabled', $chan2->getStatus());
        $this->assertEquals('2016-11-24T14:25:31.000Z', $chan2->getLastModified());
        $pubs = $chan2->getPublishUrls();
        $this->assertEquals(1, count($pubs));
        $this->assertEquals('rtmp://bucket.oss-cn-hangzhou.aliyuncs.com/live/2', $pubs[0]);

        $plays = $chan2->getPlayUrls();
        $this->assertEquals(1, count($plays));
        $this->assertEquals('http://bucket.oss-cn-hangzhou.aliyuncs.com/2/播放列表.m3u8', $plays[0]);
    }

}
