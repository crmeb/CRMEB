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
declare (strict_types = 1);

namespace app\services\diy;

use app\services\activity\StoreBargainServices;
use app\services\activity\StoreCombinationServices;
use app\services\activity\StoreSeckillServices;
use app\services\BaseServices;
use app\dao\diy\DiyDao;
use app\services\product\product\StoreProductServices;
use app\services\system\config\SystemGroupDataServices;
use app\services\system\config\SystemGroupServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;
use think\Log;

/**
 *
 * Class DiyServices
 * @package app\services\diy
 */
class DiyServices extends BaseServices
{

    /**
     * DiyServices constructor.
     * @param DiyDao $dao
     */
    public function __construct(DiyDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取DIY列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDiyList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $where['type'] = -1;
        $list = $this->dao->getDiyList($where, $page, $limit);
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 保存资源
     * @param int $id
     * @param array $data
     */
    public function saveData(int $id = 0, array $data)
    {
        if ($id) {
            $data['update_time'] = time();
            $res = $this->dao->update($id, $data);
        } else {
            $data['add_time'] = time();
            $data['update_time'] = time();
            $res = $this->dao->save($data);
        }
        if (!$res) throw new AdminException('保存失败');
    }

    /**
     * 删除DIY模板
     * @param int $id
     */
    public function del(int $id)
    {
        if ($id == 1) throw new AdminException('默认模板不能删除');
        $count = $this->dao->getCount(['id' => $id, 'status' => 1]);
        if ($count) throw new AdminException('该模板使用中，无法删除');
        $res = $this->dao->update($id, ['is_del' => 1]);
        if (!$res) throw new AdminException('删除失败，请稍后再试');
    }

    /**
     * 设置模板使用
     * @param int $id
     */
    public function setStatus(int $id)
    {
        $this->dao->update([['id', '<>', $id]], ['status' => 0]);
        $this->dao->update($id, ['status' => 1, 'type' => 0, 'update_time' => time()]);
    }

    /**
     * 获取页面数据
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDiy($name)
    {
        $data = [];
        if ($name == '') {
            $info = $this->dao->getOne(['status' => 1]);
        } else {
            $info = $this->dao->getOne(['template_name' => $name]);
        }

        if ($info) {
            $info = $info->toArray();
            if ($info['value']) {
                $data = json_decode($info['value'], true);
                foreach ($data as $key => &$item) {
                    if ($key == 'customerService') {
                        foreach ($item as $k => &$v) {
                            $v['routine_contact_type'] = sys_config('routine_contact_type', 0);
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 添加表单
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm()
    {
        $field = array();
        $title = '添加模板';
        $field[] = Form::input('name', '页面名称', '')->required();
        $field[] = Form::input('template_name', '页面类型', '')->required();
        return create_form($title, $field, Url::buildUrl('/diy/create'), 'POST');
    }

    /**
     * 获取商品数据
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function ProductList(array $where)
    {
        /** @var StoreProductServices $StoreProductServices */
        $StoreProductServices = app()->make(StoreProductServices::class);
        /** @var StoreBargainServices $StoreBargainServices */
        $StoreBargainServices = app()->make(StoreBargainServices::class);
        /** @var  $StoreCombinationServices StoreCombinationServices */
        $StoreCombinationServices = app()->make(StoreCombinationServices::class);
        /** @var  $StoreSeckillServices  StoreSeckillServices */
        $StoreSeckillServices = app()->make(StoreSeckillServices::class);
        $type = $where['type'];
        unset($where['type']);
        $data = [];
        switch ($type) {
            case 0:
                $data = $StoreProductServices->searchList($where);
                break;
            //秒杀
            case 2:
                $data = $StoreSeckillServices->getDiySeckillList($where);
                break;
            //拼团
            case 3:
                $data = $StoreCombinationServices->getDiyCombinationList($where);
                break;
            case 4:
                $where['is_hot'] = 1;
                $data = $StoreProductServices->searchList($where);
                break;
            case 5:
                $where['is_new'] = 1;
                $data = $StoreProductServices->searchList($where);
                break;
            case 6:
                $where['is_benefit'] = 1;
                $data = $StoreProductServices->searchList($where);
                break;
            case 7:
                $where['is_best'] = 1;
                $data = $StoreProductServices->searchList($where);
                break;
            //砍价
            case 8:
                $data = $StoreBargainServices->getDiyBargainList($where);
                break;
        }
        return $data;
    }

