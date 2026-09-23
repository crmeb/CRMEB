<?php

namespace AlibabaCloud\Tea\XML;

class XML
{
    public static function parseXml($xmlStr, $response)
    {
        $res    = self::parse($xmlStr);
        if ($response === null) {
            return $res;
        } else {
            if (\is_string($response)) {
                $response = new $response();
            }
            $prop   = get_object_vars($response);
            $target = [];

            foreach ($res as $k => $v) {
                if (isset($prop[$k])) {
                    $target[$k] = $v;
                }
            }
            return $target;
        }
    }

    public static function toXML($array)
    {
        $arrayToXml = new ArrayToXml();
        if (\is_object($array)) {
            $tmp      = explode('\\', \get_class($array));
            $rootName = $tmp[\count($tmp) - 1];
            $data     = json_decode(json_encode($array), true);
        } else {
            $tmp = $array;
            reset($tmp);
            $rootName = key($tmp);
            $data     = $array[$rootName];
        }
        ksort($data);

        return $arrayToXml->buildXML($data, $rootName);
    }

    private static function parse($xml)
    {
        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader(true);
        }

        return json_decode(
            json_encode(
                simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)
            ),
            true
        );
    }
}
