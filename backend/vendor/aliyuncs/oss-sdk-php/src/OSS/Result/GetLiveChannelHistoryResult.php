<?php

namespace OSS\Result;

use OSS\Model\GetLiveChannelHistory;

class GetLiveChannelHistoryResult extends Result
{
    /**
     * @return GetLiveChannelHistory
     */
    protected function parseDataFromResponse()
    {
        $content = $this->rawResponse->body;
        $channelList = new GetLiveChannelHistory();
        $channelList->parseFromXml($content);
        return $channelList;
    }
}
