<?php

namespace Volc\Service\Vod\Upload;

class Functions
{
    private $funcs = [];

//[{"Name": "GetMeta"},{"Name":"Snapshot","Input":{"SnapshotTime": 2.0}}]
    public function addGetMetaFunc()
    {
        $this->funcs[] = new FunctionInner("GetMeta", "");
    }

    public function addSnapshotTimeFunc(float $snapshotTime)
    {
        $this->funcs[] = new FunctionInner("Snapshot", new SnapshotTimeInput($snapshotTime));
    }

    public function addStartWorkflowFunc(string $templateId)
    {
        $this->funcs[] = new FunctionInner("StartWorkflow", new WorkflowInput($templateId));
    }

    public function addOptionInfoFunc(OptionInfo $optionInfo)
    {
        $this->funcs[] = new FunctionInner("AddOptionInfo", $optionInfo);
    }

    public function getFunctionsString(): string
    {
        return json_encode($this->funcs);
    }

}

