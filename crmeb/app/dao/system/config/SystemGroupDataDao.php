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

namespace app\dao\system\config;

use app\dao\BaseDao;
use app\model\system\config\SystemGroupData;

/**
 * 组合数据
 * Class SystemGroupDataDao
 * @package app\dao\system\config
 */
class SystemGroupDataDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return SystemGroupData::class;
    }

    /**
     * 获取组合数据列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGroupDataList(array $where, int $page, int $limit)
    {
        return $this->search($where)->page($page, $limit)->order('sort desc,id DESC')->select()->toArray();
    }

    /**
     * 获取某个gid下的组合数据
     * @param int $gid
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGroupDate(int $gid, int $limit = 0)
    {
        return $this->search(['gid' => $gid, 'status' => 1])->when($limit, function ($query) use ($limit) {
            $query->limit($limit);
        })->field('value,id')->order('sort DESC,id DESC')->select()->toArray();
    }

    /**
     * 根据id获取秒杀数据
     * @param array $ids
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function idByGroupList(array $ids, string $field)
    {
        return $this->getModel()->whereIn('id', $ids)->field($field)->select()->toArray();
    }

    /**
     * 根据gid删除组合数据
     * @param int $gid
     * @return bool
     */
    public function delGroupDate(int $gid)
    {
        return $this->getModel()->where('gid', $gid)->delete();
    }

    /**
     * 批量保存
     * @param array $data
     * @return mixed|\think\Collection
     * @throws \Exception
     */
    public function saveAll(array $data)
    {
        return $this->getModel()->saveAll($data);
    }
}
