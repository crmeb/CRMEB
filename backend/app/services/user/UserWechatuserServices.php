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

namespace app\services\user;

use crmeb\basic\BaseService;
use app\dao\user\UserWechatUserDao;

/**
 *
 * Class UserWechatuserServices
 * @package app\services\user
 */
class UserWechatuserServices extends BaseService
{
    /**
     * @var UserWechatUserDao
     */
    protected $dao;

    /**
     * UserWechatuserServices constructor.
     * @param UserWechatUserDao $dao
     */
    public function __construct(UserWechatUserDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 自定义简单查询总数
     * @param array $where
     * @return int
     */
    public function getCount(array $where): int
    {
        return $this->dao->getCount($where);
    }

    /**
     * 判断当前是否可关联微信用户表
     * wechat_mp 模块未安装/未启用时返回 false，调用方需降级查询字段
     * @return bool
     */
    public function canJoinWechatUser(): bool
    {
        return $this->dao->canJoinWechatUser();
    }

    /**
     * 复杂条件搜索列表
     * @param array $where
     * @param string $field
     * @return array
     */
    public function getWhereUserList(array $where, string $field): array
    {
        [$page, $limit] = $this->getPageValue();
        $order_string = '';
        $order_arr = ['ascending', 'descending'];
        if (isset($where['now_money']) && in_array($where['now_money'], $order_arr)) {
            $order_string = $where['now_money'] == 'ascending' ? 'now_money asc' : 'now_money desc';
        }
        $list = $this->dao->getListByModel($where, $field, $order_string, $page, $limit);
        $count = $this->dao->getCountByWhere($where);
        return [$list, $count];
    }
}
