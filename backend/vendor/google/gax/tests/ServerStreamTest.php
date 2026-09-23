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
use Google\GAX\OperationResponse;
use Google\GAX\ServerStream;
use Google\GAX\Testing\MockServerStreamingCall;
use Google\GAX\UnitTests\Mocks\MockPageStreamingResponse;
use Google\GAX\UnitTests\Mocks\MockStatus;
use google\rpc\Status;
use Grpc;
use PHPUnit_Framework_TestCase;

class ServerStreamTest extends PHPUnit_Framework_TestCase
{
    public function testEmptySuccess()
    {
        $call = new MockServerStreamingCall([]);
        $stream = new ServerStream($call);

        $this->assertSame($call, $stream->getServerStreamingCall());
        $this->assertSame([], iterator_to_array($stream->readAll()));
    }

    /**
     * @expectedException \Google\GAX\ApiException
     * @expectedExceptionMessage empty failure
     */
    public function testEmptyFailure()
    {
        $call = new MockServerStreamingCall([], null, new MockStatus(Grpc\STATUS_INTERNAL, 'empty failure'));
        $stream = new ServerStream($call);

        $this->assertSame($call, $stream->getServerStreamingCall());
        iterator_to_array($stream->readAll());
    }

    public function testStringsSuccess()
    {
        $responses = ['abc', 'def'];
        $call = new MockServerStreamingCall($responses);
        $stream = new ServerStream($call);

        $this->assertSame($call, $stream->getServerStreamingCall());
        $this->assertSame($responses, iterator_to_array($stream->readAll()));
    }

    /**
     * @expectedException \Google\GAX\ApiException
     * @expectedExceptionMessage strings failure
     */
    public function testStringsFailure()
    {
        $responses = ['abc', 'def'];
        $call = new MockServerStreamingCall(
            $responses,
            null,
            new MockStatus(Grpc\STATUS_INTERNAL, 'strings failure')
        );
        $stream = new ServerStream($call);

        $this->assertSame($call, $stream->getServerStreamingCall());
        $index = 0;
        try {
            foreach ($stream->readAll() as $response) {
                $this->assertSame($response, $responses[$index]);
                $index++;
            }
        } finally {
            $this->assertSame(2, $index);
        }
    }

    public function testObjectsSuccess()
    {
        $responses = [
            ServerStreamTest::createStatus(Grpc\STATUS_OK, 'response1'),
            ServerStreamTest::createStatus(Grpc\STATUS_OK, 'response2')
        ];
        $serializedResponses = [];
        foreach ($responses as $response) {
            $serializedResponses[] = $response->serialize();
        }
        $call = new MockServerStreamingCall($serializedResponses, '\google\rpc\Status::deserialize');
        $stream = new ServerStream($call);

        $this->assertSame($call, $stream->getServerStreamingCall());
        $this->assertEquals($responses, iterator_to_array($stream->readAll()));
    }

    /**
     * @expectedException \Google\GAX\ApiException
     * @expectedExceptionMessage objects failure
     */
    public function testObjectsFailure()
    {
        $responses = [
            ServerStreamTest::createStatus(Grpc\STATUS_OK, 'response1'),
            ServerStreamTest::createStatus(Grpc\STATUS_OK, 'response2')
        ];
        $serializedResponses = [];
        foreach ($responses as $response) {
            $serializedResponses[] = $response->serialize();
        }
        $call = new MockServerStreamingCall(
            $serializedResponses,
            '\google\rpc\Status::deserialize',
            new MockStatus(Grpc\STATUS_INTERNAL, 'objects failure')
        );
        $stream = new ServerStream($call);

        $this->assertSame($call, $stream->getServerStreamingCall());
        $index = 0;
        try {
            foreach ($stream->readAll() as $response) {
                $this->assertEquals($response, $responses[$index]);
                $index++;
            }
        } finally {
            $this->assertSame(2, $index);
        }
    }

    public function testResourcesSuccess()
    {
        $resources = ['resource1', 'resource2', 'resource3'];
        $responses = [
            MockPageStreamingResponse::createPageStreamingResponse('nextPageToken1', ['resource1']),
            MockPageStreamingResponse::createPageStreamingResponse('nextPageToken1', ['resource2', 'resource3'])
        ];
        $call = new MockServerStreamingCall($responses);
        $stream = new ServerStream($call, [
            'resourcesField' => 'getResourcesList'
        ]);

        $this->assertSame($call, $stream->getServerStreamingCall());
        $this->assertEquals($resources, iterator_to_array($stream->readAll()));
    }

    /**
     * @expectedException \Google\GAX\ApiException
     * @expectedExceptionMessage resources failure
     */
    public function testResourcesFailure()
    {
        $resources = ['resource1', 'resource2', 'resource3'];
        $responses = [
            MockPageStreamingResponse::createPageStreamingResponse('nextPageToken1', ['resource1']),
            MockPageStreamingResponse::createPageStreamingResponse('nextPageToken1', ['resource2', 'resource3'])
        ];
        $call = new MockServerStreamingCall(
            $responses,
            null,
            new MockStatus(Grpc\STATUS_INTERNAL, 'resources failure')
        );
        $stream = new ServerStream($call, [
            'resourcesField' => 'getResourcesList'
        ]);

        $this->assertSame($call, $stream->getServerStreamingCall());
        $index = 0;
        try {
            foreach ($stream->readAll() as $response) {
                $this->assertSame($response, $resources[$index]);
                $index++;
            }
        } finally {
            $this->assertSame(3, $index);
        }
    }

    private static function createStatus($code, $message)
    {
        $status = new Status();
        $status->setCode($code)->setMessage($message);
        return $status;
    }
}
