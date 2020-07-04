<?php

namespace app\admin\controller\store;

use app\admin\controller\AuthController;
use app\Request;
use crmeb\traits\CurdControllerTrait;
use think\facade\Route as Url;
use app\models\store\StoreProductRule as ProductRuleModel;
use crmeb\services\{
    JsonService as Json, UtilService
};

/**
 * 评论管理 控制器
 * Class StoreProductReply
 * @package app\admin\controller\store
 */
class StoreProductRule extends AuthController
{

    use CurdControllerTrait;

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch();
        }
        $where = UtilService::getMore([
            ['page', 1],
            ['limit', 15],
            ['rule_name', '']
        ]);
        $list = ProductRuleModel::sysPage($where);
        return Json::successlayui($list);
    }

    /**
     * 创建
     * @param int $id
     * @return string
     * @throws \Exception
     */
    public function create($id = 0)
    {
        $this->assign(compact('id'));
        return $this->fetch();
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request, $id = 0)
    {
        $data = UtilService::postMore([
            ['rule_name', ''],
            ['rule_value', []]
        ]);
        if ($data['rule_name'] == '') return $this->fail('请输入规则名称');
        if (!$data['rule_value']) return $this->fail('缺少规则值');
        $data['rule_value'] = json_encode($data['rule_value']);
        if ($id) {
            $rule = ProductRuleModel::get($id);
            if (!$rule) return $this->fail('数据不存在');
            ProductRuleModel::edit($data, $id);
            return Json::success('编辑成功!');
        } else {
            ProductRuleModel::create($data);
            return Json::success('规则添加成功!');
        }
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        return Json::successful(ProductRuleModel::sysInfo($id));
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete()
    {
        $data = UtilService::postMore([
            ['ids', '']
        ]);
        if ($data['ids'] == '') return $this->fail('请至少选择一条数据');
        $ids = strval($data['ids']);
        $res = ProductRuleModel::whereIn('id', $ids)->delete();
        if (!$res)
            return Json::fail(ProductRuleModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::success('删除成功!');
    }

}