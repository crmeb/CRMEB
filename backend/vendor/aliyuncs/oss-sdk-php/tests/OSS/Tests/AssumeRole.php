<?php
namespace OSS\Tests;


class AssumeRole extends StsBase
{
    private $Action = "AssumeRole";

    private $RoleArn;

    private $RoleSessionName;

    private  $Policy;

    private  $DurationSeconds = "3600";

    public function getAttributes()
    {
        return get_object_vars($this);
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __construct()
    {
        parent::__construct();
        $this->RoleSessionName = "sts";
    }
}
