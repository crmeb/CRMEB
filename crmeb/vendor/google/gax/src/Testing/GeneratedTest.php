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
namespace Google\ApiCore\Testing;

use Google\ApiCore\Serializer;
use Google\Protobuf\DescriptorPool;
use Google\Protobuf\Internal\Message;
use Google\Protobuf\Internal\RepeatedField;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
abstract class GeneratedTest extends TestCase
{
    /**
     * @param mixed $expected
     * @param mixed $actual
     */
    public function assertProtobufEquals(&$expected, &$actual)
    {
        if ($expected === $actual) {
            // This is not needed but reduces the number of "This test did not perform any assertions" messages
            $this->assertSame($expected, $actual);

            return;
        }

        if (is_array($expected) || $expected instanceof RepeatedField) {
            if (is_array($expected) === is_array($actual)) {
                $this->assertEquals($expected, $actual);
            }

            $this->assertCount(count($expected), $actual);

            $expectedValues = $this->getValues($expected);
            $actualValues = $this->getValues($actual);

            for ($i = 0; $i < count($expectedValues); $i++) {
                $expectedElement = $expectedValues[$i];
                $actualElement = $actualValues[$i];
                $this->assertProtobufEquals($expectedElement, $actualElement);
            }
        } else {
            $this->assertEquals($expected, $actual);
            if ($expected instanceof Message) {
                $pool = DescriptorPool::getGeneratedPool();
                $descriptor = $pool->getDescriptorByClassName(get_class($expected));

                $fieldCount = $descriptor->getFieldCount();
                for ($i = 0; $i < $fieldCount; $i++) {
                    $field = $descriptor->getField($i);
                    $getter = Serializer::getGetter($field->getName());
                    $expectedFieldValue = $expected->$getter();
                    $actualFieldValue = $actual->$getter();
                    $this->assertProtobufEquals($expectedFieldValue, $actualFieldValue);
                }
            }
        }
    }

    /**
     * @param iterable $field
     */
    private function getValues($field)
    {
        return array_values(
            is_array($field)
                ? $field
                : iterator_to_array($field)
        );
    }
}
