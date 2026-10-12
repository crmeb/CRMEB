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

namespace app\services\driver\express;

use app\dao\driver\express\ExpressCompanyDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;

/**
 * 电子面单物流公司管理服务
 * Class ExpressCompanyServices
 * @package app\services\driver\express
 */
class ExpressCompanyServices extends BaseService
{
    public function __construct(ExpressCompanyDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取物流公司列表
     * @param array $where
     * @return array
     */
    public function getList(array $where = [])
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, ['*'], $page, $limit);
        $count = $this->dao->count($where);
        // 绑定驱动补充中文名称
        $driverNames = app()->make(ExpressDriverServices::class)->getExpressDriverNames();
        foreach ($list as &$item) {
            $item['driver_name'] = $driverNames[$item['driver_key']] ?? $item['driver_key'];
        }
        unset($item);
        return compact('list', 'count');
    }

    /**
     * 获取物流公司详情
     * @param int $id
     * @return array
     */
    public function getDetail(int $id): array
    {
        $company = $this->dao->get($id);
        if (!$company) {
            throw new AdminException('物流公司不存在');
        }
        return $company->toArray();
    }

    /**
     * 新增物流公司
     * @param array $data
     * @return bool
     */
    public function createCompany(array $data)
    {
        $exist = $this->dao->getByCode($data['code']);
        if ($exist) {
            throw new AdminException('快递公司编码已存在');
        }
        $data['add_time'] = time();
        $data['update_time'] = time();
        $this->dao->save($data);
        CacheService::clear();
        return true;
    }

    /**
     * 修改物流公司
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateCompany(int $id, array $data)
    {
        $company = $this->dao->get($id);
        if (!$company) {
            throw new AdminException('物流公司不存在');
        }
        // 检查 code 唯一性（排除自身）
        if (isset($data['code'])) {
            $exist = $this->dao->getByCode($data['code']);
            if ($exist && $exist['id'] != $id) {
                throw new AdminException('快递公司编码已被占用');
            }
        }
        $data['update_time'] = time();
        $this->dao->update($id, $data);
        CacheService::clear();
        return true;
    }

    /**
     * 删除物流公司
     * @param int $id
     * @return bool
     */
    public function deleteCompany(int $id)
    {
        $company = $this->dao->get($id);
        if (!$company) {
            throw new AdminException('物流公司不存在');
        }
        $this->dao->delete($id);
        CacheService::clear();
        return true;
    }

    /**
     * 切换状态
     * @param int $id
     * @param int $status
     * @return bool
     */
    public function setStatus(int $id, int $status)
    {
        $company = $this->dao->get($id);
        if (!$company) {
            throw new AdminException('物流公司不存在');
        }
        $this->dao->update($id, ['status' => $status, 'update_time' => time()]);
        CacheService::clear();
        return true;
    }

    /**
     * 切换展示
     * @param int $id
     * @param int $isShow
     * @return bool
     */
    public function setIsShow(int $id, int $isShow)
    {
        $company = $this->dao->get($id);
        if (!$company) {
            throw new AdminException('物流公司不存在');
        }
        $this->dao->update($id, ['is_show' => $isShow, 'update_time' => time()]);
        CacheService::clear();
        return true;
    }

    /**
     * 排序
     * @param int $id
     * @param int $sort
     * @return bool
     */
    public function setSort(int $id, int $sort)
    {
        $this->dao->update($id, ['sort' => $sort, 'update_time' => time()]);
        CacheService::clear();
        return true;
    }

    /**
     * 获取启用的物流公司列表（供下拉选择）
     * @return array
     */
    public function getEnabledCompanies()
    {
        return $this->dao->getEnabledCompanies();
    }

    /**
     * 获取可用的驱动列表（供下拉选择）
     * @return array
     */
    public function getDriverOptions()
    {
        /** @var ExpressDriverServices $driverServices */
        $driverServices = app()->make(ExpressDriverServices::class);
        $drivers = $driverServices->getEnabledDrivers();
        $options = [];
        foreach ($drivers as $driver) {
            $options[] = [
                'value' => $driver['driver_key'],
                'label' => $driver['driver_name'],
            ];
        }
        return $options;
    }
}
