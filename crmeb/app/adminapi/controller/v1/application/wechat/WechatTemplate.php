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
namespace app\adminapi\controller\v1\application\wechat;

use app\adminapi\controller\AuthController;
use app\jobs\notice\SyncMessageJob;
use crmeb\exceptions\AdminException;
use app\services\message\TemplateMessageServices;
use crmeb\services\app\WechatService;
use think\facade\App;

/**
 * 微信模板消息
 * Class WechatTemplate
 * @package app\adminapi\controller\v1\application\wechat
 */
class WechatTemplate extends AuthController
{
    /**
     * 构造方法
     * WechatTemplate constructor.
     * @param App $app
     * @param TemplateMessageServices $services
     */
    public function __construct(App $app, TemplateMessageServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 同步微信模版消息
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function syncSubscribe()
    {
        if (!sys_config('wechat_appid') || !sys_config('wechat_appsecret')) {
            throw new AdminException(400248);
        }
        $all = $this->services->getTemplateList(['status' => 1, 'type' => 1]);
        $list = WechatService::getPrivateTemplates();
        foreach ($list->template_list as $v) {
            WechatService::deleleTemplate($v['template_id']);
        }
        foreach ($all['list'] as $template) {
            SyncMessageJob::dispatch('SyncWechat', [$template]);
        }
        return app('json')->success(100038);
    }
}
