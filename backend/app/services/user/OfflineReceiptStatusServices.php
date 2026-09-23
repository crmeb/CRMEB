<?php
declare(strict_types=1);

namespace app\services\user;

use app\dao\user\OfflineReceiptStatusDao;
use crmeb\basic\BaseService;

/**
 * 线下收银状态服务类
 * 负责 eb_offline_receipt_status 表的状态记录业务逻辑
 */
class OfflineReceiptStatusServices extends BaseService
{
    protected $dao;

    public function __construct(OfflineReceiptStatusDao $dao)
    {
        $this->dao = $dao;
    }
}
