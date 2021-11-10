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
namespace app\adminapi\controller\v1\setting;

use app\adminapi\controller\AuthController;
use app\services\system\SystemNotificationServices;
use crmeb\services\CacheService;
use think\facade\App;

/**
 * Class SystemRole
 * @package app\adminapi\controller\v1\setting
 */
class SystemNotification extends AuthController
{
    /**
     * SystemRole constructor.
     * @param App $app
     * @param SystemNotificationServices $services
     */
    public function __construct(App $app, SystemNotificationServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['type', ''],
        ]);
        return app('json')->success($this->services->getNotList($where));
    }

    /**
     * 显示编辑
     *
     * @return \think\Response
     */
    public function info()
    {
        $where = $this->request->getMore([
            ['type', ''],
            ['id', 0]
        ]);
        if (!$where['id']) return app('json')->fail('参数错误');
        return app('json')->success($this->services->getNotInfo($where));
    }

    /**
     * 保存新建的资源
     *
     * @return \think\Response
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['type', ''],
            ['name', ''],
            ['title', ''],
            ['is_system', 0],
            ['is_app', 0],
            ['is_wechat', 0],
            ['is_routine', 0],
            ['is_sms', 0],
            ['is_ent_wechat', 0],
            ['system_title', ''],
            ['system_text', ''],
            ['tempid', ''],
            ['ent_wechat_text', ''],
            ['url', ''],
            ['wechat_id', ''],
            ['routine_id', ''],
            ['mark', ''],
        ]);
        if (!$data['id']) return app('json')->fail('参数错误');
        if ($this->services->saveData($data)) {
            CacheService::delete('NOTCE_'. $data['mark']);
            return app('json')->success('修改成功!');
        } else {
            return app('json')->fail('修改失败,请稍候再试!');
        }
    }

    /**
     * 修改消息状态
     *
     * @return array
     */
    public function set_status($type, $status, $id)
    {
        if ($type == '' || $status == '' || $id == 0) return app('json')->fail('参数错误');
        $this->services->update($id, [$type => $status]);
        $res = $this->services->getOneNotce(['id'=>$id]);
        CacheService::delete('NOTCE_'.$res->mark);
        return app('json')->success($status == 1 ? '开启成功' : '关闭成功');
    }
}
