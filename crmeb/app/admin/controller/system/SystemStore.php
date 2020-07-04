<?php

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use crmeb\services\JsonService;
use crmeb\services\JsonService as Json;
use app\admin\model\system\SystemStore as SystemStoreModel;
use crmeb\services\UtilService;

/**
 * 门店管理控制器
 * Class SystemAttachment
 * @package app\admin\controller\system
 *
 */
class SystemStore extends AuthController
{

    /**
     * 门店列表
     */
    public function list()
    {
        $where = UtilService::getMore([
            ['page', 1],
            ['limit', 20],
            ['name', ''],
            ['excel', 0],
            ['type', $this->request->param('type')]
        ]);
        return JsonService::successlayui(SystemStoreModel::getStoreList($where));
    }

    /**
     * 门店设置
     * @return string
     */
    public function index()
    {
        $type = $this->request->param('type');
        $show = SystemStoreModel::where('is_show', 1)->where('is_del', 0)->count();//显示中的门店
        $hide = SystemStoreModel::where('is_show', 0)->count();//隐藏的门店
        $recycle = SystemStoreModel::where('is_del', 1)->count();//删除的门店
        if ($type == null) $type = 1;
        $this->assign(compact('type', 'show', 'hide', 'recycle'));
        return $this->fetch();
    }

    /**
     * 门店添加
     * @param int $id
     * @return string
     */
    public function add($id = 0)
    {
        $store = SystemStoreModel::getStoreDispose($id);
        $this->assign(compact('store'));
        return $this->fetch();
    }

    /**
     * 删除恢复门店
     * @param $id
     */
    public function delete($id)
    {
        if (!$id) return $this->failed('数据不存在');
        if (!SystemStoreModel::be(['id' => $id])) return $this->failed('产品数据不存在');
        if (SystemStoreModel::be(['id' => $id, 'is_del' => 1])) {
            $data['is_del'] = 0;
            if (!SystemStoreModel::edit($data, $id))
                return Json::fail(SystemStoreModel::getErrorInfo('恢复失败,请稍候再试!'));
            else
                return Json::successful('恢复门店成功!');
        } else {
            $data['is_del'] = 1;
            if (!SystemStoreModel::edit($data, $id))
                return Json::fail(SystemStoreModel::getErrorInfo('删除失败,请稍候再试!'));
            else
                return Json::successful('删除门店成功!');
        }
    }

    /**
     * 设置单个门店是否显示
     * @param string $is_show
     * @param string $id
     * @return json
     */
    public function set_show($is_show = '', $id = '')
    {
        ($is_show == '' || $id == '') && JsonService::fail('缺少参数');
        $res = SystemStoreModel::where(['id' => $id])->update(['is_show' => (int)$is_show]);
        if ($res) {
            return JsonService::successful($is_show == 1 ? '设置显示成功' : '设置隐藏成功');
        } else {
            return JsonService::fail($is_show == 1 ? '设置显示失败' : '设置隐藏失败');
        }
    }

    /**
     * 位置选择
     * @return string|void
     */
    public function select_address()
    {
        $key = sys_config('tengxun_map_key');
        if (!$key) return $this->failed('请前往设置->物流设置->物流配置 配置腾讯地图KEY', '#');
        $this->assign(compact('key'));
        return $this->fetch();
    }

    /**
     * 保存修改门店信息
     * @param int $id
     */
    public function save($id = 0)
    {
        $data = UtilService::postMore([
            ['name', ''],
            ['introduction', ''],
            ['image', ''],
            ['phone', ''],
            ['address', ''],
            ['detailed_address', ''],
            ['latlng', ''],
            ['valid_time', []],
            ['day_time', []],
        ]);
        SystemStoreModel::beginTrans();
        try {
            $data['address'] = implode(',', $data['address']);
            $data['latlng'] = is_string($data['latlng']) ? explode(',', $data['latlng']) : $data['latlng'];
            if (!isset($data['latlng'][0]) || !isset($data['latlng'][1])) return JsonService::fail('请选择门店位置');
            $data['latitude'] = $data['latlng'][0];
            $data['longitude'] = $data['latlng'][1];
            $data['valid_time'] = implode(' - ', $data['valid_time']);
            $data['day_time'] = implode(' - ', $data['day_time']);
            unset($data['latlng']);
            if ($data['image'] && strstr($data['image'], 'http') === false) {
                $site_url = sys_config('site_url');
                $data['image'] = $site_url . $data['image'];
            }
            if ($id) {
                if (SystemStoreModel::where('id', $id)->update($data)) {
                    SystemStoreModel::commitTrans();
                    return JsonService::success('修改成功');
                } else {
                    SystemStoreModel::rollbackTrans();
                    return JsonService::fail('修改失败或者您没有修改什么！');
                }
            } else {
                $data['add_time'] = time();
                $data['is_show'] = 1;
                if ($res = SystemStoreModel::create($data)) {
                    SystemStoreModel::commitTrans();
                    return JsonService::success('保存成功', ['id' => $res->id]);
                } else {
                    SystemStoreModel::rollbackTrans();
                    return JsonService::fail('保存失败！');
                }
            }
        } catch (\Exception $e) {
            SystemStoreModel::rollbackTrans();
            return JsonService::fail($e->getMessage());
        }
    }
}