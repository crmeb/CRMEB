<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\model\other;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 辅助表
 * Class Auxiliary
 * @package app\model\other
 */
class Auxiliary extends BaseModel
{

    use ModelTrait;

    /**
     * 表明
     * @var string
     */
    protected $name = 'auxiliary';

    /**
     * 主键
     * @var string
     */
    protected $pk = 'id';


}
