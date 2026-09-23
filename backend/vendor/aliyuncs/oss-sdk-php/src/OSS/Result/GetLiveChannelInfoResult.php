<?php

namespace OSS\Result;

use OSS\Model\GetLiveChannelInfo;

class GetLiveChannelInfoResult extends Result
{
    /**
     * @return GetLiveChannelInfo
     */
    protected function parseDataFromResponse()
    {
        $content = $this->rawResponse->body;
        $channelList = new GetLiveChannelInfo();
        $channelList->parseFromXml($content);
        return $channelList;
    }
}
