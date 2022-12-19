<?php

namespace Volc\Service\Vod\Upload;

class SnapshotTimeInput
{

    public $SnapshotTime;

    /**
     * SnapshotTimeInput constructor.
     * @param $SnapshotTime
     */
    public function __construct($SnapshotTime)
    {
        $this->SnapshotTime = $SnapshotTime;
    }
}