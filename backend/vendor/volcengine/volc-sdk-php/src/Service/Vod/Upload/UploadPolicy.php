<?php

namespace Volc\Service\Vod\Upload;

class UploadPolicy
{
    // 上传文件Content-Type黑名单
    public $ContentTypeBlackList;
    // 上传文件Content-Type白名单，和黑名单不共存
    public $ContentTypeWhiteList;
    // 上传文件大小上限
    public $FileSizeUpLimit;
    // 上传文件大小下限
    public $FileSizeBottomLimit;

}