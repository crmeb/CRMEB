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

namespace app\dao\system\config;


use app\dao\BaseDao;
use app\model\system\config\SystemConfigTab;

/**
 * 配置分类
 * Class SystemConfigTabDao
 * @package app\dao\system\config
 */
class SystemConfigTabDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return SystemConfigTab::class;
    }

    /**
     * 获取配置分类
     * @param array $where
     * @param array $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getConfigTabAll(array $searchWhere, array $field = ['*'], array $where = [])
    {
        return $this->search($searchWhere)->when(count($where), function ($query) use ($where) {
            $query->where($where);
        })->field($field)->order('sort desc,id asc')->select()->toArray();
    }

    /**
     * 配置分类列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getConfgTabList(array $where, int $page, int $limit)
    {
        return $this->search($where)->order('sort desc,id asc')->page($page, $limit)->select();
    }

}
