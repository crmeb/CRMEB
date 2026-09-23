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

namespace app\services\product;

use app\dao\product\ProductRuleDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;

/**
 * 商品规格模板服务
 * 替代已弃用的 StoreProductRuleServices，操作 eb_store_product_rule 表
 */
class ProductRuleServices extends BaseService
{
    /**
     * @var ProductRuleDao
     */
    protected $dao;

    /**
     * ProductRuleServices constructor.
     * @param ProductRuleDao $dao
     */
    public function __construct(ProductRuleDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取商品规格模板列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where = [])
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);
        foreach ($list as &$item) {
            $attr_name = $attr_value = [];
            if ($item['rule_value']) {
                $specs = json_decode($item['rule_value'], true);
                if ($specs) {
                    foreach ($specs as $key => $value) {
                        $attr_name[] = $value['value'] ?? '';
                        $attr_value[] = implode(',', $value['detail'] ?? '');
                    }
                } else {
                    $attr_name[] = '';
                    $attr_value[] = '';
                }
                $item['attr_name'] = implode(',', $attr_name);
                $item['attr_value'] = $attr_value;
            }
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 保存规格模板
     * @param int $id
     * @param array $data
     */
    public function save(int $id, array $data)
    {
        $data['rule_value'] = json_encode($data['spec']);
        unset($data['spec']);
        if ($id) {
            $res = $this->dao->update($id, $data);
        } else {
            $res = $this->dao->save($data);
        }
        if (!$res) throw new AdminException('保存失败');
    }

    /**
     * 获取一条规格模板数据
     * @param int $id
     * @return array
     */
    public function getInfo(int $id)
    {
        $info = $this->dao->get($id);
        $info['spec'] = json_decode($info['rule_value'], true);
        return compact('info');
    }

    /**
     * 删除规格模板
     * @param string $ids
     */
    public function del(string $ids)
    {
        if (empty($ids)) throw new AdminException('参数错误');
        $this->dao->del($ids);
    }
}
