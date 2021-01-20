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

namespace app\services\shipping;


use app\dao\shipping\ExpressDao;
use app\services\BaseServices;
use app\services\serve\ServeServices;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\express\storage\Express;
use crmeb\services\ExpressService;
use crmeb\services\FormBuilder as Form;
use crmeb\services\sms\Sms;

/**
 * 物流数据
 * Class ExpressServices
 * @package app\services\shipping
 * @method save(array $data) 保存数据
 * @method get(int $id, ?array $field = []) 获取数据
 * @method delete(int $id, ?string $key = null) 删除数据
 * @method update($id, array $data, ?string $key = null) 修改数据
 */
class ExpressServices extends BaseServices
{
    public $_cacheKey = "plat_express_list";

    /**
     * 构造方法
     * ExpressServices constructor.
     * @param ExpressDao $dao
     */
    public function __construct(ExpressDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取物流信息
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getExpressList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getExpressList($where, '*', $page, $limit);
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 物流表单
     * @param array $formData
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createExpressForm(array $formData = [])
    {
        if (isset($formData['partner_id']) && $formData['partner_id'] == 1) $field[] = Form::input('account', '月结账号', $formData['account'] ?? '');
        if (isset($formData['partner_key']) && $formData['partner_key'] == 1) $field[] = Form::input('key', '月结密码', $formData['key'] ?? '');
        if (isset($formData['net']) && $formData['net'] == 1) $field[] = Form::input('net_name', '取件网点', $formData['net_name'] ?? '');
        $field[] = Form::number('sort', '排序', (int)($formData['sort'] ?? 0));
        $field[] = Form::radio('is_show', '是否启用', $formData['is_show'] ?? 1)->options([['value' => 0, 'label' => '隐藏'], ['value' => 1, 'label' => '启用']]);
        return $field;
    }

    /**
     * 创建物流信息表单获取
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm()
    {
        return create_form('添加物流公司', $this->createExpressForm(), $this->url('/freight/express'));
    }

    /**
     * 修改物流信息表单获取
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function updateForm(int $id)
    {
        $express = $this->dao->get($id);
        if (!$express) {
            throw new AdminException('查询数据失败,无法修改');
        }
        return create_form('编辑物流公司', $this->createExpressForm($express->toArray()), $this->url('/freight/express/' . $id), 'PUT');
    }

    /**
     * 平台获取快递
     * @return array|mixed
     */
    public function getPlatExpress()
    {
        /** @var ServeServices $expressService */
        $expressService = app()->make(ServeServices::class);
        /** @var CacheService $cacheService */
        $cacheService = app()->make(CacheService::class);
        $data = [];
        if ($list = $cacheService::get($this->_cacheKey)) {
            $data = json_decode($list, true);
        } else {
            $list = $expressService->express()->express(1, 0, 1000);
            if (isset($list['data'])) {
                $cacheService->set($this->_cacheKey, json_encode($list['data']), 3600);
                $data = $list['data'];
            }
        }
        return $data;
    }

    /**
     * 获取物流信息组合成新的数组返回
     * @param array $where
     * @return array
     */
    public function express(array $where = [], string $k = 'id')
    {
        $list = $this->expressList();
        $data = [];
        if ($list) {
            foreach ($list as $k => $v) {
                $data[$k]['id'] = $v['id'];
                $data[$k]['value'] = $v['name'];
                $data[$k]['code'] = $v['code'];
            }
        }
        return $data;
    }

    /**
     * 获取物流信息组合成新的数组返回
     * @param array $where
     * @return array
     */
    public function expressSelectForm(array $where = [])
    {
        $list = $this->expressList();
        //$list = $this->dao->getExpress($where, 'name', 'id');
        $data = [];
        foreach ($list as $key => $value) {
            $data[] = ['label' => $value['name'], 'value' => $value['id']];
        }
        return $data;
    }

    public function expressList()
    {
        return $this->dao->getExpressList(['is_show' => 1, 'status' => 1], 'id,name,code,partner_id,partner_key,net,account,key,net_name', 0, 0);
//        return $this->getPlatExpress();
    }

    /**
     * 物流公司查询
     * @param string $cacheName
     * @param string $expressNum
     * @param string|null $com
     * @return array
     */
    public function query(string $cacheName, string $expressNum, string $com = null)
    {
        $resultData = CacheService::get($cacheName, null);
        if ($resultData === null || !is_array($resultData)) {
            $data = [];
            switch ((int)sys_config('logistics_type')) {
                case 1:
                    /** @var ServeServices $services */
                    $services = app()->make(ServeServices::class);
                    $result = $services->express()->query($expressNum, $com);
                    if (isset($result['ischeck']) && $result['ischeck'] == 1) {
                        $cacheTime = 0;
                    } else {
                        $cacheTime = 1800;
                    }
                    foreach (isset($result['content']) ? $result['content'] : [] as $item) {
                        $data[] = ['time' => $item['time'], 'status' => $item['status']];
                    }
                    break;
                case 2:
                    $result = ExpressService::query($expressNum);
                    if (is_array($result) &&
                        isset($result['result']) &&
                        isset($result['result']['deliverystatus']) &&
                        $result['result']['deliverystatus'] >= 3)
                        $cacheTime = 0;
                    else
                        $cacheTime = 1800;
                    $data = $result['result']['list'] ?? [];
                    break;
            }
            CacheService::set($cacheName, $data, $cacheTime);
            return $data;
        }

        return $resultData;
    }

    /**
     * 同步物流公司
     * @return bool
     */
    public function syncExpress()
    {
        if (CacheService::get('sync_express')) {
            return true;
        }
        $expressList = $this->getPlatExpress();
        $data = $data_all = [];
        $selfExpress = $this->dao->getExpress([], 'id,code', 'id');
        $codes = [];
        if ($selfExpress) {
            $codes = array_column($selfExpress, 'code');
        }
        foreach ($expressList as $express) {
            if (!in_array($express['code'], $codes)) {
                $data['name'] = $express['name'] ?? '';
                $data['code'] = $express['code'] ?? '';
                $data['partner_id'] = $express['partner_id'] ?? '';
                $data['partner_key'] = $express['partner_key'] ?? '';
                $data['net'] = $express['net'] ?? '';
                $data['is_show'] = 1;
                $data['status'] = 0;
                if ($express['partner_id'] == 0 && $express['partner_key'] == 0 && $express['net'] == 0) {
                    $data['status'] = 1;
                }
                $data_all[] = $data;
            }
        }
        if ($data_all) {
            $this->dao->saveAll($data_all);
        }
        CacheService::set('sync_express', 1, 3600);
        return true;
    }

}
