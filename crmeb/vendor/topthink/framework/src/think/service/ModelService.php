<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\service;

use think\Model;
use think\Service;

/**
 * 模型服务类
 */
class ModelService extends Service
{
    public function boot()
    {
        Model::maker(function (Model $model) {
            $db     = $this->app->db;
            $config = $this->app->config;
            $model->setDb($db);

            $isAutoWriteTimestamp = $model->getAutoWriteTimestamp();

            if (is_null($isAutoWriteTimestamp)) {
                // 自动写入时间戳
                $model->isAutoWriteTimestamp($config->get('database.auto_timestamp', 'timestamp'));
            }

            $dateFormat = $model->getDateFormat();

            if (is_null($dateFormat)) {
                // 设置时间戳格式
                $model->setDateFormat($config->get('database.datetime_format', 'Y-m-d H:i:s'));
            }

        });
    }
}
