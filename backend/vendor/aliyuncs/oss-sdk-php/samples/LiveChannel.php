<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Model\LiveChannelConfig;

$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);

//******************************* Simple Usage *******************************************************

/**
 * Create a Live Channel
 * The live channel's name is test_rtmp_live.
 * The play url file is named as test.m3u8, which includes 3 ts files.
 * The time period of each file is 5 seconds.(It is recommneded value only for demo purpose, the actual period depends on the key frame.)
 *
 */
$config = new LiveChannelConfig(array(
            'description' => 'live channel test',
            'type' => 'HLS',
            'fragDuration' => 10,
            'fragCount' => 5,
            'playListName' => 'hello.m3u8'
        ));
$info = $ossClient->putBucketLiveChannel($bucket, 'test_rtmp_live', $config);
Common::println("bucket $bucket liveChannel created:\n" . 
"live channel name: ". $info->getName() . "\n" .
"live channel description: ". $info->getDescription() . "\n" .
"publishurls: ". $info->getPublishUrls()[0] . "\n" .
"playurls: ". $info->getPlayUrls()[0] . "\n");

/**
  * You can use listBucketLiveChannels to list and manage all existing live channels.
  * Prefix can be used to filter listed live channels by prefix.
  * Max_keys indicates the maximum numbers of live channels that can be listed in an iterator at one time. Its value is 1000 in maximum and 100 by default.
 */
$list = $ossClient->listBucketLiveChannels($bucket);
Common::println("bucket $bucket listLiveChannel:\n" . 
"list live channel prefix: ". $list->getPrefix() . "\n" .
"list live channel marker: ". $list->getMarker() . "\n" .
"list live channel maxkey: ". $list->getMaxKeys() . "\n" .
"list live channel IsTruncated: ". $list->getIsTruncated() . "\n" .
"list live channel getNextMarker: ". $list->getNextMarker() . "\n");

foreach($list->getChannelList()  as $list)
{
    Common::println("bucket $bucket listLiveChannel:\n" . 
    "list live channel IsTruncated: ". $list->getName() . "\n" .
    "list live channel Description: ". $list->getDescription() . "\n" .
    "list live channel Status: ". $list->getStatus() . "\n" .
    "list live channel getNextMarker: ". $list->getLastModified() . "\n");
}
/**
  * Obtain the play_url (url used for rtmp stream pushing.
  * If the the bucket is not globally readable and writable,
  * the url must be signed as shown in the following.) and pulish_url (the url included in the m3u8 file generated in stream pushing) used to push streams.
 */
$play_url = $ossClient->signRtmpUrl($bucket, "test_rtmp_live", 3600, array('params' => array('playlistName' => 'playlist.m3u8')));
Common::println("bucket $bucket rtmp url: \n" . $play_url);
$play_url = $ossClient->signRtmpUrl($bucket, "test_rtmp_live", 3600);
Common::println("bucket $bucket rtmp url: \n" . $play_url);

/**
  * If you want to disable a created live channel (disable the pushing streaming or do not allow stream pushing to an IP address), call putLiveChannelStatus to change the channel status to "Disabled".
  * If you want to enable a disabled live channel, call PutLiveChannelStatus to chanage the channel status to "Enabled".
 */
$resp = $ossClient->putLiveChannelStatus($bucket, "test_rtmp_live", "enabled");

/**
  * You can callLiveChannelInfo to get the information about a live channel.
 */
$info = $ossClient->getLiveChannelInfo($bucket, 'test_rtmp_live');
Common::println("bucket $bucket LiveChannelInfo:\n" . 
"live channel info description: ". $info->getDescription() . "\n" .
"live channel info status: ". $info->getStatus() . "\n" .
"live channel info type: ". $info->getType() . "\n" .
"live channel info fragDuration: ". $info->getFragDuration() . "\n" .
"live channel info fragCount: ". $info->getFragCount() . "\n" .
"live channel info playListName: ". $info->getPlayListName() . "\n");

/**
  * Gets the historical pushing streaming records by calling getLiveChannelHistory. Now the max records to return is 10.
 */
$history = $ossClient->getLiveChannelHistory($bucket, "test_rtmp_live");
if (count($history->getLiveRecordList()) != 0)
{
    foreach($history->getLiveRecordList() as $recordList)
    {
        Common::println("bucket $bucket liveChannelHistory:\n" . 
        "live channel history startTime: ". $recordList->getStartTime() . "\n" .
        "live channel history endTime: ". $recordList->getEndTime() . "\n" .
        "live channel history remoteAddr: ". $recordList->getRemoteAddr() . "\n");
    }
}

/**
  * Get the live channel's status by calling getLiveChannelStatus.
  * If the live channel is receiving the pushing stream, all attributes in stat_result are valid.
  * If the live channel is idle or disabled, then the status is idle or Disabled and other attributes have no meaning.
 */
$status = $ossClient->getLiveChannelStatus($bucket, "test_rtmp_live");
Common::println("bucket $bucket listLiveChannel:\n" . 
"live channel status status: ". $status->getStatus() . "\n" .
"live channel status ConnectedTime: ". $status->getConnectedTime() . "\n" .
"live channel status VideoWidth: ". $status->getVideoWidth() . "\n" .
"live channel status VideoHeight: ". $status->getVideoHeight() . "\n" .
"live channel status VideoFrameRate: ". $status->getVideoFrameRate() . "\n" .
"live channel status VideoBandwidth: ". $status->getVideoBandwidth() . "\n" .
"live channel status VideoCodec: ". $status->getVideoCodec() . "\n" .
"live channel status AudioBandwidth: ". $status->getAudioBandwidth() . "\n" .
"live channel status AudioSampleRate: ". $status->getAudioSampleRate() . "\n" .
"live channel status AdioCodec: ". $status->getAudioCodec() . "\n");

/**
 * If you want to generate a play url from the ts files generated from pushing streaming, call postVodPlayList.
 * Specify the start time to 60 seconds before the current time and the end time to the current time, which means that a video of 60 seconds is generated.
 * The playlist file is specified to “vod_playlist.m3u8”, which means that a palylist file named vod_playlist.m3u8 is created after the interface is called.
 */
$current_time = time();
$ossClient->postVodPlaylist($bucket,
    "test_rtmp_live", "vod_playlist.m3u8", 
    array('StartTime' => $current_time - 60, 
          'EndTime' => $current_time)
);

/**
  *  Call delete_live_channel to delete a live channel if it will no longer be in used.
 */
$ossClient->deleteBucketLiveChannel($bucket, "test_rtmp_live");
