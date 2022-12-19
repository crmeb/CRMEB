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

namespace app\services\system\store;


use app\dao\system\store\SystemStoreDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;

/**
 * 门店
 * Class SystemStoreServices
 * @package app\services\system\store
 * @method update($id, array $data, ?string $key = null) 修改数据
 * @method get(int $id, ?array $field = []) 获取数据
 */
class SystemStoreServices extends BaseServices
{
    /**
     * 构造方法
     * SystemStoreServices constructor.
     * @param SystemStoreDao $dao
     */
    public function __construct(SystemStoreDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取提货点列表
     * @param array $where
     * @param string $latitude
     * @param string $longitude
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStoreList(array $where, array $field = ['*'], string $latitude = '', string $longitude = '')
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getStoreList($where, $field, $page, $limit, $latitude, $longitude);
        foreach ($list as &$item) {
            if (isset($item['distance'])) {
                $item['range'] = bcdiv($item['distance'], '1000', 1);
            }
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 获取提货点头部统计信息
     * @return mixed
     */
    public function getStoreData()
    {
        $data['show'] = [
            'name' => '显示中的提货点',
            'num' => $this->dao->count(['type' => 0]),
        ];
        $data['hide'] = [
            'name' => '隐藏中的提货点',
            'num' => $this->dao->count(['type' => 1]),
        ];
        $data['recycle'] = [
            'name' => '回收站的提货点',
            'num' => $this->dao->count(['type' => 2])
        ];
        return $data;
    }

    /**
     * 保存或修改门店
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function saveStore(int $id, array $data)
    {
        return $this->transaction(function () use ($id, $data) {
            if ($id) {
                if ($this->dao->update($id, $data)) {
                    return true;
                } else {
                    throw new AdminException(100007);
                }
            } else {
                $data['add_time'] = time();
                $data['is_show'] = 1;
                if ($this->dao->save($data)) {
                    return true;
                } else {
                    throw new AdminException(100006);
                }
            }
        });
    }

    /**
     * 后台获取提货点详情
     * @param int $id
     * @param string $felid
     * @return array|false|mixed|string|string[]|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStoreDispose(int $id, string $felid = '')
    {
        if ($felid) {
            return $this->dao->value(['id' => $id], $felid);
        } else {
            $storeInfo = $this->dao->get($id);
            if ($storeInfo) {
                $storeInfo['latlng'] = $storeInfo['latitude'] . ',' . $storeInfo['longitude'];
                $storeInfo['dataVal'] = $storeInfo['valid_time'] ? explode(' - ', $storeInfo['valid_time']) : [];
                $storeInfo['timeVal'] = $storeInfo['day_time'] ? explode(' - ', $storeInfo['day_time']) : [];
                $storeInfo['address2'] = $storeInfo['address'] ? explode(',', $storeInfo['address']) : [];
                return $storeInfo;
            }
            return false;
        }
    }

    /**
     * 获取门店不分页
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStore()
    {
        return $this->dao->getStore(['type' => 0]);
    }

    /**
     * 获得导出店员列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getExportData(array $where)
    {
        return $this->dao->getStoreList($where, ['*']);
    }

}
