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
namespace app\adminapi\controller\v1\setting;

use app\adminapi\controller\AuthController;
use app\services\message\SystemNotificationServices;
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
        if (!$where['id']) return app('json')->fail(100100);
        return app('json')->success($this->services->getNotInfo($where));
    }

    /**
     * 保存新建的资源
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
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
            ['sms_id', ''],
        ]);
        if ($data['mark'] == 'verify_code') $data['type'] = 'is_sms';
        if (!$data['id']) return app('json')->fail(100100);
        if ($this->services->saveData($data)) {
            CacheService::delete('NOTICE_SMS_' . $data['mark']);
            CacheService::delete('wechat_' . $data['mark']);
            CacheService::delete('routine_' . $data['mark']);
            CacheService::delete('TEMP_IDS_LIST');
            return app('json')->success(100001);
        } else {
            return app('json')->fail(100007);
        }
    }

    /**
     * 修改消息状态
     * @param $type
     * @param $status
     * @param $id
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function set_status($type, $status, $id)
    {
        if ($type == '' || $status == '' || $id == 0) return app('json')->fail(100100);
        $this->services->update($id, [$type => $status]);
        $res = $this->services->getOneNotce(['id' => $id]);
        CacheService::delete('NOTICE_SMS_' . $res->mark);
        CacheService::delete('wechat_' . $res->mark);
        CacheService::delete('routine_' . $res->mark);
        CacheService::delete('TEMP_IDS_LIST');
        return app('json')->success(100014);
    }
}
