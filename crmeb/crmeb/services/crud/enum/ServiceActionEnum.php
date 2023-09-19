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
 * 逻辑层方法枚举
 * Class ServiceActionEnum
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/8/14
 * @package crmeb\services\crud\enum
 */
class ServiceActionEnum
{
    //搜索
    const INDEX = 'index';
    //获取表单
    const FORM = 'form';
    //保存
    const SAVE = 'save';
    //更新
    const UPDATE = 'update';

    const SERVICE_ACTION_ALL = [
        self::INDEX,
        self::FORM,
        self::SAVE,
        self::UPDATE
    ];
}
