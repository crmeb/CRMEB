<?php
/*
 * Copyright 2017 Google LLC
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
namespace Google\ApiCore;

use Google\Protobuf\Any;
use Google\Protobuf\Descriptor;
use Google\Protobuf\DescriptorPool;
use Google\Protobuf\FieldDescriptor;
use Google\Protobuf\Internal\Message;
use RuntimeException;

/**
 * Collection of methods to help with serialization of protobuf objects
 */
class Serializer
{
    const MAP_KEY_FIELD_NAME = 'key';
    const MAP_VALUE_FIELD_NAME = 'value';

    private static $phpArraySerializer;

    private static $metadataKnownTypes = [
        'google.rpc.retryinfo-bin' => \Google\Rpc\RetryInfo::class,
        'google.rpc.debuginfo-bin' => \Google\Rpc\DebugInfo::class,
        'google.rpc.quotafailure-bin' => \Google\Rpc\QuotaFailure::class,
        'google.rpc.badrequest-bin' => \Google\Rpc\BadRequest::class,
        'google.rpc.requestinfo-bin' => \Google\Rpc\RequestInfo::class,
        'google.rpc.resourceinfo-bin' => \Google\Rpc\ResourceInfo::class,
        'google.rpc.errorinfo-bin' => \Google\Rpc\ErrorInfo::class,
        'google.rpc.help-bin' => \Google\Rpc\Help::class,
        'google.rpc.localizedmessage-bin' => \Google\Rpc\LocalizedMessage::class,
    ];

    private $fieldTransformers;
    private $messageTypeTransformers;
    private $decodeFieldTransformers;
    private $decodeMessageTypeTransformers;

    private $descriptorMaps = [];

    /**
     * Serializer constructor.
     *
     * @param array $fieldTransformers An array mapping field names to transformation functions
     * @param array $messageTypeTransformers An array mapping message names to transformation functions
     * @param array $decodeFieldTransformers An array mapping field names to transformation functions
     * @param array $decodeMessageTypeTransformers An array mapping message names to transformation functions
     */
    public function __construct(
        $fieldTransformers = [],
        $messageTypeTransformers = [],
        $decodeFieldTransformers = [],
        $decodeMessageTypeTransformers = []
    ) {
        $this->fieldTransformers = $fieldTransformers;
        $this->messageTypeTransformers = $messageTypeTransformers;
        $this->decodeFieldTransformers = $decodeFieldTransformers;
        $this->decodeMessageTypeTransformers = $decodeMessageTypeTransformers;
    }

