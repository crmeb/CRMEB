<?php

namespace Volc\Service\Vod\Upload;

class CaptionInput
{
    public $Title = "";
    public $Format = "";
    public $Vid = "";
    public $Fid = "";
    public $Language = "";
    public $Source = "";
    public $Tag = "";
    public $ActionType = "";
    public $StoreUri = "";
    public $AutoPublish = false;

    /**
     * WorkflowInput constructor.
     * @param string Title
     * @param string $Format
     */
    public function __construct(string $Title, string $Format, string $Vid, string $Fid, string $Language, string $Source,
                                string $Tag, string $ActionType, string $StoreUri, bool $AutoPublish)
    {
        $this->Title = $Title;
        $this->Format = $Format;
        $this->Vid = $Vid;
        $this->Fid = $Fid;
        $this->Language = $Language;
        $this->Source = $Source;
        $this->Tag = $Tag;
        $this->AutoPublish = $AutoPublish;
        $this->ActionType = $ActionType;
        $this->StoreUri = $StoreUri;
    }

}