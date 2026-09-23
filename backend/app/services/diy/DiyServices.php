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
declare (strict_types=1);

namespace app\services\diy;

use crmeb\basic\BaseService;
use app\dao\diy\DiyDao;
use app\services\other\QrcodeServices;
use app\services\product\CenterProductServices;
use app\services\system\config\SystemGroupDataServices;
use app\services\system\config\SystemGroupServices;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\utils\FormBuilder as Form;
use think\facade\Route as Url;

/**
 *
 * Class DiyServices
 * @package app\services\diy
 */
class DiyServices extends BaseService
{
    /**
     * @var DiyDao
     */
    protected $dao;

    /**
     * 通过事件代理查询活动数据，避免 app 层直接引用 modules
     * @param string $mode 活动类型（seckill/combination/bargain）
     * @param string $action 查询方法名
     * @param array $where 查询条件
     * @return array 查询结果
     */
    private function proxyActivityData(string $mode, string $action, array $where = []): array
    {
        $results = event('ActivityDataProxy', ['mode' => $mode, 'action' => $action, 'where' => $where]);
        if (is_array($results)) {
            foreach ($results as $result) {
                if ($result !== null) {
                    return is_array($result) ? $result : [];
                }
            }
        }
        return [];
    }

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
        $where['is_del'] = 0;
        if ($where['type'] == 2) $limit = 1000;
        $list = $this->dao->getDiyList($where, ['id', 'name', 'type', 'add_time', 'update_time', 'is_diy', 'status'], $page, $limit);
        foreach ($list as &$item) {
            $item['type_name'] = $item['type'] == 0 ? '可视化' : '专题页';
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 保存资源
     * @param int $id
     * @param array $data
     */
    public function saveData(int $id = 0, array $data = [])
    {
        if ($id) {
            $data['update_time'] = time();
            $res = $this->dao->update($id, $data);
            if (!$res) throw new AdminException('修改失败');
        } else {
            $data['add_time'] = time();
            $data['update_time'] = time();
            $res = $this->dao->save($data);
            if (!$res) throw new AdminException('保存失败');
            $id = $res->id;
        }

        return $id;
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
        if (!$res) throw new AdminException('删除失败');

        CacheService::clear();
    }

    /**
     * 设置模板使用
     * @param int $id
     */
    public function setStatus(int $id)
    {
        $this->dao->update(['is_diy' => 1], ['is_show' => 1, 'type' => 2]);
        $this->dao->update([['id', '<>', $id]], ['status' => 0]);
        $this->dao->update($id, ['status' => 1, 'update_time' => time()]);
    }

    /**
     * @param int $id
     * @return array|mixed|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/05/08
     */
    public function getDiyVersion(int $id)
    {
        if ($id) {
            $where = ['id' => $id];
        } else {
            $where = ['status' => 1, 'is_del' => 0];
        }
        $data = $this->dao->getOne($where, 'version,is_diy');
        if (isset($data['version']) && isset($data['is_diy'])) {
            return $data;
        } else {
            return $this->dao->getOne($where, 'version,is_diy');
        }
    }

    /**
     * 获取页面数据
     * @param int $id
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDiy($id = 0)
    {
        $field = 'name,value,is_show,is_bg_color,color_picker,bg_pic,bg_tab_val,is_bg_pic,order_status,is_diy,title';

        if ($id) {
            $info = $this->dao->getOne(['id' => $id], $field);
        } else {
            $info = $this->dao->getOne(['status' => 1, 'is_del' => 0], $field);
        }
        $info = $info ? $info->toArray() : [];

        if ($info) {
            if ($info['value']) {
                $info['value'] = json_decode($info['value'], true);
                if ($info['is_diy']) {
                    foreach ($info['value'] as &$item) {
                        if ($item['name'] == 'customerService') {
                            $item['routine_contact_type'] = sys_config('routine_contact_type', 0);
                        }
                    }
                    return $info;
                } else {
                    foreach ($info['value'] as $key => &$item) {
                        if ($key == 'customerService') {
                            foreach ($item as $k => &$v) {
                                $v['routine_contact_type'] = sys_config('routine_contact_type', 0);
                            }
                        }
                    }
                    return $info['value'];
                }
            }
        }
        return [];
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
        /** @var CenterProductServices $CenterProductServices */
        $CenterProductServices = app()->make(CenterProductServices::class);
        $type = $where['type'];
        unset($where['type']);
        $data = [];
        switch ($type) {
            case 0:
                $data = $CenterProductServices->searchList($where);
                break;
            //秒杀
            case 2:
                //通过事件代理查询秒杀DIY列表，避免 app 层直接引用 modules
                $data = $this->proxyActivityData('seckill', 'getDiySeckillList', $where);
                break;
            //拼团
            case 3:
                //通过事件代理查询拼团DIY列表，避免 app 层直接引用 modules
                $data = $this->proxyActivityData('combination', 'getDiyCombinationList', $where);
                break;
            case 4:
                // is_hot 推荐位字段已删除，返回空数组
                $data = [];
                break;
            case 5:
                // is_new 推荐位字段已删除，返回空数组
                $data = [];
                break;
            case 6:
                // is_benefit 推荐位字段已删除，返回空数组
                $data = [];
                break;
            case 7:
                // is_best 推荐位字段已删除，返回空数组
                $data = [];
                break;
            //砍价
            case 8:
                //通过事件代理查询砍价DIY列表，避免 app 层直接引用 modules
                $data = $this->proxyActivityData('bargain', 'getDiyBargainList', $where);
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
        /** @var CenterProductServices $CenterProductServices */
        $CenterProductServices = app()->make(CenterProductServices::class);
        $type = $where['type'];
        $data = [];
        switch ($type) {
            case 0:
                $where['type'] = $where['isType'] ?? 0;
                $data['list'] = $CenterProductServices->getGoodsList($where, $uid);
                break;
            //秒杀
            case 2:
                //通过事件代理查询秒杀首页列表，避免 app 层直接引用 modules
                $data = $this->proxyActivityData('seckill', 'getHomeSeckillList', $where);
                break;
            //拼团
            case 3:
                //通过事件代理查询拼团首页列表，避免 app 层直接引用 modules
                $data = $this->proxyActivityData('combination', 'getHomeList', $where);
                break;
            case 4:
                // is_hot 推荐位字段已删除，返回空数组
                $data['list'] = [];
                break;
            case 5:
                // is_new 推荐位字段已删除，返回空数组
                $data['list'] = [];
                break;
            case 6:
                // is_benefit 推荐位字段已删除，返回空数组
                $data['list'] = [];
                break;
            case 7:
                // is_best 推荐位字段已删除，返回空数组
                $data['list'] = [];
                break;
            //砍价
            case 8:
                //通过事件代理查询砍价首页列表，避免 app 层直接引用 modules
                $data = $this->proxyActivityData('bargain', 'getHomeList', $where);
                break;
        }
        foreach ($data['list'] as &$item) {
            $item['image'] = set_file_url($item['image'], sys_config('site_url'));
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
        $routine_my_menus = $systemGroupDataServices->getGroupDataList(['gid' => $menus_gid], 'all');
        $routine_my_menus = $routine_my_menus['list'] ?? [];
        foreach ($routine_my_menus as &$item) {
            if (!isset($item['is_show']) || $item['is_show'] === '') {
                $item['is_show'] = '1';
            }
        }
        $routine_my_banner = $systemGroupDataServices->getGroupDataList(['gid' => $banner_gid], 'all');
        $routine_my_banner = $routine_my_banner['list'] ?? [];
        foreach ($routine_my_banner as &$bannerItem) {
            $bannerItem['is_show'] = strval($bannerItem['status']);
        }
        $my_banner_status = boolval($info['my_banner_status']);
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
            $info->business_status = $data['business_status'];
            $info->my_menus_status = $data['my_menus_status'];
            $info->update_time = time();
            $res = $info->save();
        } else {
            throw new AdminException('个人中心模板不存在');
        }
        $systemGroupDataServices->saveAllData($data['routine_my_banner'], 'routine_my_banner');
        $systemGroupDataServices->saveAllData($data['routine_my_menus'], 'routine_my_menus');
        return true;
    }

    /**
     * 获取底部导航
     * @param string $template_name
     * @return array|mixed
     */
    public function getNavigation(string $template_name)
    {
        $value = $this->dao->value(['status' => 1], 'value');
        if (!$value) {
            $value = $this->dao->value(['template_name' => 'default'], 'value');
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

    /**
     * 取单个diy小程序预览二维码
     * @param int $id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRoutineCode(int $id)
    {
        $diy = $this->dao->getOne(['id' => $id, 'is_del' => 0]);
        if (!$diy) {
            throw new AdminException('数据不存在');
        }
        /** @var QrcodeServices $QrcodeService */
        $QrcodeService = app()->make(QrcodeServices::class);
        return $QrcodeService->getRoutineQrcodePath($id, 0, 6, [], false);
    }
}
