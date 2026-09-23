<?php
/*
 * Copyright 2016, Google Inc.
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
namespace Google\GAX\UnitTests;

use Google\GAX\ApiException;
use Google\GAX\Serializer;
use google\protobuf\Duration;
use google\rpc\BadRequest;
use google\rpc\DebugInfo;
use google\rpc\Help;
use google\rpc\LocalizedMessage;
use google\rpc\QuotaFailure;
use google\rpc\RequestInfo;
use google\rpc\ResourceInfo;
use google\rpc\RetryInfo;
use PHPUnit_Framework_TestCase;
use Grpc;

class ApiExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testWithoutMetadata()
    {
        $status = new \stdClass();
        $status->code = Grpc\STATUS_OK;
        $status->details = 'testWithoutMetadata';

        $apiException = ApiException::createFromStdClass($status);

        $expectedMessage = json_encode([
            'message' => 'testWithoutMetadata',
            'code' => Grpc\STATUS_OK,
            'status' => 'OK',
            'details' => []
        ], JSON_PRETTY_PRINT);

        $this->assertSame(Grpc\STATUS_OK, $apiException->getCode());
        $this->assertSame($expectedMessage, $apiException->getMessage());
        $this->assertNull($apiException->getMetadata());
    }

    /**
     * @dataProvider getMetadata
     */
    public function testWithMetadata($metadata, $metadataArray)
    {
        $status = new \stdClass();
        $status->code = Grpc\STATUS_OK;
        $status->details = 'testWithMetadata';
        $status->metadata = $metadata;

        $apiException = ApiException::createFromStdClass($status);

        $expectedMessage = json_encode([
            'message' => 'testWithMetadata',
            'code' => Grpc\STATUS_OK,
            'status' => 'OK',
            'details' => $metadataArray
        ], JSON_PRETTY_PRINT);

        $this->assertSame(Grpc\STATUS_OK, $apiException->getCode());
        $this->assertSame($expectedMessage, $apiException->getMessage());
        $this->assertSame($metadata, $apiException->getMetadata());
    }

    public function getMetadata()
    {
        $retryInfo = new RetryInfo();
        $duration = new Duration();
        $duration->setSeconds(1);
        $duration->setNanos(2);
        $retryInfo->setRetryDelay($duration);

        $unknownBinData = ['unknown-bin' => ['<Binary Data>']];
        $asciiData = ['ascii' => ['ascii-data']];
        $retryInfoData = [
            'google.rpc.retryinfo-bin' => [
                [
                    'retry_delay' => [
                        'seconds' => 1,
                        'nanos' => 2,
                    ]
                ]
            ]
        ];
        $allKnownTypesData = [
            'google.rpc.retryinfo-bin' => [[]],
            'google.rpc.debuginfo-bin' => [[]],
            'google.rpc.quotafailure-bin' => [[]],
            'google.rpc.badrequest-bin' => [[]],
            'google.rpc.requestinfo-bin' => [[]],
            'google.rpc.resourceinfo-bin' => [[]],
            'google.rpc.help-bin' => [[]],
            'google.rpc.localizedmessage-bin' => [[]],
        ];

        return [
            [['unknown-bin' => ['some-data-that-should-not-appear']], $unknownBinData],
            [['ascii' => ['ascii-data']], $asciiData],
            [['google.rpc.retryinfo-bin' => [$retryInfo->serialize()]], $retryInfoData],
            [[
                'google.rpc.retryinfo-bin' => [(new RetryInfo())->serialize()],
                'google.rpc.debuginfo-bin' => [(new DebugInfo())->serialize()],
                'google.rpc.quotafailure-bin' => [(new QuotaFailure())->serialize()],
                'google.rpc.badrequest-bin' => [(new BadRequest())->serialize()],
                'google.rpc.requestinfo-bin' => [(new RequestInfo())->serialize()],
                'google.rpc.resourceinfo-bin' => [(new ResourceInfo())->serialize()],
                'google.rpc.help-bin' => [(new Help())->serialize()],
                'google.rpc.localizedmessage-bin' => [(new LocalizedMessage())->serialize()],
            ], $allKnownTypesData],
        ];
    }
}
