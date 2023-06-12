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

namespace app\model\system;


use crmeb\basic\BaseModel;

/**
 * Class SystemCrud
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/4/6
 * @package app\model\system
 */
class SystemCrud extends BaseModel
{

    /**
     * @var string
     */
    protected $name = 'system_crud';

    /**
     * @var string
     */
    protected $pk = 'id';

    public function getAddTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    public function getFieldAttr($value)
    {
        return json_decode($value, true);
    }

    public function getMenuIdsAttr($value)
    {
        return json_decode($value, true);
    }

    public function getMakePathAttr($value)
    {
        return json_decode($value, true);
    }

    public function getRouteIdsAttr($value)
    {
        return json_decode($value, true);
    }
}
