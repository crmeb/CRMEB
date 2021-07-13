<?php

namespace AlibabaCloud\Tea\FileForm;

class FileForm
{
    public static function getBoundary()
    {
        return (string) (mt_rand(10000000000000, 99999999999999));
    }

    public static function toFileForm($map, $boundary)
    {
        return new FileFormStream($map, $boundary);
    }
}
