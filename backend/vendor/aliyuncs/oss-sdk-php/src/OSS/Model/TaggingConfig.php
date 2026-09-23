<?php

namespace OSS\Model;


use OSS\Core\OssException;

/**
 * Class TaggingConfig
 * @package OSS\Model
 *
 */
class TaggingConfig implements XmlConfig
{
    /**
     * TaggingConfig constructor.
     */
    public function __construct()
    {
        $this->tags = array();
    }

    /**
     * Get Tag list
     *
     * @return Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }


    /**
     * Add a new Tag
     *
     * @param Tag $tag
     * @throws OssException
     */
    public function addTag($tag)
    {
        $this->tags[] = $tag;
    }

    /**
     * Parse TaggingConfig from the xml.
     *
     * @param string $strXml
     * @throws OssException
     * @return null
     */
    public function parseFromXml($strXml)
    {
        $xml = simplexml_load_string($strXml);
        if (!isset($xml->TagSet) || !isset($xml->TagSet->Tag)) return;
        foreach ($xml->TagSet->Tag as $tag) {
            $this->addTag(new Tag($tag->Key, $tag->Value));
        }
    }

    /**
     * Serialize the object into xml string.
     *
     * @return string
     */
    public function serializeToXml()
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Tagging></Tagging>');
        $xmlTagSet = $xml->addChild('TagSet');
        foreach ($this->tags as $tag) {
            $xmlTag = $xmlTagSet->addChild('Tag');
            $xmlTag->addChild('Key', strval($tag->getKey()));
            $xmlTag->addChild('Value', strval($tag->getValue()));
        }
        return $xml->asXML();
    }

    public function __toString()
    {
        return $this->serializeToXml();
    }

    /**
     * Tag list
     *
     * @var Tag[]
     */
    private $tags = array();
}