<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\listener\http;

use think\facade\Log;
use think\Response;

/**
 * 请求结束事件
 * Class Create
 * @package app\listener\http
 */
class HttpEnd
{
    public function handle(Response $response):void
    {
        //业务成功和失败分开存储
        $status = $response->getData()["status"] ?? 0;
        if ($status == 200) {
            //业务成功日志开关
            if (!config("log.success_log")) return;
            $type = "success";
        } else {
            //业务失败日志开关
            if (!config("log.fail_log")) return;
            $type = "fail";
        }

        //当前用户身份标识
        if (!empty(request()->uid())) {
            $uid = request()->uid();
        } elseif (!empty(request()->adminId())) {
            $uid = request()->adminId();
        } elseif (!empty(request()->kefuId())) {
            $uid = request()->kefuId();
        } else {
            $uid = 0;
        }

        //日志内容
        $log = [
            $uid,                                                                                 //用户ID
            request()->ip(),                                                                      //客户ip
            ceil(msectime() - (request()->time(true) * 1000)),                                    //耗时（毫秒）
            request()->rule()->getMethod(),                                                       //请求类型
            str_replace("/", "", request()->rootUrl()),                                           //应用
            request()->baseUrl(),                                                                 //路由
            json_encode(request()->param(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),     //请求参数
            json_encode($response->getData(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),   //响应数据

        ];
        Log::write(implode("|", $log), $type);
    }
}
