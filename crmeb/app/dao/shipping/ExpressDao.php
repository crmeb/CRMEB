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

namespace app\dao\shipping;

use app\dao\BaseDao;
use app\model\other\Express;

/**
 * 物流信息
 * Class ExpressDao
 * @package app\dao\other
 */
class ExpressDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return Express::class;
    }

    /**
     * 获取物流列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getExpressList(array $where, string $field, int $page, int $limit)
    {
        return $this->search($where)->field($field)->order('sort DESC,id DESC')
            ->when($page > 0 && $limit > 0, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->select()->toArray();
    }

    /**
     * 指定的条件获取物流信息以数组返回
     * @param array $where
     * @param string $field
     * @param string $key
     * @return array
     */
    public function getExpress(array $where, string $field, string $key)
    {
        return $this->search($where)->order('id DESC')->column($field, $key);
    }

    /**
     * 通过code获取一条信息
     * @param string $code
     * @param string $field
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getExpressByCode(string $code, string $field = '*')
    {
        return $this->getModel()->field($field)->where('code', $code)->find();
    }
}
