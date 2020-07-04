<?php


namespace app\admin\model\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use crmeb\services\PHPExcelService;

/**
 * 门店自提 model
 * Class SystemStore
 * @package app\admin\model\system
 */
class SystemStore extends BaseModel
{
    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'system_store';


    public static function getLatlngAttr($value, $data)
    {
        return $data['latitude'] . ',' . $data['longitude'];
    }

    public static function verificWhere()
    {
        return self::where('is_show', 1)->where('is_del', 0);
    }

    /**
     * 获取门店信息
     * @param int $id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getStoreDispose($id = 0)
    {
        if ($id)
            $storeInfo = self::verificWhere()->where('id', $id)->find();
        else
//            $storeInfo = self::verificWhere()->find();
            $storeInfo = [];
        if ($storeInfo) {
            $storeInfo['latlng'] = self::getLatlngAttr(null, $storeInfo);
            $storeInfo['valid_time'] = $storeInfo['valid_time'] ? explode(' - ', $storeInfo['valid_time']) : [];
            $storeInfo['day_time'] = $storeInfo['day_time'] ? explode(' - ', $storeInfo['day_time']) : [];
            $storeInfo['address'] = $storeInfo['address'] ? explode(',', $storeInfo['address']) : [];
        } else {
            $storeInfo['latlng'] = [];
            $storeInfo['valid_time'] = [];
            $storeInfo['valid_time'] = [];
            $storeInfo['day_time'] = [];
            $storeInfo['address'] = [];
            $storeInfo['id'] = 0;
        }
        return $storeInfo;
    }

    /**
     * 获取门店列表
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getStoreList($where)
    {
        $model = new self();
        if (isset($where['name']) && $where['name'] != '') {
            $model = $model->where('id|name|introduction', 'like', '%' . $where['name'] . '%');
        }
        if (isset($where['type']) && $where['type'] != '' && ($data = self::setData($where['type']))) {
            $model = $model->where($data);
        }
        $count = $model->count();
        $data = $model->page((int)$where['page'], (int)$where['limit'])->select();
        if ($where['excel'] == 1) {
            $export = [];
            foreach ($data as $index => $item) {
                $export[] = [
                    $item['name'],
                    $item['phone'],
                    $item['address'] .= ' ' . $item['detailed_address'],
                    $item['introduction'],
                    $item['day_time'],
                    $item['valid_time']
                ];
            }
            PHPExcelService::setExcelHeader(['门店名称', '门店电话', '门店地址', '门店简介', '营业时间', '核销日期'])
                ->setExcelTile('门店导出', '门店信息' . time(), ' 生成时间：' . date('Y-m-d H:i:s', time()))
                ->setExcelContent($export)
                ->ExcelSave();
        }
        return compact('count', 'data');
    }

    /**
     * 获取连表查询条件
     * @param $type
     * @return array
     */
    public static function setData($type)
    {
        switch ((int)$type) {
            case 1:
                $data = ['is_show' => 1, 'is_del' => 0];
                break;
            case 2:
                $data = ['is_show' => 0, 'is_del' => 0];
                break;
            case 3:
                $data = ['is_del' => 1];
                break;
        };
        return isset($data) ? $data : [];
    }

    public static function dropList()
    {
        $model = new self();
        $model = $model->where('is_del', 0);
        $list = $model->select()
            ->toArray();
        return $list;
    }
}