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
namespace driver\printer\YiLianYun;

use crmeb\services\printer\BasePrinter;
use think\facade\Log;

/**
 * 易联云小票打印驱动
 */
class Driver extends BasePrinter
{
    /** @var string 本实例未指定任务标识时的打印单号 */
    protected string $originId = '';

    /**
     * 初始化
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config){}

    /**
     * 开始打印
     * @return array|false
     */
    public function startPrinter()
    {
        $this->submissionUncertain = false;
        if (!$this->printerContent) {
            return $this->setError(getLang('打印内容不能为空'));
        }
        if ($this->accessToken->machineCode === '') {
            return $this->setError(getLang('易联云打印配置不完整'));
        }
        $printRequested = false;
        try {
            $originId = $this->getOriginId();
            // 仅授权失效允许立即重试一次，两次请求保持同一单号避免重复提交。
            for ($attempt = 0; $attempt < 2; $attempt++) {
                $timestamp = time();
                $accessToken = $this->accessToken->getAccessToken();
                $requestId = $this->accessToken->createUuid();
                $printRequested = true;
                $response = $this->accessToken->postRequest('https://open-api.10ss.net/print/index', [
                    'client_id' => $this->accessToken->clientId,
                    'access_token' => $accessToken,
                    'machine_code' => $this->accessToken->machineCode,
                    'content' => $this->printerContent,
                    'origin_id' => $originId,
                    'sign' => strtolower(md5($this->accessToken->clientId . $timestamp . $this->accessToken->apiKey)),
                    'id' => $requestId,
                    'timestamp' => $timestamp,
                ]);
                $request = $this->accessToken->decodeResponse($response);
                if (!$request || !array_key_exists('error', $request)
                    || !(is_int($request['error']) || (is_string($request['error']) && ctype_digit($request['error'])))) {
                    $this->submissionUncertain = true;
                    return $this->setError(getLang('易联云打印响应无效'));
                }
                $error = (int)$request['error'];
                if ($error === 0) {
                    $acceptedId = is_array($request['body'] ?? null) ? ($request['body']['id'] ?? null) : null;
                    if (!((is_string($acceptedId) && trim($acceptedId) !== '') || (is_int($acceptedId) && $acceptedId > 0))) {
                        $this->submissionUncertain = true;
                        return $this->setError(getLang('易联云打印响应无效'));
                    }
                    return $request;
                }
                $printRequested = false;
                if (in_array($error, [18, 14], true)) {
                    $this->accessToken->invalidateAccessToken();
                    if ($attempt === 0) {
                        continue;
                    }
                }
                return $this->setError(getLang('易联云打印失败，错误码：') . $error);
            }
        } catch (\Throwable $e) {
            $this->submissionUncertain = $printRequested;
            Log::error('易联云打印请求异常，异常类型：' . get_class($e));
            return $this->setError(getLang('易联云打印请求失败'));
        }
        return $this->setError(getLang('易联云打印请求失败'));
    }

    /**
     * 获取当前打印任务的稳定单号，长度不超过 32 字符
     * @return string
     */
    protected function getOriginId(): string
    {
        if ($this->jobId !== '') {
            return 'crmeb_' . substr(hash('sha256', $this->jobId . "\0" . $this->accessToken->machineCode), 0, 26);
        }
        if ($this->originId === '') {
            $this->originId = 'crmeb_' . substr(str_replace('-', '', $this->accessToken->createUuid()), 0, 26);
        }
        return $this->originId;
    }

    /**
     * 设置打印内容
     * @param string $content 打印内容
     * @param int $times
     * @return Driver
     */
    public function setPrinterContent($content, int $times = 1): self
    {
        $this->printerContent = $content;
        $this->times = $times;
        $this->originId = '';
        return $this;
    }
}
