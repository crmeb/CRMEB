<?php
/**
 * Created by CRMEB.
 * Copyright (c) 2017~2019 http://www.crmeb.com All rights reserved.
 * Author: liaofei <136327134@qq.com>
 * Date: 2019/4/3 16:36
 */

namespace crmeb\services;

use crmeb\traits\LogicTrait;

/** 模版消息类
 * Class Template
 * @package crmeb\services
 */
class Template
{
    use LogicTrait;

    protected  $providers=[
        'routine_two'=>ProgramTemplateService::class,
    ];

}