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

namespace app\services\wechat;


use app\dao\wechat\WechatMediaDao;
use app\services\BaseServices;

/**
 * Class WechatMediaServices
 * @package app\services\wechat
 * @method save(array $data) 保存数据
 */
class WechatMediaServices extends BaseServices
{
    /**
     * WechatMediaServices constructor.
     * @param WechatMediaDao $dao
     */
    public function __construct(WechatMediaDao $dao)
    {
        $this->dao = $dao;
    }

}
