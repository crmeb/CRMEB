<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------
 */

namespace app\dao\system;


use app\dao\BaseDao;
use app\model\system\SystemRoute;

/**
 * Class SystemRouteDao
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/4/6
 * @package app\dao\system
 */
class SystemRouteDao extends BaseDao
{

    /**
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/6
     */
    protected function setModel(): string
    {
        return SystemRoute::class;
    }

    /**
     * @param array $ids
     * @return bool
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/23
     */
    public function deleteRoutes(array $ids)
    {
        return $this->getModel()::destroy(function ($q) use ($ids) {
            $q->whereIn('id', $ids);
        }, true);
    }
}
