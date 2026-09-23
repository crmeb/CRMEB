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
namespace driver\printer\FeiEYun;

use crmeb\services\printer\BasePrinter;
use think\facade\Log;

/**
 * 飞鹅云小票打印驱动
 */
class Driver extends BasePrinter
{

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
        if ($this->accessToken->feyUser === '' || $this->accessToken->feyUkey === '' || $this->accessToken->feySn === '') {
            return $this->setError(getLang('飞鹅云打印配置不完整'));
        }
        $time = time();
        try {
            $request = $this->accessToken->postRequest('https://api.feieyun.cn/Api/Open/', [
                'user' => $this->accessToken->feyUser,
                'stime' => $time,
                'sig' => sha1($this->accessToken->feyUser . $this->accessToken->feyUkey . $time),
                'apiname' => 'Open_printMsg',
                'sn' => $this->accessToken->feySn,
                'content' => $this->printerContent,
                'times' => $this->times,
            ]);
            $res = $this->accessToken->decodeResponse($request);
            if (!$res || !array_key_exists('ret', $res)
                || !(is_int($res['ret']) || (is_string($res['ret']) && preg_match('/^-?\d+$/D', $res['ret'])))) {
                $this->submissionUncertain = true;
                return $this->setError(getLang('飞鹅云打印响应无效'));
            }
            if (in_array($res['ret'], [0, '0'], true)) {
                $acceptedId = $res['data'] ?? null;
                if (($res['msg'] ?? null) !== 'ok'
                    || !((is_string($acceptedId) && trim($acceptedId) !== '') || (is_int($acceptedId) && $acceptedId > 0))) {
                    $this->submissionUncertain = true;
                    return $this->setError(getLang('飞鹅云打印响应无效'));
                }
                return $res;
            }
            return $this->setError(getLang('飞鹅云打印失败，错误码：') . (int)$res['ret']);
        } catch (\Throwable $e) {
            $this->submissionUncertain = true;
            Log::error('飞鹅云打印请求异常，异常类型：' . get_class($e));
            return $this->setError(getLang('飞鹅云打印请求失败'));
        }
    }

    /**
     * 设置打印内容和联数
     * @param string $content 打印内容
     * @param int $times 打印联数
     * @return self
     */
    public function setPrinterContent($content, $times = 1): self
    {
        $this->times = $times;
        $this->printerContent = $content;
        return $this;
    }
}
