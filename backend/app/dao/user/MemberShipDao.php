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
use app\model\user\MemberShip;

/**
 * 会员类型DAO类
 * 负责会员类型表的数据访问操作
 */
class MemberShipDao extends BaseDao
{
    /**
     * 设置模型类
     * @return string 返回模型类名
     */
    protected function setModel(): string
    {
        return MemberShip::class;
    }

    /**
     * 获取会员类型列表（后台管理）
     * 根据条件查询会员类型列表，支持分页和字段筛选
     * @param array $where 查询条件
     * @param int $page 页码，0表示不分页
     * @param int $limit 每页数量，0表示不分页
     * @param array $field 查询字段
     * @return \think\Collection 返回会员类型集合
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSearchList(array $where, int $page = 0, int $limit = 0, array $field = ['*'])
    {
        return $this->search($where)->order('sort desc,id desc')
            ->field($field)
            ->page($page, $limit)
            ->select();

    }

    /**
     * 获取会员类型列表（API接口）
     * 查询所有会员类型数据，用于前端API调用
     * @param array $where 查询条件
     * @return mixed 返回会员类型数组
     */
    public function getApiList(array $where)
    {
        return $this->search()->where($where)->order('sort desc,id DESC')->select()->toArray();
    }


}