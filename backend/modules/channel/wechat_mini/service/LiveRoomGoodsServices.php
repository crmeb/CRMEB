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

namespace modules\channel\wechat_mini\service;


use modules\channel\wechat_mini\dao\LiveRoomGoodsDao;
use crmeb\basic\BaseService;

/**
 * Class LiveRoomGoodsServices
 * @package app\services\activity\live
 */
class LiveRoomGoodsServices extends BaseService
{
    /**
     * @var LiveRoomGoodsDao
     */
    protected $dao;

    public function __construct(LiveRoomGoodsDao $dao)
    {
        $this->dao = $dao;
    }
}
