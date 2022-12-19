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

namespace app\dao\system;

use app\dao\BaseDao;
use app\model\system\SystemMenus;

/**
 * 菜单dao层
 * Class SystemMenusDao
 * @package app\dao\system
 */
class SystemMenusDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return SystemMenus::class;
    }

    /**
     * 获取权限菜单列表
     * @param array $where
     * @param array $field
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenusRoule(array $where, ?array $field = [])
    {
        if (!$field) {
            $field = ['id', 'menu_name', 'icon', 'pid', 'sort', 'menu_path', 'is_show', 'header', 'is_header', 'is_show_path'];
        }
        return $this->search($where)->field($field)->order('sort DESC,id DESC')->failException(false)->select();
    }

    /**
     * 获取菜单中的唯一权限
     * @param array $where
     * @return array
     */
    public function getMenusUnique(array $where)
    {
        return $this->search($where)->where('unique_auth', '<>', '')->column('unique_auth', '');
    }

    /**
     * 根据访问地址获得菜单名
     * @param string $rule
     * @return mixed
     */
    public function getVisitName(string $rule)
    {
        return $this->search(['url' => $rule])->value('menu_name');
    }

    /**
     * 获取后台菜单列表并分页
     * @param array $where
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenusList(array $where)
    {
        $where = array_merge($where, ['is_del' => 0]);
        return $this->search($where)->order('sort DESC,id ASC')->select();
    }

    /**
     * 菜单总数
     * @param array $where
     * @return int
     */
    public function countMenus(array $where)
    {
        $where = array_merge($where, ['is_del' => 0]);
        return $this->count($where);
    }

    /**
     * 指定条件获取某些菜单的名称以数组形式返回
     * @param array $where
     * @param string $field
     * @param string $key
     * @return array
     */
    public function column(array $where, string $field, string $key)
    {
        return $this->search($where)->column($field, $key);
    }

    /**菜单列表
     * @param array $where
     * @param int $type
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function menusSelect(array $where, $type = 1)
    {
        if ($type == 1) {
            return $this->search($where)->field('id,pid,menu_name,menu_path,unique_auth,sort')->order('sort DESC')->select();
        } else {
            return $this->search($where)->group('pid')->column('pid');
        }
    }

    /**
     * 搜索列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSearchList()
    {
        return $this->search(['is_show' => 1, 'auth_type' => 1, 'is_del' => 0, 'is_show_path' => 0])
            ->field('id,pid,menu_name,menu_path,unique_auth,sort')->order('sort DESC')->select();
    }
}
