<?php

namespace crmeb\services\printer\storage;

use crmeb\basic\BasePrinter;
use crmeb\services\printer\AccessToken;

/**
 * Class YiLianYun
 * @package crmeb\services\printer\storage
 */
class YiLianYun extends BasePrinter
{

    /**
     * 初始化
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config)
    {

    }

    /**
     * 开始打印
     * @param array|null $config
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function startPrinter(?array $config = [])
    {
        if (!$this->printerContent) {
            return $this->setError('Missing print');
        }
        $request = $this->accessToken->postRequest($this->accessToken->getApiUrl('print/index'), [
            'client_id' => $this->accessToken->clientId,
            'access_token' => $this->accessToken->getAccessToken(),
            'machine_code' => $this->accessToken->machineCode,
            'content' => $this->printerContent,
            'origin_id' => $config['origin_id'] ?? 'crmeb' . time(),
            'sign' => strtolower(md5($this->accessToken->clientId . time() . $this->accessToken->apiKey)),
            'id' => $this->accessToken->createUuid(),
            'timestamp' => time()
        ]);
        if ($request === false) {
            return $this->setError('request was aborted');
        }
        $request = is_string($request) ? json_decode($request, true) : $request;
        if (isset($request['error']) && in_array($request['error'], [18, 14])) {
            return $this->setError('Accesstoken has expired');
        }
        return $request;
    }

    /**
     * 设置打印内容
     * @param array $config
     * @return YiLianYun
     */
    public function setPrinterContent(array $config): self
    {
        $this->printerContent = $config['content'] ?? null;
        return $this;
    }


}
