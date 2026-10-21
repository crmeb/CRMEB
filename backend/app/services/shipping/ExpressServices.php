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

namespace app\services\shipping;


use app\dao\shipping\ExpressDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\plugin\ModuleStateChecker;
use crmeb\services\CacheService;
use crmeb\services\logistics\Express;
use app\services\driver\express\ExpressDriverServices;
use app\services\driver\logistics\LogisticsDriverServices;
use crmeb\utils\FormBuilder as Form;

/**
 * 物流数据
 * Class ExpressServices
 * @package app\services\shipping
 * @method save(array $data) 保存数据
 * @method get(int $id, ?array $field = []) 获取数据
 * @method delete(int $id, ?string $key = null) 删除数据
 * @method update($id, array $data, ?string $key = null) 修改数据
 */
class ExpressServices extends BaseService
{
    public $_cacheKey = "plat_express_list";

    //物流查询物流公司code
    public $express_code = [
        'yunda' => 'yunda',
        'yundakuaiyun' => 'yunda56',
        'ems' => 'EMS',
        'youzhengguonei' => 'chinapost',
        'huitongkuaidi' => 'HTKY',
        'baishiwuliu' => 'BSKY',
        'shentong' => 'STO',
        'jd' => 'JD',
        'zhongtong' => 'ZTO',
        'zhongtongkuaiyun' => 'ZTO56',
    ];

     /**
       * @var ExpressDao
       */
    protected $dao;

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

