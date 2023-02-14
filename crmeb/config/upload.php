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

return [
    //默认上传模式,后台配置优先,添加类型一定索引要和驱动名一致 用小些字母
    'default' => 'local',
    //上传文件大小
    'filesize' => 2097152,
    //上传文件后缀类型
    'fileExt' => ['jpg', 'jpeg', 'png', 'gif', 'pem', 'mp3', 'wma', 'wav', 'amr', 'mp4', 'key', 'xlsx', 'xls', 'txt', 'ico'],
    //上传文件类型
    'fileMime' => [
        'image/jpeg',
        'image/gif',
        'image/png',
        'text/plain',
        'audio/mpeg',
        'video/mp4',
        'application/octet-stream',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-works',
        'application/vnd.ms-excel',
        'application/zip',
        'application/vnd.ms-excel',
        'application/vnd.ms-excel',
        'text/xml',
        'image/x-icon',
        'image/vnd.microsoft.icon',
        'application/x-x509-ca-cert',
    ],
    //驱动模式，此配置优先与后台配置，后台添加配置请加前缀，例如添加七牛云配置：accessKey 后台添加变量名 qiniu_accessKey
    'stores' => [
        //本地上传配置
        'local' => [],
        //七牛云上传配置
        'qiniu' => [
            'AccessKeyId' => '', // sys_config('qiniu_accessKey')
            'AccessKeySecret' => '', // sys_config('qiniu_secretKey')
        ],
        //oss 阿里云上传配置
        'oss' => [
            'AccessKeyId' => '', // sys_config('accessKey')
            'AccessKeySecret' => '', // sys_config('secretKey')
        ],
        //cos 腾讯云上传配置
        'cos' => [
            'AccessKeyId' => '', //sys_config('tengxun_accessKey')
            'AccessKeySecret' => '', //sys_config('tengxun_secretKey')
            'APPID' => '', //sys_config('tengxun_appid')
        ],
    ]
];
