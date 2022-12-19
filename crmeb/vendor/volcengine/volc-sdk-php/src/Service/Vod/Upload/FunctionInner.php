<?php

namespace Volc\Service\Vod\Upload;

class FunctionInner
{
    public $Name = "";
    public $Input;

    /**
     * FunctionInner constructor.
     * @param string $Name
     * @param $Input
     */
    public function __construct(string $Name, $Input)
    {
        $this->Name = $Name;
        $this->Input = $Input;
    }


    public function __toString()
    {
        return json_encode($this);
    }


}