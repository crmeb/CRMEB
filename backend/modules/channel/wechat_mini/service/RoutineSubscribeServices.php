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

namespace modules\channel\wechat_mini\service;

use app\services\message\admin\NoticeConfigServices;
use app\dao\message\NoticeTemplateDao;
use modules\channel\wechat_mini\sdk\MiniProgramService;
use think\facade\Log;

/**
 * 小程序订阅消息
 * 负责小程序订阅消息的模板ID查询与发送
 */
class RoutineSubscribeServices
{
    /**
     * openid
     * @var string
     */
    protected $openId;

    /**
     * 跳转链接
     * @var string|null
     */
    protected $toUrl;

    /**
     * 颜色
     * @var string|null
     */
    protected $color;

    /**
     * 错误信息
     * @var string|null
     */
    protected $error;

    /**
     * 最近一次微信接口响应摘要
     * @var array
     */
    protected array $lastResponse = [];

    /**
     * 设置openid
     * @param string $openId
     * @return $this
     */
    public function to(string $openId)
    {
        $this->openId = $openId;
        return $this;
    }

    /**
     * 跳转路径
     * @param string $url
     * @return $this
     */
    public function url(string $url)
    {
        $this->toUrl = $url;
        return $this;
    }

    /**
     * 设置背景颜色
     * @param string|null $color
     * @return $this
     */
    public function color(?string $color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * 从 eb_notice_template.extra 读取小程序模板ID
     * @param string $templateId 消息标识（mark）
     * @return string
     */
    public function getTempId(string $templateId)
    {
        $sysNoti = app()->make(NoticeConfigServices::class);
        $notification = $sysNoti->getOneNotce(['mark' => $templateId]);
        if (!$notification) return '';

        $templateDao = app()->make(NoticeTemplateDao::class);
        $channel = $templateDao->getByNotificationAndChannel($notification['id'], 'is_routine');
        if ($channel) {
            $extra = !empty($channel['extra']) ? (is_string($channel['extra']) ? json_decode($channel['extra'], true) : $channel['extra']) : [];
            return $extra['tempid'] ?? '';
        }
        return '';
    }

    /**
     * 发送订阅消息
     * @param string $tempid
     * @param array $data
     * @return bool
     */
    public function send(string $tempid, array $data = []): bool
    {
        $this->error = null;
        $this->lastResponse = [];
        if (!$tempid) {
            Log::warning('小程序订阅消息发送跳过：模板ID为空');
            return $this->setError('Template ID does not exist');
        }
        if (!$this->openId) {
            Log::warning('小程序订阅消息发送跳过：openid为空 ' . json_encode([
                    'template_id' => $tempid,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return $this->setError('Openid does not exist');
        }

        $openId = $this->openId;
        $context = [
            'template_id' => $tempid,
            'openid' => $this->maskOpenId($openId),
            'page' => $this->toUrl ? parse_url($this->toUrl, PHP_URL_PATH) : null,
            'data_keys' => array_values(array_keys($data)),
        ];
        Log::info('小程序订阅消息发送开始 ' . json_encode([
                'template_id' => $tempid,
                'openid' => $this->maskOpenId($openId),
                'page' => $this->toUrl ? parse_url($this->toUrl, PHP_URL_PATH) : null,
                'data_keys' => $data,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        try {
            $res = MiniProgramService::sendSubscribeTemlate($openId, $tempid, $data, $this->toUrl === null ? '' : $this->toUrl);
            Log::info('小程序订阅消息发送结果 ' . json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            $this->clear();

            return true;
        } catch (\Throwable $e) {
            Log::info('小程序订阅消息发送异常 ' . json_encode([
                    'exception' => get_class($e),
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->clear();
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 判断微信订阅消息接口是否返回成功
     * @param array $response 微信接口响应
     * @return bool
     */
    public static function isSuccessResponse(array $response): bool
    {
        return array_key_exists('errcode', $response) && (int)$response['errcode'] === 0;
    }

    /**
     * 获取最近一次微信响应摘要
     * @return array
     */
    public function getLastResponse(): array
    {
        return $this->lastResponse;
    }

    /**
     * 恢复默认值
     */
    protected function clear()
    {
        $this->openId = null;
        $this->toUrl = null;
        $this->color = null;
    }

    /**
     * 只保留微信响应中的排障字段，避免把未知响应内容写入日志
     * @param array $response 微信接口响应
     * @return array
     */
    protected static function responseSummary(array $response): array
    {
        $summary = [];
        foreach (['errcode', 'errmsg', 'msgid'] as $key) {
            if (array_key_exists($key, $response)) {
                $summary[$key] = $response[$key];
            }
        }
        return $summary;
    }

    /**
     * 脱敏小程序 openid
     * @param string $openId
     * @return string
     */
    protected function maskOpenId(string $openId): string
    {
        $length = strlen($openId);
        if ($length <= 8) {
            return '***';
        }
        return substr($openId, 0, 4) . '***' . substr($openId, -4);
    }

    /**
     * 设置错误信息
     * @param string|null $error
     * @return bool
     */
    protected function setError(?string $error = null)
    {
        $this->error = $error ?: '未知错误';
        return false;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        $error = $this->error;
        $this->error = null;
        return $error;
    }
}
