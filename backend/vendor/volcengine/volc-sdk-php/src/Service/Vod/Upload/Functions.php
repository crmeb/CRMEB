<?php

namespace Volc\Service\Vod\Upload;

use Volc\Service\Vod\Models\Business\VodUploadTemplate;

class Functions
{
    private $funcs = [];

//[{"Name": "GetMeta"},{"Name":"Snapshot","Input":{"SnapshotTime": 2.0}}]
    public function addGetMetaFunc()
    {
        $this->funcs[] = new FunctionInner("GetMeta", null);
    }

    public function addSnapshotTimeFunc(float $snapshotTime)
    {
        $this->funcs[] = new FunctionInner("Snapshot", new SnapshotTimeInput($snapshotTime));
    }
    //deprecated
    public function addStartWorkflowFunc(string $templateId)
    {
        $this->funcs[] = new FunctionInner("StartWorkflow", new WorkflowInput($templateId));
    }

    public function addStartWorkflowTemplateFunc($templates)
    {
        $this->funcs[] = new FunctionInner("StartWorkflow", new WorkflowInput("", $templates));
    }

    public function addOptionInfoFunc(OptionInfo $optionInfo)
    {
        $this->funcs[] = new FunctionInner("AddOptionInfo", $optionInfo);
    }

    public function addCaptionUploadFunc(CaptionInput $captionInput)
    {
        $this->funcs[] = new FunctionInner("CaptionUpload", $captionInput);
    }

    public function getFunctionsString(): string
    {
        return json_encode($this->funcs);
    }

}

