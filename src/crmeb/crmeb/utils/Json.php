<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\utils;


use think\exception\HttpResponseException;
use think\facade\Config;
use think\facade\Lang;
use think\Response;

/**
 * Json输出类
 * Class Json
 * @package crmeb\utils
 */
class Json
{
    private $code = 200;

    public function code(int $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function make(int $status, string $msg, ?array $data = null): Response
    {
        $request = app()->request;
        $res = compact('status', 'msg');

        if (!is_null($data))
            $res['data'] = $data;

        if ($res['msg'] && !is_numeric($res['msg'])) {
            if (!$range = $request->get('lang')) {
                $range = $request->cookie(Config::get('lang.cookie_var'));
            }
            $res['msg'] = Lang::get($res['msg'], [], $range !== 'deleted' && $range ? $range : 'zh-cn');
        }
        return Response::create($res, 'json', $this->code);
    }

    public function success($msg = 'ok', ?array $data = null): Response
    {
        if (is_array($msg)) {
            $data = $msg;
            $msg = 'ok';
        }

        return $this->make(200, $msg, $data);
    }

    public function successful(...$args): Response
    {
        return $this->success(...$args);
    }

    public function fail($msg = 'fail', ?array $data = null): Response
    {
        if (is_array($msg)) {
            $data = $msg;
            $msg = 'ok';
        }

        return $this->make(400, $msg, $data);
    }

    public function status($status, $msg, $result = [])
    {
        $status = strtoupper($status);
        if (is_array($msg)) {
            $result = $msg;
            $msg = 'ok';
        }
        return $this->success($msg, compact('status', 'result'));
    }
}
