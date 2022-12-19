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

namespace app\model\other;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * Class Qrcode
 * @package app\model\other
 */
class Qrcode extends BaseModel
{

    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'qrcode';

    /**
     * type 搜索器
     * @param Model $query
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
        if ($value != '') {
            $query->whereLike('type', $value);
        }
    }

    /**
     * status 搜索器
     * @param Model $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value != '') {
            $query->whereLike('status', $value);
        }
    }

    /**
     * third_type 搜索器
     * @param Model $query
     * @param $value
     */
    public function searchThirdTypeAttr($query, $value)
    {
        if ($value != '') {
            $query->whereLike('third_type', $value);
        }
    }

    /**
     * third_id 搜索器
     * @param Model $query
     * @param $value
     */
    public function searchThirdIdAttr($query, $value)
    {
        if ($value != '') {
            $query->whereLike('third_id', $value);
        }
    }

}
