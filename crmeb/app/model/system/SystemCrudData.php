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
 * Class SystemCrudData
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/7/28
 * @package app\model\system
 */
class SystemCrudData extends BaseModel
{
    /**
     * @var string
     */
    protected $name = 'system_crud_data';

    /**
     * @var string
     */
    protected $pk = 'id';

    public function getValueAttr($value)
    {
        return json_decode($value, true);
    }

    /**
     * @param $query
     * @param $value
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/10
     */
    public function searchNameAttr($query, $value)
    {
        if ($value != '') {
            $query->where('name', 'like', '%' . $value . '%');
        }
    }
}
