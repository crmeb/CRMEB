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

namespace app\http;

use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\exceptions\ApiStatusException;
use crmeb\exceptions\AuthException;
use think\exception\Handle;
use think\exception\ValidateException;
use think\facade\Env;
use think\facade\Log;
use think\Response;
use Throwable;

/**
 * 三端 API 异常处理器公共基类（api / adminapi / outapi）
 * 收口统一的异常上报日志格式与 JSON 渲染流程，各端子类仅覆写差异扩展点：
 * - operatorId():  日志首列操作者身份标识（uid / adminId / kefuId）
 * - guardReport(): report 是否启用外层兜底 try/catch（日志构造失败时降级记录）
 * - renderCustom(): render 前置钩子，返回 Response 则短路通用渲染（各端特有异常分支）
 */
abstract class BaseApiExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        ValidateException::class,
        AuthException::class,
        AdminException::class,
        ApiException::class,
    ];

    /**
     * 日志首列操作者身份标识（各端子类覆写：uid / adminId / kefuId）
     * @return mixed
     */
    abstract protected function operatorId();

    /**
     * report 是否启用外层兜底 try/catch（构造日志过程自身异常时降级为记录错误消息）
     * @return bool
     */
    protected function guardReport(): bool
    {
        return false;
    }

    /**
     * render 前置钩子：各端特有异常分支，返回 Response 则短路通用渲染
     * @param \think\Request $request
     * @param Throwable $e
     * @return Response|null
     */
    protected function renderCustom($request, Throwable $e): ?Response
    {
        return null;
    }

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     * @access public
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        if ($this->isIgnoreReport($exception)) {
            return;
        }
        if ($this->guardReport()) {
            // 兜底：日志构造过程自身异常时降级记录，避免掩盖原始异常
            try {
                $this->writeErrorLog($exception);
            } catch (\Throwable $e) {
                Log::write($e->getMessage(), "error");
            }
        } else {
            $this->writeErrorLog($exception);
        }
    }

    /**
     * 只复制日志参数并递归脱敏，不修改请求中的业务数据
     * @param array $params
     * @return array
     */
    protected function redactLogParams(array $params): array
    {
        $redacted = [];
        foreach ($params as $key => $value) {
            $name = strtolower(preg_replace('/[^a-z0-9]/i', '', (string)$key));
            if (preg_match('/password|passwd|passphrase|pwd|token|secret|accesskey|apikey|privatekey|credential|authorization|authkey|cookie/', $name)
                || in_array($name, ['auth', 'xauth', 'authcode', 'authentication', 'jwt', 'sessionid', 'sessionkey',
                    'pass', 'phone', 'mobile', 'telephone', 'tel', 'phonenumber',
                    'idcard', 'identitycard', 'idnumber', 'bankcard', 'bankaccount', 'cardnumber'], true)) {
                $redacted[$key] = '********';
            } else {
                $redacted[$key] = is_array($value) ? $this->redactLogParams($value) : $value;
            }
        }
        return $redacted;
    }

    /**
     * 扫码能力位于轮询路径中，按解码后的固定段定位但保留其余原始路径
     * @param string $route
     * @return string
     */
    protected function redactLogRoute(string $route): string
    {
        $segments = preg_split('~((?:/|%2f)+)~i', $route, -1, PREG_SPLIT_DELIM_CAPTURE);
        for ($index = 0, $count = count($segments); $index + 6 < $count; $index += 2) {
            if (strtolower(rawurldecode($segments[$index])) === 'file'
                && strtolower(rawurldecode($segments[$index + 2])) === 'scan_upload'
                && strtolower(rawurldecode($segments[$index + 4])) === 'image'
                && $segments[$index + 6] !== '') {
                $segments[$index + 6] = '********';
            }
        }
        $route = implode('', $segments);
        // 原始 URI 与服务器规范化 PATH_INFO 可能不同，兜底使用不会被 GET/POST 覆盖的路由能力。
        $scanToken = request()->route('scan_token');
        if (is_string($scanToken) && preg_match('/^[a-f0-9]{64}$/iD', $scanToken)) {
            $pattern = '';
            foreach (str_split($scanToken) as $character) {
                $pattern .= '(?:' . $character . '|%' . bin2hex($character) . ')';
            }
            $route = preg_replace('~' . $pattern . '~i', '********', $route);
        }
        return $route;
    }

    /**
     * 拼装并写入统一格式的错误日志（管道分隔 8 列）
     * @param Throwable $exception
     * @return void
     */
    protected function writeErrorLog(Throwable $exception): void
    {
        $data = [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'message' => $this->getMessage($exception),
            'code' => $this->getCode($exception),
        ];

        // 无效 UTF8 仅替换到日志副本，不能因编码失败回退到明文请求。
        $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE;
        //日志内容
        $log = [
            $this->operatorId(),                                                                        //操作者ID
            request()->ip(),                                                                            //客户ip
            ceil(msectime() - (request()->time(true) * 1000)),                                          //耗时（毫秒）
            request()->rule()->getMethod(),                                                             //请求类型
            str_replace("/", "", request()->rootUrl()),                                                 //应用
            $this->redactLogRoute(request()->baseUrl()),                                                 //路由
            json_encode($this->redactLogParams(request()->param()), $jsonFlags),                         //请求参数
            json_encode($data, $jsonFlags),                                                              //报错数据
        ];
        // 管道只作为列分隔符；JSON 中的转义在解码后仍恢复原字符。
        $log = array_map(static fn($column) => str_replace('|', '\\u007c', (string)$column), $log);
        Log::write(implode("|", $log), "error");
    }

    /**
     * Render an exception into an HTTP response.
     * @access public
     * @param \think\Request $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 各端特有异常分支（如 admin 的 HttpResponseException），命中则短路通用渲染
        $custom = $this->renderCustom($request, $e);
        if ($custom instanceof Response) {
            return $custom;
        }
        $massageData = Env::get('app_debug', false) ? [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace(),
            'previous' => $e->getPrevious(),
        ] : [];
        $message = $e->getMessage();
        // 添加自定义异常处理机制
        if ($e instanceof ApiStatusException) {
            return app('json')->status($e->getApiStatus(), $message, $e->getApiData());
        }
        if ($e instanceof AuthException || $e instanceof AdminException || $e instanceof ApiException || $e instanceof ValidateException) {
            return app('json')->make($e->getCode() ?: 400, $message, $massageData);
        }
        return app('json')->fail($message, $massageData);
    }
}
