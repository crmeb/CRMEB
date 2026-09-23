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

namespace app\dao\user;


use crmeb\basic\BaseDao;
use app\model\user\MemberCardBatch;

/**
 * 会员卡批次DAO类
 * 负责会员卡批次表的数据访问操作
 */
class MemberCardBatchDao extends BaseDao
{
    /**
     * 设置模型类
     * @return string 返回模型类名
     */
    protected function setModel(): string
    {
        return MemberCardBatch::class;
    }

    /**
     * 获取会员卡批次列表
     * 根据条件查询会员卡批次列表，支持分页和排序
     * @param array $where 查询条件
     * @param int $page 页码，0表示不分页
     * @param int $limit 每页数量，0表示不分页
     * @param string $order 排序字段
     * @return array 返回批次列表数组
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, int $page = 0, int $limit = 0, string $order = '')
    {
        return $this->search($where)
            ->order(($order ? $order . ' ,' : '') . 'sort desc,id desc')
            ->page($page, $limit)->select()->toArray();
    }


}