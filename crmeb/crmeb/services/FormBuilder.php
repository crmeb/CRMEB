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

namespace crmeb\services;

use FormBuilder\Factory\Iview as Form;

/**
 * Form Builder
 * Class FormBuilder
 * @package crmeb\services
 */
class FormBuilder extends Form
{

    public static function setOptions($call){
        if (is_array($call)) {
            return $call;
        }else{
            return  $call();
        }

    }


}
