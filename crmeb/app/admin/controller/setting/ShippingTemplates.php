<?php

namespace app\admin\controller\setting;

use app\admin\controller\AuthController;
use app\admin\model\system\ShippingTemplatesFree;
use app\admin\model\system\ShippingTemplatesRegion;
use crmeb\services\UtilService;
use crmeb\services\JsonService;
use app\admin\model\system\SystemCity;
use app\admin\model\system\ShippingTemplates as STModel;

class ShippingTemplates extends AuthController
{
    /**
     * 列表页面
     * @return string
     * @throws \Exception
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 添加页面
     * @return string
     * @throws \Exception
     */
    public function add($id = 0)
    {
        $this->assign(compact('id'));
        return $this->fetch();
    }

    /**
     * 修改
     * @return string
     * @throws \Exception
     */
    public function edit($id = 0)
    {
        $templates = STModel::get($id);
        if (!$templates) {
            return JsonService::fail('修改的模板不存在');
        }
        $data['appointList'] = ShippingTemplatesFree::getFreeList($id);
        $data['templateList'] = ShippingTemplatesRegion::getRegionList($id);
        if (!isset($data['templateList'][0]['region'])) {
            $data['templateList'][0]['region'] = ['city_id' => 0, 'name' => '默认全国'];
        }
        $data['formData'] = [
            'name' => $templates->name,
            'type' => $templates->getData('type'),
            'appoint_check' => $templates->getData('appoint'),
            'sort' => $templates->getData('sort'),
        ];
        return JsonService::successful($data);
    }

    /**
     * 选择城市页面
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function city()
    {
        $data = UtilService::getMore([
            ['type', 0],
            ['isedit', 0]
        ]);
        $this->assign('is_layui', true);
        $this->assign($data);
        return $this->fetch();
    }

    public function city_list()
    {
        $list = SystemCity::with('children')->where('parent_id', 0)->order('id asc')->select();
        return app('json')->success($list->toArray());
    }

    /**
     * 列表数据
     */
    public function temp_list()
    {
        $where = UtilService::postMore([
            ['page', 1],
            ['limit', 20],
            ['name', '']
        ]);
        return JsonService::successlayui(STModel::getList($where));
    }

    /**
     * 保存或者修改
     * @param int $id
     */
    public function save($id = 0)
    {
        $data = UtilService::postMore([
            ['region_info', []],
            ['appoint_info', []],
            ['sort', 0],
            ['type', 0],
            ['name', ''],
            ['appoint', 0],
        ]);
        $temp['name'] = $data['name'];
        $temp['type'] = $data['type'];
        $temp['appoint'] = $data['appoint'];
        $temp['sort'] = $data['sort'];
        $temp['add_time'] = time();
        STModel::beginTrans();
        $res = true;
        try {
            if ($id) {
                $res = STModel::where('id', $id)->update($temp);
            } else {
                $id = STModel::insertGetId($temp);
            }
            //设置区域配送
            $res = $res && ShippingTemplatesRegion::saveRegion($data['region_info'], $data['type'], $id);
            if (!$res) {
                STModel::rollbackTrans();
                return JsonService::fail(ShippingTemplatesRegion::getErrorInfo());
            }
            //设置指定包邮
            if ($data['appoint']) {
                $res = $res && ShippingTemplatesFree::saveFree($data['appoint_info'], $data['type'], $id);
            } else {
                if ($id) {
                    if (ShippingTemplatesFree::where('temp_id', $id)->count()) {
                        $res = $res && ShippingTemplatesFree::where('temp_id', $id)->delete();
                    }
                }
            }
            if ($res) {
                STModel::commitTrans();
                return JsonService::successful('保存成功');
            } else {
                STModel::rollbackTrans();
                return JsonService::fail(ShippingTemplatesFree::getErrorInfo('保存失败'));
            }
        } catch (\Throwable $e) {
            STModel::rollbackTrans();
            return JsonService::fail($e->getMessage());
        }
    }

    /**
     * 删除运费模板
     */
    public function delete()
    {
        $data = UtilService::getMore([
            ['id', ''],
        ]);
        if ($data['id'] == 1) {
            return JsonService::fail('默认模板不能删除');
        } else {
            STModel::del($data['id']);
            ShippingTemplatesRegion::where('temp_id', $data['id'])->delete();
            ShippingTemplatesFree::where('temp_id', $data['id'])->delete();
            return JsonService::successful('删除成功');
        }
    }

    /**
     * 获取所有运费模板
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_template_list()
    {
        return JsonService::successful(STModel::order('sort desc,id desc')->field(['id', 'name'])->select()->toArray());
    }


}