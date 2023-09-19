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

namespace crmeb\services\crud\enum;

/**
 * 搜索方式枚举
 * Class SearchEnum
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/8/14
 * @package crmeb\services\crud\enum
 */
class SearchEnum
{
    //等于
    const SEARCH_TYPE_EQ = '=';
    //小于等于
    const SEARCH_TYPE_LTEQ = '<=';
    //大于等于
    const SEARCH_TYPE_GTEQ = '>=';
    //不等于
    const SEARCH_TYPE_NEQ = '<>';
    //模糊搜索
    const SEARCH_TYPE_LIKE = 'LIKE';
    //区间-用来时间区间搜索
    const SEARCH_TYPE_BETWEEN = 'BETWEEN';

    //搜索类型
    const SEARCH_TYPE = [
        self::SEARCH_TYPE_EQ,
        self::SEARCH_TYPE_LTEQ,
        self::SEARCH_TYPE_GTEQ,
        self::SEARCH_TYPE_NEQ,
        self::SEARCH_TYPE_LIKE,
        self::SEARCH_TYPE_BETWEEN,
    ];
}
