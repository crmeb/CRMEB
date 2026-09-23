<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace crmeb\services\easywechat\miniScheme;

use crmeb\services\easywechat\AbstractApiClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * 小程序 URL Scheme / URL Link 生成
 * Class ProgramScheme
 * @package crmeb\services\easywechat\miniScheme
 */
class ProgramScheme extends AbstractApiClient
{
    const URL_SCHEME_API = 'wxa/generatescheme';
    const URL_LINK_API = 'wxa/generate_urllink';

    public function getUrlScheme($jumpWxa = [], $expireType = -1, $expireNum = 0)
    {
        $params = [];
        if (!empty($jumpWxa)) $params['jump_wxa'] = $jumpWxa;
        if ($expireType != -1) {
            $params['expire_type'] = (int)$expireType;
            $params['is_expire'] = true;
        } else {
            $params['is_expire'] = false;
        }
        if ($expireType == 0) $params['expire_time'] = (int)$expireNum;
        if ($expireType == 1) $params['expire_interval'] = (int)$expireNum;
        return $this->postJson(self::URL_SCHEME_API, $params);
    }

    public function getUrlLink($jumpWxa = [])
    {
        $params = [
            'path' => $jumpWxa['path'],
            'query' => $jumpWxa['query'],
            'expire_type' => 1,
            'expire_interval' => 30,
        ];
        return $this->postJson(self::URL_LINK_API, $params);
    }
}
