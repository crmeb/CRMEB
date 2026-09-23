<?php
declare(strict_types=1);

namespace app\services\user;

use app\dao\user\MemberOrderStatusDao;
use crmeb\basic\BaseService;

/**
 * 会员订单状态服务类
 * 负责会员订单状态变更记录的保存与查询
 */
class MemberOrderStatusServices extends BaseService
{
    /**
     * @var MemberOrderStatusDao
     */
    protected $dao;

    /**
     * 初始化DAO层实例
     * MemberOrderStatusServices constructor.
     * @param MemberOrderStatusDao $dao
     */
    public function __construct(MemberOrderStatusDao $dao)
    {
        $this->dao = $dao;
    }
}
