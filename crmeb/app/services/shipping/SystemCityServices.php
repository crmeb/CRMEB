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

namespace app\services\shipping;


use app\dao\shipping\SystemCityDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\FormBuilder as Form;

/**
 * 城市数据
 * Class SystemCityServices
 * @package app\services\shipping
 * @method deleteCity(int $cityId) 删除cityId下的数据
 * @method getCityIdMax() 获取最大的cityId
 * @method save(array $data) 保存数据
 * @method update($id, array $data, ?string $key = null) 修改数据
 * @method value(array $where, ?string $field = '') 获取一条数据
 * @method getShippingCity() 获取运费模板城市数据
 */
class SystemCityServices extends BaseServices
{
    /**
     * 构造方法
     * SystemCityServices constructor.
     * @param SystemCityDao $dao
     */
    public function __construct(SystemCityDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取城市数据
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCityList(array $where)
    {
//        $list = $this->dao->getCityList($where);
//        $cityIds = array_column($list, 'parent_id');
//        $cityNames = $this->dao->getCityArray(['city_id' => $cityIds], 'name', 'city_id');
//        foreach ($list as &$item) {
//            $item['parent_id'] = $cityNames[$item['parent_id']] ?? '中国';
//        }
//        return $list;
//        return CacheService::get('tree_city_list', function () {
        return $this->getSonCityList($where['parent_id']);
//        }, 86400);
    }

    /**
     * tree形城市列表
     * @param int $pid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSonCityList($pid = 0)
    {
        $list = $this->dao->getCityList(['parent_id' => $pid], 'id,city_id,level,name');
        $parent_name = $pid ? $this->dao->value(['city_id' => $pid], 'name') : '中国';
        $is_add = $pid == 0 || $this->dao->value(['city_id' => $pid], 'parent_id') == 0 ? 1 : 0;
        $arr = [];
        if ($list) {
            foreach ($list as $item) {
                $data = [];
                $data['id'] = $item['id'];
                $data['city_id'] = $item['city_id'];
                $data['label'] = $item['name'];
                $data['parent_name'] = $parent_name;
                if ($is_add) {
                    $data['children'] = [];
                    $data['_loading'] = false;
                }
                $arr [] = $data;
            }
        }
        return $arr;
    }

    /**
     * 添加城市数据表单
     * @param int $parentId
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function createCityForm(int $parentId)
    {
        if ($parentId) {
            $info = $this->dao->getOne(['city_id' => $parentId], 'level,city_id,name');
        } else {
            $info = ['level' => 0, 'city_id' => 0, 'name' => '中国'];
        }
        $field[] = Form::hidden('level', $info['level']);
        $field[] = Form::hidden('parent_id', $info['city_id']);
        $field[] = Form::input('parent_name', '上级名称', $info['name'])->disabled(true)->readonly(true);
        $field[] = Form::input('name', '名称')->required('请填写城市名称');
        return create_form('添加城市', $field, $this->url('/setting/city/save'));
    }

    /**
     * 添加城市数据创建
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function updateCityForm(int $id)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException(100026);
        }
        $info = $info->toArray();
        $info['parent_name'] = $this->dao->value(['city_id' => $info['parent_id']], 'name') ?: '中国';
        $field[] = Form::hidden('id', $info['id']);
        $field[] = Form::hidden('level', $info['level']);
        $field[] = Form::hidden('parent_id', $info['parent_id']);
        $field[] = Form::input('parent_name', '上级名称', $info['parent_name'])->readonly(true);
        $field[] = Form::input('name', '名称', $info['name'])->required('请填写城市名称');
        $field[] = Form::input('merger_name', '合并名称', $info['merger_name'])->placeholder('格式:陕西,西安,雁塔')->required('请填写合并名称');
        return create_form('修改城市', $field, $this->url('/setting/city/save'));
    }

    /**
     * 获取城市数据
     * @return mixed
     */
    public function cityList()
    {
        return CacheService::remember('CITY_LIST', function () {
            $allCity = $this->dao->getCityList([], 'city_id as v,name as n,parent_id');
            return sort_city_tier($allCity, 0);
        }, 0);
    }

}
