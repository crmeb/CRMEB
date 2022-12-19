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

use crmeb\exceptions\AdminException;
use app\services\other\CacheServices;
use think\facade\App;
use app\adminapi\controller\AuthController;
use app\services\system\config\SystemGroupDataServices;
use app\services\system\config\SystemGroupServices;

/**
 * 数据管理
 * Class SystemGroupData
 * @package app\adminapi\controller\v1\setting
 */
class SystemGroupData extends AuthController
{
    /**
     * 构造方法
     * SystemGroupData constructor.
     * @param App $app
     * @param SystemGroupDataServices $services
     */
    public function __construct(App $app, SystemGroupDataServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取数据列表头
     * @return mixed
     */
    public function header(SystemGroupServices $services)
    {
        [$gid, $config_name] = $this->request->getMore([
            ['gid', 0],
            ['config_name', '']
        ], true);
        if (!$gid && !$config_name) return app('json')->fail(100100);
        if (!$gid) {
            $gid = $services->value(['config_name' => $config_name], 'id');
        }
        return app('json')->success($services->getGroupDataTabHeader($gid));
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(SystemGroupServices $group)
    {
        $where = $this->request->getMore([
            ['gid', 0],
            ['status', ''],
            ['config_name', '']
        ]);
        if (!$where['gid'] && !$where['config_name']) return app('json')->fail(100100);
        if (!$where['gid']) {
            $where['gid'] = $group->value(['config_name' => $where['config_name']], 'id');
        }
        unset($where['config_name']);
        return app('json')->success($this->services->getGroupDataList($where));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $gid = $this->request->param('gid/d');
        if ($this->services->isGroupGidSave($gid, 4, 'index_categy_images')) {
            return app('json')->fail(400298);
        }
        if ($this->services->isGroupGidSave($gid, 7, 'sign_day_num')) {
            return app('json')->fail(400299);
        }
        return app('json')->success($this->services->createForm($gid));
    }

    /**
     * 保存新建的资源
     *
     * @return \think\Response
     */
    public function save(SystemGroupServices $services)
    {
        $params = request()->post();
        $gid = (int)$params['gid'];
        $group = $services->getOne(['id' => $gid], 'id,config_name,fields');
        if ($group && $group['config_name'] == 'order_details_images') {
            $groupDatas = $this->services->getColumn(['gid' => $gid], 'value', 'id');
            foreach ($groupDatas as $groupData) {
                $groupData = json_decode($groupData, true);
                if (isset($groupData['order_status']['value']) && $groupData['order_status']['value'] == $params['order_status']) {
                    return app('json')->fail(400188);
                }
            }
        }
        $this->services->checkSeckillTime($services, $gid, $params);
        $this->checkSign($services, $gid, $params);
        $fields = json_decode($group['fields'], true) ?? [];
        $value = [];
        foreach ($params as $key => $param) {
            foreach ($fields as $index => $field) {
                if ($key == $field["title"]) {
                    if ($param == "")
                        return app('json')->fail(400297);
                    else {
                        $value[$key]["type"] = $field["type"];
                        $value[$key]["value"] = $param;
                    }
                }
            }
        }
        $data = [
            "gid" => $params['gid'],
            "add_time" => time(),
            "value" => json_encode($value),
            "sort" => $params["sort"] ?: 0,
            "status" => $params["status"]
        ];
        $this->services->save($data);
        \crmeb\services\CacheService::clear();
        return app('json')->success(400189);
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $gid = $this->request->param('gid/d');
        if (!$gid) {
            return app('json')->fail(100100);
        }
        return app('json')->success($this->services->updateForm((int)$gid, (int)$id));
    }

    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update(SystemGroupServices $services, $id)
    {
        $groupData = $this->services->get($id);
        $fields = $services->getValueFields((int)$groupData["gid"]);
        $params = request()->post();
        $this->services->checkSeckillTime($services, $groupData["gid"], $params, $id);
        $this->checkSign($services, $groupData["gid"], $params);
        $value = [];
        foreach ($params as $key => $param) {
            foreach ($fields as $index => $field) {
                if ($key == $field["title"]) {
                    if ($param == '')
                        return app('json')->fail(400297);
                    else {
                        $value[$key]["type"] = $field["type"];
                        $value[$key]["value"] = $param;
                    }
                }
            }
        }
        $data = [
            "value" => json_encode($value),
            "sort" => $params["sort"],
            "status" => $params["status"]
        ];
        $this->services->update($id, $data);
        \crmeb\services\CacheService::clear();
        return app('json')->success(100001);
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$this->services->delete($id))
            return app('json')->fail(100008);
        else {
            \crmeb\services\CacheService::clear();
            return app('json')->success(100002);
        }
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        if ($status == '' || $id == 0) return app('json')->fail(100100);
        $this->services->update($id, ['status' => $status]);
        \crmeb\services\CacheService::clear();
        return app('json')->success(100014);
    }



    /**
     * 检查签到配置
     * @param SystemGroupServices $services
     * @param $gid
     * @param $params
     * @param int $id
     * @return mixed
     */
    public function checkSign(SystemGroupServices $services, $gid, $params, $id = 0)
    {
        $name = $services->value(['id' => $gid], 'config_name');
        if ($name == 'sign_day_num') {
            if (!$params['sign_num']) {
                throw new AdminException(400196);
            }
            if (!preg_match('/^\+?[1-9]\d*$/', $params['sign_num'])) {
                throw new AdminException(400197);
            }
        }
    }

    /**
     * 获取客服页面广告内容
     * @return mixed
     */
    public function getKfAdv()
    {
        /** @var CacheServices $cache */
        $cache = app()->make(CacheServices::class);
        $content = $cache->getDbCache('kf_adv', '');
        return app('json')->success(compact('content'));
    }

    /**
     * 设置客服页面广告内容
     * @return mixed
     */
    public function setKfAdv()
    {
        $content = $this->request->post('content');
        /** @var CacheServices $cache */
        $cache = app()->make(CacheServices::class);
        $cache->setDbCache('kf_adv', $content);
        return app('json')->success(100014);
    }

    public function saveAll()
    {
        $params = request()->post();
        if (!isset($params['config_name']) || !isset($params['data'])) {
            return app('json')->fail(100100);
        }
        $this->services->saveAllData($params['data'], $params['config_name']);
        return app('json')->success(400295);
    }


    /**
     * 获取用户协议内容
     * @return mixed
     */
    public function getUserAgreement()
    {
        /** @var CacheServices $cache */
        $cache = app()->make(CacheServices::class);
        $content = $cache->getDbCache('user_agreement', '');
        return app('json')->success(compact('content'));
    }

    /**
     * 设置用户协议内容
     * @return mixed
     */
    public function setUserAgreement()
    {
        $content = $this->request->post('content');
        /** @var CacheServices $cache */
        $cache = app()->make(CacheServices::class);
        $cache->setDbCache('user_agreement', $content);
        return app('json')->success(100014);
    }
}
