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

namespace app\dao\other;


use app\dao\BaseDao;
use app\model\other\Auxiliary;

/**
 * 辅助表
 * Class AuxiliaryDao
 * @package app\dao\other
 */
class AuxiliaryDao extends BaseDao
{

    /**
     * @return string
     */
    protected function setModel(): string
    {
        return Auxiliary::class;
    }

}
