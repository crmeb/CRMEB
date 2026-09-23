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
use app\adminapi\validate\serve\ExpressValidata;
use app\adminapi\validate\serve\MealValidata;
use app\adminapi\validate\serve\ServeValidata;
use app\services\driver\express\ExpressConfigServices;
use app\services\system\config\SystemConfigServices;
use crmeb\services\CacheService;
use crmeb\services\yihaotong\Serve;
use crmeb\services\logistics\Express;
use think\facade\App;

/**
 * Class Serve
 * @package app\adminapi\controller\serve
 */
class ServeController extends AuthController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 检测登录
     * @return mixed
     */
    public function isLogin()
    {
        $sms_info = CacheService::get('yihaotong_appid');
        if ($sms_info) {
            return app('json')->success(['status' => true, 'info' => $sms_info]);
        } else {
            return app('json')->success(['status' => false]);
        }
    }

    /**
     * 获取套餐列表
     * @param string $type
     * @return mixed
     */
    public function mealList(string $type)
    {
        $res = app()->make(Serve::class, [yihaotong_config()])->mealList($type);
        if ($res) {
            return app('json')->success($res);
        } else {
            return app('json')->fail('获取套餐列表失败');
        }
    }

    /**
     * 获取支付码
     * @return mixed
     */
    public function payMeal()
    {
        $data = $this->request->postMore([
            ['meal_id', ''],
            ['price', ''],
            ['num', ''],
            ['type', ''],
            ['pay_type', ''],
        ]);
        $openInfo = app()->make(Serve::class, [yihaotong_config()])->getUser();
        if (!$openInfo) app('json')->fail('获取支付码失败');
        switch ($data['type']) {
            case "sms" :
                if (!$openInfo['sms']['open']) return app('json')->fail('请先开通短信服务');
                break;
            case "query" :
                if (!$openInfo['query']['open']) return app('json')->fail('请先开通物流查询服务');
                break;
            case "dump" :
                if (!$openInfo['dump']['open']) return app('json')->fail('请先开通电子面单打印服务');
                break;
            case "copy" :
                if (!$openInfo['copy']['open']) return app('json')->fail('请先开通商品采集服务');
                break;
        }
        $this->validate($data, MealValidata::class);

        $res = app()->make(Serve::class, [yihaotong_config()])->payMeal($data);
        if ($res) {
            return app('json')->success($res);
        } else {
            return app('json')->fail('获取支付码失败');
        }
    }

    /**
     * 开通打印电子面单
     * @return mixed
     */
    public function openExpress()
    {
        $data = $this->request->postMore([
            ['com', ''],
            ['temp_id', ''],
            ['to_name', ''],
            ['to_tel', ''],
            ['to_address', ''],
            ['siid', ''],
        ]);

        $this->validate($data, ExpressValidata::class);

        /** @var ExpressConfigServices $expressConfigService */
        $expressConfigService = app()->make(ExpressConfigServices::class);
        $expressConfigService->saveConfig($data);
        app()->make(Express::class, [yihaotong_config()])->open();
        return app('json')->success('开通成功');

    }

    /**
     * 获取用户信息，用户信息内包含是否开通服务字段
     * @return mixed
     */
    public function getUserInfo()
    {
        return app('json')->success(app()->make(Serve::class, [yihaotong_config()])->getUser());
    }

    /**
     * 查询记录
     * @return mixed
     */
    public function getRecord()
    {
        [$page, $limit, $type] = $this->request->getMore([
            [['page', 'd'], 0],
            [['limit', 'd'], 10],
            [['type', 'd'], 0],
        ], true);

        return app('json')->success(app()->make(Serve::class, [yihaotong_config()])->record($page, $limit, $type));
    }

    /**
     * 开通服务
     * @param int $type
     * @return mixed
     */
    public function openServe($type = 0)
    {
        if ($type) {
            app()->make(\driver\copyproduct\Yihaotong\Driver::class, [yihaotong_config()])->open();
        } else {
            app()->make(Express::class, [yihaotong_config()])->open();
        }

        return app('json')->success('开通成功');
    }

    /**
     * 修改密码
     * @return mixed
     */
    public function modify()
    {
        $data = $this->request->postMore([
            ['account', ''],
            ['password', ''],
            ['phone', ''],
            ['verify_code', ''],
        ]);

        $this->validate($data, ServeValidata::class);

        $data['password'] = md5($data['password']);
        app()->make(Serve::class, [yihaotong_config()])->modify($data);
        CacheService::delete('yihaotong_appid');
        return app('json')->success('修改成功');
    }

    /**
     * 修改手机号
     * @return mixed
     */
    public function updatePhone()
    {
        $data = $this->request->postMore([
            ['account', ''],
            ['phone', ''],
            ['verify_code', ''],
        ]);

        $this->validate($data, ServeValidata::class, 'phone');

        app()->make(Serve::class, [yihaotong_config()])->modifyPhone($data);
        CacheService::delete('yihaotong_appid');
        return app('json')->success('修改成功');
    }
}
