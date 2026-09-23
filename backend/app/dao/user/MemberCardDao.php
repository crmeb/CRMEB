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
use app\model\user\MemberCard;

/**
 * 会员卡DAO类
 * 负责会员卡表的数据访问操作
 */
class MemberCardDao extends BaseDao
{
    /**
     * Batch insert cards without model save semantics. The table uses a
     * composite primary key, so saveAll() may incorrectly treat rows as updates.
     */
    public function insertAll(array $data): int
    {
        return $this->getModel()->insertAll($data);
    }

    /**
     * 设置模型类
     * @return string 返回模型类名
     */
    protected function setModel(): string
    {
        return MemberCard::class;
    }

    /**
     * 获取会员卡列表
     * 根据条件查询会员卡列表，支持分页和字段筛选
     * @param array $where 查询条件
     * @param int $page 页码，0表示不分页
     * @param int $limit 每页数量，0表示不分页
     * @param array $field 查询字段
     * @return \think\Collection 返回会员卡集合
     */
    public function getSearchList(array $where, int $page = 0, int $limit = 0, array $field = ['*'])
    {
        return $this->search($where)->order('use_time desc,id desc')
            ->field($field)
            ->when($page > 0 || $limit > 0, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })
            ->select();
    }

    /**
     * 获取单条会员卡信息
     * 根据条件查询单个会员卡记录
     * @param array $where 查询条件
     * @return array|\think\Model|null 返回会员卡数据或null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOneByWhere(array $where)
    {
        return $this->getModel()->where($where)->find();
    }


}
