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
use think\model\concern\SoftDelete;

/**
 * Class SystemRoute
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/4/6
 * @package app\model\system
 */
class SystemRoute extends BaseModel
{

    use SoftDelete;

    /**
     * @var string
     */
    protected $name = 'system_route';

    /**
     * @var string
     */
    protected $key = 'id';

    public function searchNameLikeAttr($query, $value)
    {
        if ('' !== $value) {
            $query->where('name|path', 'LIKE', '%' . $value . '%');
        }
    }

    public function setQueryAttr($value)
    {
        return json_encode($value);
    }

    public function getQueryAttr($value)
    {
        return json_decode($value, true);
    }

    public function setHeaderAttr($value)
    {
        return json_encode($value);
    }

    public function getHeaderAttr($value)
    {
        return json_decode($value, true);
    }

    public function setRequestAttr($value)
    {
        return json_encode($value);
    }

    public function getRequestAttr($value)
    {
        return json_decode($value, true);
    }

    public function setResponseAttr($value)
    {
        return json_encode($value);
    }

    public function getResponseAttr($value)
    {
        return json_decode($value, true);
    }

    public function setRequestExampleAttr($value)
    {
        return json_encode($value);
    }

    public function getRequestExampleAttr($value)
    {
        return json_decode($value, true);
    }

    public function setErrorCodeAttr($value)
    {
        return json_encode($value);
    }

    public function getErrorCodeAttr($value)
    {
        return json_decode($value, true);
    }


    public function setResponseExampleAttr($value)
    {
        return json_encode($value);
    }

    public function getResponseExampleAttr($value)
    {
        return json_decode($value, true);
    }
}