    /**
     * 前台获取首页数据接口
     * @param array $where
     */
    public function homeProductList(array $where, int $uid)
    {
        /** @var StoreProductServices $StoreProductServices */
        $StoreProductServices = app()->make(StoreProductServices::class);
        /** @var StoreBargainServices $StoreBargainServices */
        $StoreBargainServices = app()->make(StoreBargainServices::class);
        /** @var  $StoreCombinationServices StoreCombinationServices */
        $StoreCombinationServices = app()->make(StoreCombinationServices::class);
        /** @var  $StoreSeckillServices  StoreSeckillServices */
        $StoreSeckillServices = app()->make(StoreSeckillServices::class);
        $type = $where['type'];
        $data = [];
        switch ($type) {
            case 0:
                $where['type'] = $where['isType'] ?? 0;
                $data['list'] = $StoreProductServices->getGoodsList($where, $uid);
                break;
            //秒杀
            case 2:
                $data = $StoreSeckillServices->getHomeSeckillList($where);
                break;
            //拼团
            case 3:
                $data = $StoreCombinationServices->getHomeList($where);
                break;
            case 4:
                $where['is_hot'] = 1;
                $where['type'] = $where['isType'] ?? 0;
                $data['list'] = $StoreProductServices->getGoodsList($where, $uid);
                break;
            case 5:
                $where['is_new'] = 1;
                $where['type'] = $where['isType'] ?? 0;
                $data['list'] = $StoreProductServices->getGoodsList($where, $uid);
                break;
            case 6:
                $where['is_benefit'] = 1;
                $where['type'] = $where['isType'] ?? 0;
                $data['list'] = $StoreProductServices->getGoodsList($where, $uid);
                break;
            case 7:
                $where['is_best'] = 1;
                $where['type'] = $where['isType'] ?? 0;
                $data['list'] = $StoreProductServices->getGoodsList($where, $uid);
                break;
            //砍价
            case 8:
                $data = $StoreBargainServices->getHomeList($where);
                break;
        }
        return $data;
    }

    /**
     * 分类、个人中心、一键换色
     * @param string $name
     * @return mixed
     */
    public function getColorChange(string $name)
    {
        return $this->dao->value(['template_name' => $name, 'type' => 1], 'value');
    }

    public function getMemberData()
    {
        $info = $this->dao->get(['template_name' => 'member', 'type' => 1]);
        $status = (int)$info['value'];
        $order_status = $info['order_status'] ? (int)$info['order_status'] : 1;
        $color_change = (int)$this->getColorChange('color_change');
        /** @var SystemGroupDataServices $systemGroupDataServices */
        $systemGroupDataServices = app()->make(SystemGroupDataServices::class);
        /** @var SystemGroupServices $systemGroupServices */
        $systemGroupServices = app()->make(SystemGroupServices::class);
        $menus_gid = $systemGroupServices->value(['config_name' => 'routine_my_menus'], 'id');
        $banner_gid = $systemGroupServices->value(['config_name' => 'routine_my_banner'], 'id');
        $routine_my_menus = $systemGroupDataServices->getGroupDataList(['gid' => $menus_gid]);
        $routine_my_menus = $routine_my_menus['list'] ?? [];
        $routine_my_banner = $systemGroupDataServices->getGroupDataList(['gid' => $banner_gid]);
        $routine_my_banner = $routine_my_banner['list'] ?? [];
        $my_banner_status = $routine_my_banner[0]['status'] ?? 1;
        return compact('status', 'order_status', 'routine_my_menus', 'routine_my_banner', 'color_change', 'my_banner_status');
    }

    /**
     * 保存个人中心数据配置
     * @param array $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function memberSaveData(array $data)
    {
        /** @var SystemGroupDataServices $systemGroupDataServices */
        $systemGroupDataServices = app()->make(SystemGroupDataServices::class);
        if (!$data['status']) throw new AdminException('参数错误');
        $info = $this->dao->get(['template_name' => 'member', 'type' => 1]);
        if ($info) {
            $info->my_banner_status = $data['my_banner_status'];
            $info->value = $data['status'];
            $info->order_status = $data['order_status'];
            $info->update_time = time();
            $res = $info->save();
        } else {
            throw new AdminException('个人中心模板不存在');
        }
        if ($data['routine_my_banner']) $res1 = $systemGroupDataServices->saveAllData($data['routine_my_banner'], 'routine_my_banner');
        if ($data['routine_my_menus']) $res1 = $systemGroupDataServices->saveAllData($data['routine_my_menus'], 'routine_my_menus');
        return true;
    }

    /**
     * 获取底部导航
     * @param string $template_name
     * @return array|mixed
     */
    public function getNavigation(string $template_name)
    {
        if ($template_name) {
            $value = $this->dao->value(['template_name' => $template_name], 'value');
        } else {
            $value = $this->dao->value(['status' => 1, 'type' => 1], 'value');
            if (!$value) {
                $value = $this->dao->value(['template_name' => 'default'], 'value');
            }
        }
        $navigation = [];
        if ($value) {
            $value = json_decode($value, true);
            foreach ($value as $item) {
                if (isset($item['name']) && strtolower($item['name']) === 'pagefoot') {
                    $navigation = $item;
                    break;
                }
            }
        }
        return $navigation;
    }
}
