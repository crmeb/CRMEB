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

use Google\GAX\OperationResponse;
use google\longrunning\Operation;
use Google\GAX\LongRunning\OperationsClient;
use google\protobuf\Any;
use google\rpc\Status;
use PHPUnit_Framework_TestCase;

class OperationResponseTest extends PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $opName = 'operations/opname';
        $opClient = self::createOperationsClient();
        $op = new OperationResponse($opName, $opClient);

        $this->assertSame($opName, $op->getName());
        $this->assertSame($opClient, $op->getOperationsClient());
    }

    public function testWithoutResponse()
    {
        $opName = 'operations/opname';
        $opClient = self::createOperationsClient();
        $op = new OperationResponse($opName, $opClient);

        $this->assertNull($op->getLastProtoResponse());
        $this->assertFalse($op->isDone());
        $this->assertNull($op->getResult());
        $this->assertNull($op->getError());
        $this->assertNull($op->getMetadata());
        $this->assertFalse($op->operationSucceeded());
        $this->assertFalse($op->operationFailed());
        $this->assertEquals([
            'operationReturnType' => null,
            'metadataReturnType' => null,
        ], $op->getReturnTypeOptions());
    }

    public function testWithResponse()
    {
        $opName = 'operations/opname';
        $opClient = self::createOperationsClient();
        $protoResponse = new Operation();
        $op = new OperationResponse($opName, $opClient, [
            'lastProtoResponse' => $protoResponse,
        ]);

        $this->assertSame($protoResponse, $op->getLastProtoResponse());
        $this->assertFalse($op->isDone());
        $this->assertNull($op->getResult());
        $this->assertNull($op->getError());
        $this->assertNull($op->getMetadata());
        $this->assertFalse($op->operationSucceeded());
        $this->assertFalse($op->operationFailed());
        $this->assertEquals([
            'operationReturnType' => null,
            'metadataReturnType' => null,
        ], $op->getReturnTypeOptions());

        $response = self::createAny(self::createStatus(0, "response"));
        $error = self::createStatus(2, "error");
        $metadata = self::createAny(self::createStatus(0, "metadata"));

        $protoResponse->setDone(true)->setResponse($response)->setMetadata($metadata);
        $this->assertTrue($op->isDone());
        $this->assertSame($response, $op->getResult());
        $this->assertSame($metadata, $op->getMetadata());
        $this->assertTrue($op->operationSucceeded());
        $this->assertFalse($op->operationFailed());

        $protoResponse->clearResponse()->setError($error);
        $this->assertNull($op->getResult());
        $this->assertSame($error, $op->getError());
        $this->assertFalse($op->operationSucceeded());
        $this->assertTrue($op->operationFailed());
    }

    public function testWithOptions()
    {
        $opName = 'operations/opname';
        $opClient = self::createOperationsClient();
        $protoResponse = new Operation();
        $op = new OperationResponse($opName, $opClient, [
            'operationReturnType' => '\google\rpc\Status',
            'metadataReturnType' => '\google\protobuf\Any',
            'lastProtoResponse' => $protoResponse,
        ]);

        $this->assertSame($protoResponse, $op->getLastProtoResponse());
        $this->assertFalse($op->isDone());
        $this->assertNull($op->getResult());
        $this->assertNull($op->getError());
        $this->assertNull($op->getMetadata());
        $this->assertEquals([
            'operationReturnType' => '\google\rpc\Status',
            'metadataReturnType' => '\google\protobuf\Any',
        ], $op->getReturnTypeOptions());

        $innerResponse = self::createStatus(0, "response");
        $innerMetadata = (new Any())->setValue("metadata");

        $response = self::createAny($innerResponse);
        $metadata = self::createAny($innerMetadata);

        $protoResponse->setDone(true)->setResponse($response)->setMetadata($metadata);
        $this->assertTrue($op->isDone());
        $this->assertEquals($innerResponse, $op->getResult());
        $this->assertEquals($innerMetadata, $op->getMetadata());
    }

    public static function createAny($value)
    {
        $any = new Any();
        return $any->setValue($value->serialize());
    }

    public static function createStatus($code, $message)
    {
        $value = new Status();
        return $value->setCode($code)->setMessage($message);
    }

    public static function createOperationsClient($stub = null)
    {
        $client = new OperationsClient([
            'createOperationsStubFunction' => function ($hostname, $opts) use ($stub) {
                return $stub;
            },
            'serviceAddress' => '',
            'scopes' => [],
        ]);
        return $client;
    }
}