    public function apiExpressList()
    {
        return $this->dao->getExpressList([], '*', 0, 0);
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
        if (isset($formData['net']) && $formData['net'] == 1) $field[] = Form::input('net_name', '取件网点', $formData['net_name'] ?? '')->required();
        if (isset($formData['check_man']) && $formData['check_man'] == 1) $field[] = Form::input('courier_name', '承载快递员名', $formData['courier_name'] ?? '')->required();
        if (isset($formData['partner_name']) && $formData['partner_name'] == 1) $field[] = Form::input('customer_name', '客户账户名称', $formData['customer_name'] ?? '')->required();
        if (isset($formData['is_code']) && $formData['is_code'] == 1) $field[] = Form::input('code_name', '电子面单承载编号', $formData['code_name'] ?? '')->required();
        $field[] = Form::number('sort', '排序', (int)($formData['sort'] ?? 0))->precision(0);
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
            throw new AdminException('数据不存在');
        }
        return create_form('编辑物流公司', $this->createExpressForm($express->toArray()), $this->url('/freight/express/' . $id), 'PUT');
    }

    /**
     * 平台获取快递
     * @return array|mixed
     */
    public function getPlatExpress()
    {
        /** @var ExpressDriverServices $driverServices */
        $driverServices = app()->make(ExpressDriverServices::class);
        /** @var Express $expressService */
        $expressService = $driverServices->createExpressService();
        /** @var CacheService $cacheService */
        $cacheService = app()->make(CacheService::class);
        $data = [];
        if ($list = $cacheService::get($this->_cacheKey)) {
            $data = json_decode($list, true);
        } else {
            $list = $expressService->express(0, 0, 1000);
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
        $list = $this->expressList($where);
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
            $data[] = ['label' => $value['name'], 'value' => $value['code']];
        }
        return $data;
    }

    public function expressList($where = [])
    {
        if (empty($where)) $where = ['is_show' => 1];
        return $this->dao->getExpressList($where, 'id,name,code,partner_id,partner_key,net,account,key,net_name', 0, 0);
    }

    /**
     * 解析当前启用的物流查询驱动
     * @return array 驱动配置；未启用或所属插件停用时返回空数组
     */
    protected function resolveEnabledQueryDriver(): array
    {
        /** @var LogisticsDriverServices $logisticsDriverServices */
        $logisticsDriverServices = app()->make(LogisticsDriverServices::class);
        $enabledDriver = $logisticsDriverServices->getEnabledDriver() ?? [];
        $driverKey = (string)($enabledDriver['driver_key'] ?? '');
        $ownerPluginId = (string)($enabledDriver['owner_plugin_id'] ?? '');
        // 插件或查询驱动关闭时，即使轨迹缓存仍存在也不返回旧数据。
        if ($driverKey === '' || $ownerPluginId === '' || !ModuleStateChecker::isDriverActive($ownerPluginId)) {
            return [];
        }
        if (!in_array($driverKey, ['aliyun_express', 'yihaotong'], true)) {
            throw new \RuntimeException('物流查询驱动不支持');
        }
        return $enabledDriver;
    }

    /**
     * 仅读取物流轨迹缓存，不请求第三方服务
     * 用于订单详情首屏：避免第三方物流接口的慢请求阻塞详情返回
     * @param string $cacheName 订单号 + 快递单号
     * @return array 已缓存的轨迹数据，未命中返回空数组
     */
    public function getCachedTracks(string $cacheName): array
    {
        try {
            $enabledDriver = $this->resolveEnabledQueryDriver();
            $driverKey = (string)($enabledDriver['driver_key'] ?? '');
            if ($driverKey === '') {
                return [];
            }
            $data = CacheService::get('logistics:' . $driverKey . ':' . $cacheName, null);
            return is_array($data) ? $data : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 物流公司查询
     * @param string $cacheName
     * @param string $expressNum
     * @param string|null $com
     * @param string $phone
     * @return array
     */
    public function query(string $cacheName, string $expressNum, ?string $com = null, $phone = '')
    {
        // 驱动契约要求快递编码和手机号为字符串；兼容历史订单/售后数据中的 null 值。
        $com = (string)($com ?? '');
        $phone = (string)($phone ?? '');

        $enabledDriver = $this->resolveEnabledQueryDriver();
        $driverKey = (string)($enabledDriver['driver_key'] ?? '');
        if ($driverKey === '') {
            return [];
        }

        // 按查询驱动隔离轨迹缓存，避免切换后复用其他服务商的数据。
        $driverCacheName = 'logistics:' . $driverKey . ':' . $cacheName;
        $resultData = CacheService::get($driverCacheName, null);
        if (!is_array($resultData) || empty($resultData)) {
            // 第三方无轨迹/请求失败时短缓存，避免详情页与物流页反复触发慢请求
            $emptyCacheName = $driverCacheName . ':empty:' . md5($phone . '|' . $com);
            if ((int)CacheService::get($emptyCacheName, 0) === 1) {
                return [];
            }
            $data = [];
            $cacheTime = 0;
            switch ($driverKey) {
                case 'aliyun_express':
                    $appCode = '';
                    foreach (($enabledDriver['config_fields'] ?? []) as $field) {
                        if (($field['field'] ?? '') === 'app_code') {
                            $appCode = (string)($field['value'] ?? '');
                            break;
                        }
                    }
                    /** @var Express $services */
                    $services = app()->make(Express::class, ['aliyun_express']);
                    $result = $services->query($expressNum, '', $appCode);
                    if (is_array($result) &&
                        isset($result['result']) &&
                        isset($result['result']['deliverystatus']) &&
                        $result['result']['deliverystatus'] >= 3)
                        $cacheTime = 0;
                    else
                        $cacheTime = 1800;
                    $data = $result['result']['list'] ?? [];
                    break;
                case 'yihaotong':
                    /** @var Express $services */
                    $services = app()->make(Express::class, [$driverKey]);
                    $result = $services->query($expressNum, $com, $phone);
                    if (isset($result['ischeck']) && $result['ischeck'] == 1) {
                        $cacheTime = 0;
                    } else {
                        $cacheTime = 1800;
                    }
                    foreach ($result['content'] ?? [] as $item) {
                        $data[] = ['time' => $item['time'], 'status' => $item['status']];
                    }
                    break;
                default:
                    throw new \RuntimeException('物流查询驱动不支持');
            }
            // 空轨迹通常表示手机号/快递编码尚未满足验单条件：按查询参数短缓存 120 秒，
            // 既避免详情页/物流页反复触发第三方慢请求，又允许后续携带更完整参数时重试。
            if ($data !== []) {
                CacheService::set($driverCacheName, $data, $cacheTime);
            } else {
                CacheService::set($emptyCacheName, 1, 120);
            }
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
        $expressList = $this->getPlatExpress();
        $data = $data_all = [];
        $this->dao->delete([['id', '>', 0]]);
        foreach ($expressList as $express) {
            $data['name'] = $express['name'] ?? '';
            $data['code'] = $express['code'] ?? '';
            $data['partner_id'] = $express['partner_id'] ?? '';
            $data['partner_key'] = $express['partner_key'] ?? '';
            $data['check_man'] = $express['check_man'] ?? '';
            $data['partner_name'] = $express['partner_name'] ?? '';
            $data['is_code'] = $express['is_code'] ?? '';
            $data['net'] = $express['net'] ?? '';
            $data['is_show'] = 0;
            $data['status'] = 0;
            if ($express['partner_id'] == 0 && $express['partner_key'] == 0 && $express['net'] == 0 && $express['check_man'] == 0 && $express['partner_name'] == 0 && $express['is_code'] == 0) {
                $data['status'] = 1;
            }
            $data_all[] = $data;
        }
        if ($data_all) {
            $this->dao->saveAll($data_all);
        }
        return true;
    }

    /** 查询单个快递公司
     * @param array $where
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOneByWhere(array $where)
    {
        return $this->dao->getOne($where);
    }


}
