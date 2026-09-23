<?php

namespace DrSlump\Protobuf\Codec;

use DrSlump\Protobuf;

/**
 * This codec serializes and unserializes from/to Xml documents
 * where the elements represent the field's name.
 *
 * It makes use of the PhpArray codec to do the heavy work to just
 * take care of converting the array to/from XML.
 */
class Xml extends PhpArray
    implements Protobuf\CodecInterface
{
    /** @var bool */
    protected $dom = false;
    /** @var string */
    protected $root;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        foreach ($options as $option=>$value) {
            $this->setOption($option, $value);
        }
    }

    /**
     * @throws \InvalidArgumentException
     * @param string $option
     * @param mixed $value
     * @return void
     */
    public function setOption($option, $value)
    {
        switch (strtolower($option)) {
        case 'root':
            $this->root = $value;
            break;
        case 'dom':
            $this->dom = (bool)$value;
            break;
        default:
            throw new \InvalidArgumentException('Unknown option ' . $option);
        }
    }

    /**
     * @param \DrSlump\Protobuf\Message $message
     * @return string | \SimpleXMLElement
     */
    public function encode(Protobuf\Message $message)
    {
        // Generate an associative array
        $data = $this->encodeMessage($message);

        // Build an XML representation
        $root = $this->root ?: str_replace('\\', '_', get_class($message));
        $root = new \SimpleXMLElement('<?xml version="1.0"?><' . $root . '></' . $root . '>');
        $this->arrayToXml($root, $data);

        return $this->dom ? $root : $root->asXML();
    }

    /**
     * @param \SimpleXMLElement $elem
     * @param array $data
     * @param null $label
     */
    protected function arrayToXml(\SimpleXMLElement $elem, array $data, $label = null)
    {
        foreach ($data as $k=>$v) {
            if (is_array($v)) {
                // Detect nested messages
                if (!is_int(key($v))) {
                    $child = $elem->addChild($label ?: $k);
                    $this->arrayToXml($child, $v);
                // Lists are forced a fixed label
                } else {
                    $this->arrayToXml($elem, $v, $k);
                }
            } else {
                $elem->addChild($label ?: $k, $v);
            }
        }
    }

    /**
     * @param \DrSlump\Protobuf\Message $message
     * @param string | \SimpleXMLElement $xml
     * @return \DrSlump\Protobuf\Message
     */
    public function decode(Protobuf\Message $message, $xml)
    {
        if (is_string($xml)) {
            $xml = new \SimpleXMLElement($xml);
        }

        // Build an associative array from the XML
        $data = $this->xmlToArray($xml);
        // Generate a message from the data
        return $this->decodeMessage($message, $data);
    }

    /**
     * @param \SimpleXMLElement $elem
     * @return array
     */
    protected function xmlToArray(\SimpleXMLElement $elem)
    {
        $data = array();
        foreach ($elem->children() as $child) {
            if (count($child->children())) {
                $value = $this->xmlToArray($child);
            } else {
                $value = (string)$child;
            }

            $name = $child->getName();
            if (isset($data[$name])) {
                // If not yet a "list" array
                if (!is_array($data[$name]) || !is_int(key($data[$name]))) {
                    $data[$name] = array($data[$name]);
                }
                $data[$name][] = $value;
            } else {
                $data[$name] = $value;
            }
        }

        return $data;
    }

}
