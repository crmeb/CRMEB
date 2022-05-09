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

namespace crmeb\utils;

/**
 * AdminApi错误码统一存放类
 * Class AdminApiErrorCode
 * @package crmeb\utils
 */
class AdminApiErrorCode
{
    // 商品相关

    /** 请选择商品分类 Please select product category 411001 */
    const ERR_PLEASE_SELECT_PRODUCT_CATEGORY = [411001, 'product.Please select product category'];

    /** 请输入商品名称 Please enter the product name* code:411002 */
    const ERR_PLEASE_ENTER_THE_PRODUCT_NAME = 'product.Please enter the product name';

    /** 请上传商品轮播图 Please upload the slide image code:411003 */
    const ERR_PLEASE_UPLOAD_SLIDER_IMAGE = 'product.Please upload the slide image';

    public static $code = [
        'product.Please enter the product name' => 411002,
        'product.Please upload the slide image' => 411003,
    ];

    public static function getCode($msg = ''): int
    {
        if (isset(self::$code[$msg])) {
            return self::$code[$msg];
        }
        return 0;
    }
}
