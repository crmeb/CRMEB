<?php

namespace AlibabaCloud\Tea\XML;

use XmlWriter;

/**
 * Based on: http://stackoverflow.com/questions/99350/passing-php-associative-arrays-to-and-from-xml.
 */
class ArrayToXml
{
    private $version;
    private $encoding;

    /**
     * Construct ArrayToXML object with selected version and encoding
     * for available values check XmlWriter docs http://www.php.net/manual/en/function.xmlwriter-start-document.php.
     *
     * @param string $xmlVersion  XML Version, default 1.0
     * @param string $xmlEncoding XML Encoding, default UTF-8
     */
    public function __construct($xmlVersion = '1.0', $xmlEncoding = 'utf-8')
    {
        $this->version  = $xmlVersion;
        $this->encoding = $xmlEncoding;
    }

    /**
     * Build an XML Data Set.
     *
     * @param array  $data         Associative Array containing values to be parsed into an XML Data Set(s)
     * @param string $startElement Root Opening Tag, default data
     *
     * @return string XML String containing values
     * @return mixed  Boolean false on failure, string XML result on success
     */
    public function buildXML($data, $startElement = 'data')
    {
        if (!\is_array($data)) {
            $err = 'Invalid variable type supplied, expected array not found on line ' . __LINE__ . ' in Class: ' . __CLASS__ . ' Method: ' . __METHOD__;
            trigger_error($err);

            return false; //return false error occurred
        }
        $xml = new XmlWriter();
        $xml->openMemory();
        $xml->startDocument($this->version, $this->encoding);
        $xml->startElement($startElement);
        $data = $this->writeAttr($xml, $data);
        $this->writeEl($xml, $data);
        $xml->endElement(); //write end element
        //returns the XML results
        return $xml->outputMemory(true);
    }

    /**
     * Write keys in $data prefixed with @ as XML attributes, if $data is an array.
     * When an @ prefixed key is found, a '%' key is expected to indicate the element itself,
     * and '#' prefixed key indicates CDATA content.
     *
     * @param XMLWriter $xml  object
     * @param array     $data with attributes filtered out
     *
     * @return array $data | $nonAttributes
     */
    protected function writeAttr(XMLWriter $xml, $data)
    {
        if (\is_array($data)) {
            $nonAttributes = [];
            foreach ($data as $key => $val) {
                //handle an attribute with elements
                if ('@' == $key[0]) {
                    $xml->writeAttribute(substr($key, 1), $val);
                } elseif ('%' == $key[0]) {
                    if (\is_array($val)) {
                        $nonAttributes = $val;
                    } else {
                        $xml->text($val);
                    }
                } elseif ('#' == $key[0]) {
                    if (\is_array($val)) {
                        $nonAttributes = $val;
                    } else {
                        $xml->startElement(substr($key, 1));
                        $xml->writeCData($val);
                        $xml->endElement();
                    }
                } elseif ('!' == $key[0]) {
                    if (\is_array($val)) {
                        $nonAttributes = $val;
                    } else {
                        $xml->writeCData($val);
                    }
                } //ignore normal elements
                else {
                    $nonAttributes[$key] = $val;
                }
            }

            return $nonAttributes;
        }

        return $data;
    }

    /**
     * Write XML as per Associative Array.
     *
     * @param XMLWriter $xml  object
     * @param array     $data Associative Data Array
     */
    protected function writeEl(XMLWriter $xml, $data)
    {
        foreach ($data as $key => $value) {
            if (\is_array($value) && !$this->isAssoc($value)) { //numeric array
                foreach ($value as $itemValue) {
                    if (\is_array($itemValue)) {
                        $xml->startElement($key);
                        $itemValue = $this->writeAttr($xml, $itemValue);
                        $this->writeEl($xml, $itemValue);
                        $xml->endElement();
                    } else {
                        $itemValue = $this->writeAttr($xml, $itemValue);
                        $xml->writeElement($key, "{$itemValue}");
                    }
                }
            } elseif (\is_array($value)) { //associative array
                $xml->startElement($key);
                $value = $this->writeAttr($xml, $value);
                $this->writeEl($xml, $value);
                $xml->endElement();
            } else { //scalar
                $value = $this->writeAttr($xml, $value);
                $xml->writeElement($key, "{$value}");
            }
        }
    }

    /**
     * Check if array is associative with string based keys
     * FROM: http://stackoverflow.com/questions/173400/php-arrays-a-good-way-to-check-if-an-array-is-associative-or-sequential/4254008#4254008.
     *
     * @param array $array Array to check
     *
     * @return bool
     */
    protected function isAssoc($array)
    {
        return (bool) \count(array_filter(array_keys($array), 'is_string'));
    }
}
