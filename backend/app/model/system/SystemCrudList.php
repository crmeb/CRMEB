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
namespace app\model\system;

use crmeb\basic\BaseModel;

/**
 * @author wuhaotian
 * @email 442384644@qq.com
 * @date 2024/5/20
 */
class SystemCrudList extends BaseModel
{
    /**
     * @var string
     */
    protected $name = 'system_crud_list';

    /**
     * @var string
     */
    protected $pk = 'id';

    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('status', $value);
        }
    }
}