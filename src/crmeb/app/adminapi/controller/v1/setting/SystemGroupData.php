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
        $gid = $this->request->param('gid/d');
        if (!$gid) return app('json')->fail('参数错误');
        return app('json')->success($services->getGroupDataTabHeader($gid));
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['gid', 0],
            ['status', ''],
        ]);
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
            return app('json')->fail('不能大于四个！');
        }
        if ($this->services->isGroupGidSave($gid, 7, 'sign_day_num')) {
            return app('json')->fail('签到天数配置不能大于7天');
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
                    return app('json')->fail('已存在请不要重复添加');
                }
            }
        }
        $this->checkSeckillTime($services, $gid, $params);
        $this->checkSign($services, $gid, $params);
        $fields = json_decode($group['fields'], true) ?? [];
        $value = [];
        foreach ($params as $key => $param) {
            foreach ($fields as $index => $field) {
                if ($key == $field["title"]) {
                    if ($param == "")
                        return app('json')->fail($field["name"] . "不能为空！");
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
        return app('json')->success('添加数据成功!');
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
        if(!$gid){
            return app('json')->fail('缺少参数');
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
        $this->checkSeckillTime($services, $groupData["gid"], $params, $id);
        $this->checkSign($services, $groupData["gid"], $params);
        foreach ($params as $key => $param) {
            foreach ($fields as $index => $field) {
                if ($key == $field["title"]) {
                    if ($param == '')
                        return app('json')->fail($field["name"] . "不能为空！");
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
        return app('json')->success('修改成功!');
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
            return app('json')->fail('删除失败,请稍候再试!');
        else {
            \crmeb\services\CacheService::clear();
            return app('json')->success('删除成功!');
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
        if ($status == '' || $id == 0) return app('json')->fail('参数错误');
        $this->services->update($id, ['status' => $status]);
        \crmeb\services\CacheService::clear();
        return app('json')->success($status == 0 ? '隐藏成功' : '显示成功');
    }

    /**
     * 检查秒杀时间段
     * @param SystemGroupServices $services
     * @param $gid
     * @param $params
     * @param int $id
     * @return mixed
     */
    public function checkSeckillTime(SystemGroupServices $services, $gid, $params, $id = 0)
    {
        $name = $services->value(['id' => $gid], 'config_name');
        if ($name == 'routine_seckill_time') {
            if ($params['time'] == '') {
                throw new AdminException('请输入开始时间');
            }
            if (!$params['continued']) {
                throw new AdminException('请输入持续时间');
            }
            if (!preg_match('/^(\d|1\d|2[0-3])$/', $params['time'])) {
                throw new AdminException('请输入0-23点之前的整点数');
            }
            if (!preg_match('/^([1-9]|1\d|2[0-4])$/', $params['continued'])) {
                throw new AdminException('请输入1-24点之前的持续时间');
            }
            if (($params['time'] + $params['continued']) > 24) throw new AdminException('开始时间+持续时间不能大于24小时');
            $list = $this->services->getColumn(['gid' => $gid], 'value', 'id');
            if ($id) unset($list[$id]);
            $times = $time = [];
            foreach ($list as $item) {
                $info = json_decode($item, true);
                for ($i = 0; $i < $info['continued']['value']; $i++) {
                    $times[] = $info['time']['value'] + $i;
                }
            }
            for ($i = 0; $i < $params['continued']; $i++) {
                $time[] = $params['time'] + $i;
            }
            foreach ($time as $v) {
                if (in_array($v, $times)) throw new AdminException('时段已占用');
            }
        }
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
                throw new AdminException('请输入签到赠送积分');
            }
            if (!preg_match('/^\+?[1-9]\d*$/', $params['sign_num'])) {
                throw new AdminException('请输入大于等于0的整数');
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
        return app('json')->success('设置成功');
    }
}
