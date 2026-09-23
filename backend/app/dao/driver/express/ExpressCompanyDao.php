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

namespace app\dao\driver\express;

use crmeb\basic\BaseDao;
use app\model\driver\express\ExpressCompany;

/**
 * 电子面单物流公司管理 DAO
 * Class ExpressCompanyDao
 * @package app\dao\driver\express
 */
class ExpressCompanyDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return ExpressCompany::class;
    }

    /**
     * 获取列表
     * @param array $where
     * @param array $field
     * @param int $page
     * @param int $limit
     * @param string $order
     * @return array
     */
    public function getList(array $where = [], array $field = ['*'], int $page = 0, int $limit = 0, string $order = 'sort desc, id asc')
    {
        return $this->search($where)->field($field)
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })
            ->order($order)
            ->select()
            ->toArray();
    }

    /**
     * 根据 code 获取公司信息
     * @param string $code
     * @return array|\think\Model|null
     */
    public function getByCode(string $code)
    {
        return $this->search(['code' => $code])->find();
    }

    /**
     * 获取启用的物流公司列表
     * @return array
     */
    public function getEnabledCompanies()
    {
        return $this->search(['status' => 1])->order('sort desc, id asc')->select()->toArray();
    }
}
