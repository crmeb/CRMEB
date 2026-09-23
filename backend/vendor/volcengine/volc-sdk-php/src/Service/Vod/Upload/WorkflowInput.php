<?php

namespace Volc\Service\Vod\Upload;

class WorkflowInput
{
    public $TemplateId;

    public $Templates;

    /**
     * WorkflowInput constructor.
     * @param $TemplateId
     * @param $Templates
     */
    public function __construct($TemplateId, $Templates = [])
    {
        $this->TemplateId = $TemplateId;
        $this->Templates = $Templates;
    }

}