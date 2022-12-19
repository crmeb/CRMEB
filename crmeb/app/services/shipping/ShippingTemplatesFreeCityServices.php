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

namespace app\services\shipping;


use app\dao\shipping\ShippingTemplatesFreeCityDao;
use app\services\BaseServices;

/**
 * 包邮和城市数据连表业务处理层
 * Class ShippingTemplatesFreeCityServices
 * @package app\services\shipping
 * @method getUniqidList(array $where, bool $group) 获取指定条件下的包邮列表
 */
class ShippingTemplatesFreeCityServices extends BaseServices
{
    /**
     * 构造方法
     * ShippingTemplatesFreeCityServices constructor.
     * @param ShippingTemplatesFreeCityDao $dao
     */
    public function __construct(ShippingTemplatesFreeCityDao $dao)
    {
        $this->dao = $dao;
    }
}