    /**
     * Encode protobuf message as a PHP array
     *
     * @param mixed $message
     * @return array
     * @throws ValidationException
     */
    public function encodeMessage($message)
    {
        // Get message descriptor
        $pool = DescriptorPool::getGeneratedPool();
        $messageType = $pool->getDescriptorByClassName(get_class($message));
        try {
            return $this->encodeMessageImpl($message, $messageType);
        } catch (\Exception $e) {
            throw new ValidationException(
                "Error encoding message: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Decode PHP array into the specified protobuf message
     *
     * @param mixed $message
     * @param array $data
     * @return mixed
     * @throws ValidationException
     */
    public function decodeMessage($message, array $data)
    {
        // Get message descriptor
        $pool = DescriptorPool::getGeneratedPool();
        $messageType = $pool->getDescriptorByClassName(get_class($message));
        try {
            return $this->decodeMessageImpl($message, $messageType, $data);
        } catch (\Exception $e) {
            throw new ValidationException(
                "Error decoding message: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param Message $message
     * @return string Json representation of $message
     * @throws ValidationException
     */
    public static function serializeToJson(Message $message)
    {
        return json_encode(self::serializeToPhpArray($message), JSON_PRETTY_PRINT);
    }

    /**
     * @param Message $message
     * @return array PHP array representation of $message
     * @throws ValidationException
     */
    public static function serializeToPhpArray(Message $message)
    {
        return self::getPhpArraySerializer()->encodeMessage($message);
    }

    /**
     * Decode metadata received from gRPC status object
     *
     * @param array $metadata
     * @return array
     */
    public static function decodeMetadata(array $metadata)
    {
        if (count($metadata) == 0) {
            return [];
        }
        $result = [];
        foreach ($metadata as $key => $values) {
            foreach ($values as $value) {
                $decodedValue = [
                    '@type' => $key,
                ];
                if (self::hasBinaryHeaderSuffix($key)) {
                    if (isset(self::$metadataKnownTypes[$key])) {
                        $class = self::$metadataKnownTypes[$key];
                        /** @var Message $message */
                        $message = new $class();
                        try {
                            $message->mergeFromString($value);
                            $decodedValue += self::serializeToPhpArray($message);
                        } catch (\Exception $e) {
                            // We encountered an error trying to deserialize the data
                            $decodedValue += [
                                'data' => '<Unable to deserialize data>',
                            ];
                        }
                    } else {
                        // The metadata contains an unexpected binary type
                        $decodedValue += [
                            'data' => '<Unknown Binary Data>',
                        ];
                    }
                } else {
                    $decodedValue += [
                        'data' => $value,
                    ];
                }
                $result[] = $decodedValue;
            }
        }
        return $result;
    }

    /**
     * Decode an array of Any messages into a printable PHP array.
     *
     * @param iterable $anyArray
     * @return array
     */
    public static function decodeAnyMessages($anyArray)
    {
        $results = [];
        foreach ($anyArray as $any) {
            try {
                /** @var Any $any */
                /** @var Message $unpacked */
                $unpacked = $any->unpack();
                $results[] = self::serializeToPhpArray($unpacked);
            } catch (\Exception $ex) {
                echo "$ex\n";
                // failed to unpack the $any object - show as unknown binary data
                $results[] = [
                    'typeUrl' => $any->getTypeUrl(),
                    'value' => '<Unknown Binary Data>',
                ];
            }
        }
        return $results;
    }

    /**
     * @param FieldDescriptor $field
     * @param Message|array|string $data
     * @return mixed
     * @throws \Exception
     */
    private function encodeElement(FieldDescriptor $field, $data)
    {
        switch ($field->getType()) {
            case GPBType::MESSAGE:
                if (is_array($data)) {
                    $result = $data;
                } else {
                    $result = $this->encodeMessageImpl($data, $field->getMessageType());
                }
                $messageType = $field->getMessageType()->getFullName();
                if (isset($this->messageTypeTransformers[$messageType])) {
                    $result = $this->messageTypeTransformers[$messageType]($result);
                }
                break;
            default:
                $result = $data;
                break;
        }

        if (isset($this->fieldTransformers[$field->getName()])) {
            $result = $this->fieldTransformers[$field->getName()]($result);
        }
        return $result;
    }

    private function getDescriptorMaps(Descriptor $descriptor)
    {
        if (!isset($this->descriptorMaps[$descriptor->getFullName()])) {
            $fieldsByName = [];
            $fieldCount = $descriptor->getFieldCount();
            for ($i = 0; $i < $fieldCount; $i++) {
                $field = $descriptor->getField($i);
                $fieldsByName[$field->getName()] = $field;
            }
            $fieldToOneof = [];
            $oneofCount = $descriptor->getOneofDeclCount();
            for ($i = 0; $i < $oneofCount; $i++) {
                $oneof = $descriptor->getOneofDecl($i);
                $oneofFieldCount = $oneof->getFieldCount();
                for ($j = 0; $j < $oneofFieldCount; $j++) {
                    $field = $oneof->getField($j);
                    $fieldToOneof[$field->getName()] = $oneof->getName();
                }
            }
            $this->descriptorMaps[$descriptor->getFullName()] = [$fieldsByName, $fieldToOneof];
        }
        return $this->descriptorMaps[$descriptor->getFullName()];
    }

    /**
     * @param Message $message
     * @param Descriptor $messageType
     * @return array
     * @throws \Exception
     */
    private function encodeMessageImpl(Message $message, Descriptor $messageType)
    {
        $data = [];

        $fieldCount = $messageType->getFieldCount();
        for ($i = 0; $i < $fieldCount; $i++) {
            $field = $messageType->getField($i);
            $key = $field->getName();
            $getter = $this->getGetter($key);
            $v = $message->$getter();

            if (is_null($v)) {
                continue;
            }

            // Check and skip unset fields inside oneofs
            list($_, $fieldsToOneof) = $this->getDescriptorMaps($messageType);
            if (isset($fieldsToOneof[$key])) {
                $oneofName = $fieldsToOneof[$key];
                $oneofGetter =  $this->getGetter($oneofName);
                if ($message->$oneofGetter() !== $key) {
                    continue;
                }
            }

            if ($field->isMap()) {
                list($mapFieldsByName, $_) = $this->getDescriptorMaps($field->getMessageType());
                $keyField = $mapFieldsByName[self::MAP_KEY_FIELD_NAME];
                $valueField = $mapFieldsByName[self::MAP_VALUE_FIELD_NAME];
                $arr = [];
                foreach ($v as $k => $vv) {
                    $arr[$this->encodeElement($keyField, $k)] = $this->encodeElement($valueField, $vv);
                }
                $v = $arr;
            } elseif ($field->getLabel() === GPBLabel::REPEATED) {
                $arr = [];
                foreach ($v as $k => $vv) {
                    $arr[$k] = $this->encodeElement($field, $vv);
                }
                $v = $arr;
            } else {
                $v = $this->encodeElement($field, $v);
            }

            $key = self::toCamelCase($key);
            $data[$key] = $v;
        }

        return $data;
    }

    /**
     * @param FieldDescriptor $field
     * @param mixed $data
     * @return mixed
     * @throws \Exception
     */
    private function decodeElement(FieldDescriptor $field, $data)
    {
        if (isset($this->decodeFieldTransformers[$field->getName()])) {
            $data = $this->decodeFieldTransformers[$field->getName()]($data);
        }

        switch ($field->getType()) {
            case GPBType::MESSAGE:
                if ($data instanceof Message) {
                    return $data;
                }
                $messageType = $field->getMessageType();
                $messageTypeName = $messageType->getFullName();
                $klass = $messageType->getClass();
                $msg = new $klass();
                if (isset($this->decodeMessageTypeTransformers[$messageTypeName])) {
                    $data = $this->decodeMessageTypeTransformers[$messageTypeName]($data);
                }

                return $this->decodeMessageImpl($msg, $messageType, $data);
            default:
                return $data;
        }
    }

    /**
     * @param Message $message
     * @param Descriptor $messageType
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    private function decodeMessageImpl(Message $message, Descriptor $messageType, array $data)
    {
        list($fieldsByName, $_) = $this->getDescriptorMaps($messageType);
        foreach ($data as $key => $v) {
            // Get the field by tag number or name
            $fieldName = self::toSnakeCase($key);

            // Unknown field found
            if (!isset($fieldsByName[$fieldName])) {
                throw new RuntimeException(sprintf(
                    "cannot handle unknown field %s on message %s",
                    $fieldName,
                    $messageType->getFullName()
                ));
            }

            /** @var FieldDescriptor $field */
            $field = $fieldsByName[$fieldName];

            if ($field->isMap()) {
                list($mapFieldsByName, $_) = $this->getDescriptorMaps($field->getMessageType());
                $keyField = $mapFieldsByName[self::MAP_KEY_FIELD_NAME];
                $valueField = $mapFieldsByName[self::MAP_VALUE_FIELD_NAME];
                $arr = [];
                foreach ($v as $k => $vv) {
                    $arr[$this->decodeElement($keyField, $k)] = $this->decodeElement($valueField, $vv);
                }
                $value = $arr;
            } elseif ($field->getLabel() === GPBLabel::REPEATED) {
                $arr = [];
                foreach ($v as $k => $vv) {
                    $arr[$k] = $this->decodeElement($field, $vv);
                }
                $value = $arr;
            } else {
                $value = $this->decodeElement($field, $v);
            }

            $setter = $this->getSetter($field->getName());
            $message->$setter($value);

            // We must unset $value here, otherwise the protobuf c extension will mix up the references
            // and setting one value will change all others
            unset($value);
        }
        return $message;
    }

    /**
     * @param string $name
     * @return string Getter function
     */
    public static function getGetter(string $name)
    {
        return 'get' . ucfirst(self::toCamelCase($name));
    }

    /**
     * @param string $name
     * @return string Setter function
     */
    public static function getSetter(string $name)
    {
        return 'set' . ucfirst(self::toCamelCase($name));
    }

    /**
     * Convert string from camelCase to snake_case
     *
     * @param string $key
     * @return string
     */
    public static function toSnakeCase(string $key)
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $key));
    }

    /**
     * Convert string from snake_case to camelCase
     *
     * @param string $key
     * @return string
     */
    public static function toCamelCase(string $key)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
    }

    private static function hasBinaryHeaderSuffix(string $key)
    {
        return substr_compare($key, "-bin", strlen($key) - 4) === 0;
    }

    private static function getPhpArraySerializer()
    {
        if (is_null(self::$phpArraySerializer)) {
            self::$phpArraySerializer = new Serializer();
        }
        return self::$phpArraySerializer;
    }

    public static function loadKnownMetadataTypes()
    {
        foreach (self::$metadataKnownTypes as $key => $class) {
            new $class;
        }
    }
}

// It is necessary to call this when this file is included. Otherwise we cannot be
// guaranteed that the relevant classes will be loaded into the protobuf descriptor
// pool when we try to unpack an Any object containing that class.
// phpcs:disable PSR1.Files.SideEffects
Serializer::loadKnownMetadataTypes();
// phpcs:enable
