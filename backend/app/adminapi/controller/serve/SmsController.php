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

namespace app\adminapi\controller\serve;


use app\adminapi\controller\AuthController;
use app\adminapi\validate\serve\ServeValidata;
use crmeb\services\sms\Sms;
use think\facade\App;

/**
 * Class Sms
 * @package app\adminapi\controller\serve
 */
class SmsController extends AuthController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 开通服务
     * @param string $sign
     * @return mixed
     */
    public function openServe(string $sign)
    {
        if (!$sign) {
            return app('json')->fail('请设置短信签名');
        }
        app()->make(Sms::class, [yihaotong_config()])->setSign($sign)->open();
        return app('json')->success('开通成功');
    }

    /**
     * 修改短信签名
     * @param string $sign
     * @return mixed
     */
    public function editSign(string $sign)
    {
        [$sign, $phone, $code] = $this->request->postMore([
            ['sign', ''],
            ['phone', ''],
            ['code', ''],
        ], true);

        $this->validate(['phone' => $phone], ServeValidata::class, 'phone');

        if (!$sign) {
            return app('json')->fail('请设置短信签名');
        }
        app()->make(Sms::class, [yihaotong_config()])->modify($sign, $phone, $code);
        return app('json')->success('修改短信签名成功');
    }

    /**
     * 获取短信模板
     * @return mixed
     */
    public function temps()
    {
        [$page, $limit, $type] = $this->request->getMore([
            ['page', 1],
            ['limit', 10],
            ['temp_type', 0],
        ], true);

        $list = app()->make(Sms::class, [yihaotong_config()])->temps((int)$page, (int)$limit, (int)$type);
        foreach ($list['data'] as &$item) {
            $item['templateid'] = $item['temp_id'];
            switch ((int)$item['temp_type']) {
                case 1:
                    $item['type'] = '验证码';
                    break;
                case 2:
                    $item['type'] = '通知';
                    break;
                case 30:
                    $item['type'] = '营销短信';
                    break;
            }
        }
        return app('json')->success($list);
    }

    /**
     * 申请模板
     * @return mixed
     */
    public function apply()
    {
        [$title, $content, $type] = $this->request->postMore([
            ['title', ''],
            ['content', ''],
            ['type', 0]
        ], true);

        if (!$title || !$content || !$type) {
            return app('json')->success('请输入模板内容');
        }
        return app('json')->success(app()->make(Sms::class, [yihaotong_config()])->apply($title, $content, (int)$type));
    }

    /**
     * 获取申请记录
     * @return mixed
     */
    public function applyRecord()
    {
        [$page, $limit, $tempType] = $this->request->getMore([
            [['page', 'd'], 1],
            [['limit', 'd'], 10],
            [['temp_type', 'd'], 0],
        ], true);

        return app('json')->success(app()->make(Sms::class, [yihaotong_config()])->applys($tempType, $page, $limit));
    }
}
