<?php

namespace Volc\Service\Vod\Upload;

class WorkflowInput
{
    public $TemplateId;

    /**
     * WorkflowInput constructor.
     * @param $TemplateId
     */
    public function __construct($TemplateId)
    {
        $this->TemplateId = $TemplateId;
    }

}