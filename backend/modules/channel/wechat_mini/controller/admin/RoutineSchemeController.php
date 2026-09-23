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
namespace modules\channel\wechat_mini\controller\admin;

use app\adminapi\controller\AuthController;
use modules\channel\wechat_mini\service\RoutineSchemeServices;
use think\facade\App;

class RoutineSchemeController extends AuthController
{
    /**
     * @var RoutineSchemeServices
     */
    protected $services;


    public function __construct(App $app, RoutineSchemeServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    public function schemeList()
    {
        $where = $this->request->postMore([
            ['title', ''],
        ]);
        return app('json')->success($this->services->schemeList($where));
    }

    public function schemeForm($id)
    {
        return app('json')->success($this->services->schemeForm($id));
    }

    public function schemeSave($id)
    {
        $data = $this->request->postMore([
            ['title', ''],
            ['path', ''],
            ['expire_type', -1],
            ['expire_num', 0],
        ]);
        $this->services->schemeSave($id, $data);
        return app('json')->success('保存成功');
    }

    public function schemeDel($id)
    {
        $res = $this->services->delete($id);
        if (!$res) return app('json')->fail('删除失败');
        return app('json')->success('删除成功');
    }
}
