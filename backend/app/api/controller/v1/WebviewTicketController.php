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

namespace app\api\controller\v1;

use app\Request;
use app\services\user\WebviewTicketServices;

/**
 * 插件 webview 登录票据
 */
class WebviewTicketController
{
    /**
     * @var WebviewTicketServices
     */
    protected $services;

    public function __construct(WebviewTicketServices $services)
    {
        $this->services = $services;
    }

    /**
     * 已登录的小程序签发票据
     * @param Request $request
     * @return mixed
     */
    public function issue(Request $request)
    {
        [$path] = $request->postMore([
            ['path', ''],
        ], true);
        $ticket = $this->services->issue((int)$request->uid(), (string)$path);
        return app('json')->success(['ticket' => $ticket]);
    }

    /**
     * 插件 H5 用票据换 token
     * @param Request $request
     * @return mixed
     */
    public function exchange(Request $request)
    {
        [$ticket, $path] = $request->postMore([
            ['ticket', ''],
            ['path', ''],
        ], true);
        $data = $this->services->exchange((string)$ticket, (string)$path);
        return app('json')->success($data);
    }
}
