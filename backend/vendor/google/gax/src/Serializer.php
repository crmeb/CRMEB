<?php
/*
 * Copyright 2017, Google Inc.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 *
 *     * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above
 * copyright notice, this list of conditions and the following disclaimer
 * in the documentation and/or other materials provided with the
 * distribution.
 *     * Neither the name of Google Inc. nor the names of its
 * contributors may be used to endorse or promote products derived from
 * this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
namespace Google\GAX;

/**
 * Collection of methods to help with serialization of protobuf objects
 */
class Serializer
{
    private static $jsonCodec;
    private static $phpArrayCodec;

    private static $metadataKnownTypes = [
        'google.rpc.retryinfo-bin' => '\google\rpc\RetryInfo',
        'google.rpc.debuginfo-bin' => '\google\rpc\DebugInfo',
        'google.rpc.quotafailure-bin' => '\google\rpc\QuotaFailure',
        'google.rpc.badrequest-bin' => '\google\rpc\BadRequest',
        'google.rpc.requestinfo-bin' => '\google\rpc\RequestInfo',
        'google.rpc.resourceinfo-bin' => '\google\rpc\ResourceInfo',
        'google.rpc.help-bin' => '\google\rpc\Help',
        'google.rpc.localizedmessage-bin' => '\google\rpc\LocalizedMessage',
    ];

    private static function getJsonCodec()
    {
        if (is_null(self::$jsonCodec)) {
            self::$jsonCodec = new \DrSlump\Protobuf\Codec\Json();
        }
        return self::$jsonCodec;
    }

    private static function getPhpArrayCodec()
    {
        if (is_null(self::$phpArrayCodec)) {
            self::$phpArrayCodec = new \DrSlump\Protobuf\Codec\PhpArray();
        }
        return self::$phpArrayCodec;
    }

    /**
     * @param \DrSlump\Protobuf\Message $message
     * @return string
     */
    public static function serializeToJson($message)
    {
        return $message->serialize(self::getJsonCodec());
    }

    /**
     * @param \DrSlump\Protobuf\Message $message
     * @return string
     */
    public static function serializeToPhpArray($message)
    {
        return $message->serialize(self::getPhpArrayCodec());
    }

    /**
     * Decode metadata received from gRPC status object
     * @param $metadata
     * @return array
     */
    public static function decodeMetadata($metadata)
    {
        if (is_null($metadata) || count($metadata) == 0) {
            return [];
        }
        $result = [];
        foreach ($metadata as $key => $values) {
            $decodedValues = [];
            foreach ($values as $value) {
                if (self::hasBinaryHeaderSuffix($key)) {
                    if (isset(self::$metadataKnownTypes[$key])) {
                        $class = self::$metadataKnownTypes[$key];
                        $message = new $class();
                        $message->parse($value);
                        $decodedValue = self::serializeToPhpArray($message);
                    } else {
                        // The metadata contains an unexpected binary type
                        $decodedValue = "<Binary Data>";
                    }
                } else {
                    $decodedValue = $value;
                }
                $decodedValues[] = $decodedValue;
            }
            $result[$key] = $decodedValues;
        }
        return $result;
    }

    private static function hasBinaryHeaderSuffix($key)
    {
        return substr_compare($key, "-bin", strlen($key) - 4) === 0;
    }
}
