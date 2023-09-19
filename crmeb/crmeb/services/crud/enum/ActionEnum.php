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
 * 访问方法枚举
 * Class ActionEnum
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/8/12
 * @package crmeb\services\crud\enum
 */
class ActionEnum
{
    //列表
    const INDEX = 'index';
    //获取创建数据
    const CREATE = 'create';
    //保存
    const SAVE = 'save';
    //获取编辑数据
    const EDIT = 'edit';
    //修改
    const UPDATE = 'update';
    //状态
    const STATUS = 'status';
    //删除
    const DELETE = 'delete';
    //查看
    const READ = 'read';
    //所有方法名称
    const ACTION_ALL = [
        self::INDEX,
        self::CREATE,
        self::SAVE,
        self::EDIT,
        self::UPDATE,
        self::STATUS,
        self::DELETE,
        self::READ
    ];
}
