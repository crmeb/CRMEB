<?php

namespace crmeb\services\wechatlive;

use EasyWeChat\Core\AbstractAPI;
use EasyWeChat\Core\AccessToken;
use EasyWeChat\Core\Exceptions\InvalidArgumentException;

/**
 * Class ProgramWechatLive
 * @package crmeb\services\wechatlive
 */
class ProgramWechatLive extends AbstractAPI
{

    /**
     * 获取直播列表信息
     */
    const API_WECHAT_LIVE = 'http://api.weixin.qq.com/wxa/business/getliveinfo';

    /**
     * ProgramWechatLive constructor.
     * @param AccessToken $accessToken
     */
    public function __construct(AccessToken $accessToken)
    {
        parent::__construct($accessToken);
    }

    /**
     * 获取直播间列表
     * @param int $page
     * @param int $limit
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function getLiveInfo(int $page = 1, int $limit = 10)
    {
        $page = ($page - 1) * $limit;
        $params = [
            'start' => $page,
            'limit' => $limit
        ];
        return $this->parseJSON('json', [self::API_WECHAT_LIVE, $params]);
    }
}