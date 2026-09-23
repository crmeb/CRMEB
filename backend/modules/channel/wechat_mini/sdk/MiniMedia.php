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

namespace modules\channel\wechat_mini\sdk;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * 小程序临时素材上传(EasyWeChat 6.x)
 * 替代 4.x miniprogram()->media 客户端,方法名保持以兼容
 * MiniProgramApiCall 事件链式调用(materialTemporaryService -> uploadImage)
 *
 * Class MiniMedia
 * @package modules\channel\wechat_mini\sdk
 */
class MiniMedia
{
    public function __construct(protected HttpClientInterface $api)
    {
    }

    /**
     * 上传图片素材,返回包含 media_id 的数组
     * @param string $path 本地文件绝对路径
     * @param string $type 素材类型
     * @return array
     */
    public function uploadImage(string $path, string $type = 'image'): array
    {
        return $this->upload($path, $type ?: 'image');
    }

    /**
     * 上传语音素材
     * @param string $path
     * @param string $type
     * @return array
     */
    public function uploadVoice(string $path, string $type = 'voice'): array
    {
        return $this->upload($path, $type ?: 'voice');
    }

    public function upload(string $path, string $type): array
    {
        $res = $this->api->withFile($path, 'media')
            ->post('cgi-bin/media/upload?type=' . $type)
            ->toArray(false);

        return is_array($res) ? $res : [];
    }
}
