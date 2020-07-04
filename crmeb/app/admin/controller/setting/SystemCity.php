<?php

namespace app\admin\controller\setting;

use app\admin\controller\AuthController;
use app\admin\model\system\SystemCity as CityModel;
use crmeb\services\CacheService;
use crmeb\services\FormBuilder as Form;
use crmeb\services\JsonService as Json;
use crmeb\services\UtilService as Util;
use think\facade\Route as Url;

class SystemCity extends AuthController
{
    /**
     * 城市列表
     * @return string
     * @throws \Exception
     */
    public function index()
    {
        $params = Util::getMore([
            ['parent_id', 0]
        ], $this->request);
        $this->assign('pid',$params['parent_id']);
        $this->assign('list', CityModel::getList($params));
        $addurl = Url::buildUrl('add?parent_id=' . $params['parent_id']);
        $this->assign(compact('params', 'addurl'));
        return $this->fetch();
    }

    /**
     * 添加城市
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        $data = Util::getMore([
            ['parent_id', 0]
        ]);
        if ($data['parent_id'] != 0) {
            $info = CityModel::where('city_id', $data['parent_id'])->find()->toArray();
        } else {
            $info = [
                "level" => 0,
                "city_id" => 0,
                "name" => '中国'
            ];
        }
        $field[] = Form::hidden('level', $info['level']);
        $field[] = Form::hidden('parent_id', $info['city_id']);
        $field[] = Form::input('parent_name', '上级名称', $info['name'])->readonly(true);
        $field[] = Form::input('name', '名称');
        $field[] = Form::input('merger_name', '合并名称')->placeholder('格式:陕西,西安,雁塔');
        $field[] = Form::input('area_code', '区号');
        $field[] = Form::input('lng', '经度');
        $field[] = Form::input('lat', '纬度');
        $form = Form::make_post_form('添加地区', $field, Url::buildUrl('save'), 3);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存
     */
    public function save()
    {
        $data = Util::postMore([
            ['id', 0],
            ['name', 0],
            ['merger_name', 0],
            ['area_code', 0],
            ['lng', 0],
            ['lat', 0],
            ['level', 0],
            ['parent_id', 0],
        ]);
        if (!$data['name']) return Json::fail('请输入城市名称');
        if (!$data['merger_name']) return Json::fail('请输入城市合并名称');
        if ($data['id'] == 0) {
            unset($data['id']);
            $data['level'] = $data['level'] + 1;
            $data['city_id'] = intval(CityModel::max('city_id') + 1);
            CityModel::create($data);
            return Json::successful('添加城市成功!');
        } else {
            unset($data['level']);
            unset($data['parent_id']);
            CityModel::where('id', $data['id'])->update($data);
            return Json::successful('修改城市成功!');
        }
    }

    /**
     * 修改城市
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $data = Util::getMore([
            ['id', 0]
        ]);
        $info = CityModel::get($data['id'])->toArray();
        $info['parent_name'] = CityModel::where('city_id', $info['parent_id'])->value('name') ? : '中国';
        $field[] = Form::hidden('id', $info['id']);
        $field[] = Form::hidden('level', $info['level']);
        $field[] = Form::hidden('parent_id', $info['parent_id']);
        $field[] = Form::input('parent_name', '上级名称', $info['parent_name'])->readonly(true);
        $field[] = Form::input('name', '名称', $info['name']);
        $field[] = Form::input('merger_name', '合并名称', $info['merger_name'])->placeholder('格式:陕西,西安,雁塔');
        $field[] = Form::input('area_code', '区号', $info['area_code']);
        $field[] = Form::input('lng', '经度', $info['lng']);
        $field[] = Form::input('lat', '纬度', $info['lat']);
        $form = Form::make_post_form('添加地区', $field, Url::buildUrl('save'), 3);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 删除城市
     * @throws \Exception
     */
    public function delete()
    {
        $data = Util::getMore([
            ['city_id', 0]
        ]);
        CityModel::where('city_id', $data['city_id'])->whereOr('parent_id', $data['city_id'])->delete();
        return Json::successful('删除成功!');
    }

    /**
     * 清除城市缓存
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function clean_cache()
    {
        $res = CacheService::delete('CITY_LIST');
        if ($res) {
            return Json::successful('清除成功!');
        } else {
            return Json::fail('清除失败!');
        }
    }
}