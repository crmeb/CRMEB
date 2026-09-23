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
use Google\GAX\BidiStream;
use Google\GAX\Testing\MockBidiStreamingCall;
use Google\GAX\UnitTests\Mocks\MockPageStreamingResponse;
use Google\GAX\UnitTests\Mocks\MockStatus;
use google\rpc\Status;
use Grpc;
use PHPUnit_Framework_TestCase;

class BidiStreamTest extends PHPUnit_Framework_TestCase
{
    public function testEmptySuccess()
    {
        $call = new MockBidiStreamingCall([]);
        $stream = new BidiStream($call);

        $this->assertSame($call, $stream->getBidiStreamingCall());
        $this->assertSame([], iterator_to_array($stream->closeWriteAndReadAll()));
    }

    /**
     * @expectedException \Google\GAX\ApiException
     * @expectedExceptionMessage empty failure read
     */
    public function testEmptyFailureRead()
    {
        $call = new MockBidiStreamingCall([], null, new MockStatus(Grpc\STATUS_INTERNAL, 'empty failure read'));
        $stream = new BidiStream($call);

        $this->assertSame($call, $stream->getBidiStreamingCall());
        $stream->closeWrite();
        $stream->read();
    }

    /**
     * @expectedException \Google\GAX\ApiException
     * @expectedExceptionMessage empty failure readall
     */
    public function testEmptyFailureReadAll()
    {
        $call = new MockBidiStreamingCall([], null, new MockStatus(Grpc\STATUS_INTERNAL, 'empty failure readall'));
        $stream = new BidiStream($call);

        $this->assertSame($call, $stream->getBidiStreamingCall());
        iterator_to_array($stream->closeWriteAndReadAll());
    }

    /**
     * @expectedException \Google\GAX\ValidationException
     * @expectedExceptionMessage Cannot call read() after streaming call is complete.
     */
    public function testReadAfterComplete()
    {
        $call = new MockBidiStreamingCall([]);
        $stream = new BidiStream($call);

        $this->assertSame($call, $stream->getBidiStreamingCall());
        $stream->closeWrite();
        $this->assertNull($stream->read());
        $stream->read();
    }

    /**
     * @expectedException \Google\GAX\ValidationException
     * @expectedExceptionMessage Cannot call write() after streaming call is complete.
     */
    public function testWriteAfterComplete()
    {
        $call = new MockBidiStreamingCall([]);
        $stream = new BidiStream($call);

        $this->assertSame($call, $stream->getBidiStreamingCall());
        $stream->closeWrite();
        $this->assertNull($stream->read());
        $stream->write('request');
    }

    /**
     * @expectedException \Google\GAX\ValidationException
     * @expectedExceptionMessage Cannot call write() after calling closeWrite().
     */
    public function testWriteAfterCloseWrite()
    {
        $call = new MockBidiStreamingCall([]);
        $stream = new BidiStream($call);

        $this->assertSame($call, $stream->getBidiStreamingCall());
        $stream->closeWrite();
        $stream->write('request');
    }

    public function testReadStringsSuccess()
    {
        $responses = ['abc', 'def'];
        $call = new MockBidiStreamingCall($responses);
        $stream = new BidiStream($call);

        $this->assertSame($call, $stream->getBidiStreamingCall());
        $this->assertSame($responses, iterator_to_array($stream->closeWriteAndReadAll()));
    }

    public function testReadObjectsSuccess()
    {
        $responses = [
            BidiStreamTest::createStatus(Grpc\STATUS_OK, 'response1'),
            BidiStreamTest::createStatus(Grpc\STATUS_OK, 'response2')
        ];
        $serializedResponses = [];
        foreach ($responses as $response) {
            $serializedResponses[] = $response->serialize();
        }
        $call = new MockBidiStreamingCall($serializedResponses, '\google\rpc\Status::deserialize');
        $stream = new BidiStream($call);

        $this->assertSame($call, $stream->getBidiStreamingCall());
        $this->assertEquals($responses, iterator_to_array($stream->closeWriteAndReadAll()));
    }

    public function testReadCloseReadSuccess()
    {
        $responses = [
            BidiStreamTest::createStatus(Grpc\STATUS_OK, 'response1'),
            BidiStreamTest::createStatus(Grpc\STATUS_OK, 'response2')
        ];
        $serializedResponses = [];
        foreach ($responses as $response) {
            $serializedResponses[] = $response->serialize();
        }
        $call = new MockBidiStreamingCall($serializedResponses, '\google\rpc\Status::deserialize');
        $stream = new BidiStream($call);

        $this->assertSame($call, $stream->getBidiStreamingCall());
        $response = $stream->read();
        $stream->closeWrite();
        $index = 0;
        while (!is_null($response)) {
            $this->assertEquals($response, $responses[$index]);
            $response = $stream->read();
            $index++;
        }
    }

    /**
     * @expectedException \Google\GAX\ApiException
     * @expectedExceptionMessage read failure
     */
    public function testReadFailure()
    {
        $responses = ['abc', 'def'];
        $call = new MockBidiStreamingCall(
            $responses,
            null,
            new MockStatus(Grpc\STATUS_INTERNAL, 'read failure')
        );
        $stream = new BidiStream($call);

        $this->assertSame($call, $stream->getBidiStreamingCall());
        $index = 0;
        try {
            foreach ($stream->closeWriteAndReadAll() as $response) {
                $this->assertSame($response, $responses[$index]);
                $index++;
            }
        } finally {
            $this->assertSame(2, $index);
        }
    }

    public function testWriteStringsSuccess()
    {
        $requests = ['request1', 'request2'];
        $responses = [];
        $call = new MockBidiStreamingCall($responses);
        $stream = new BidiStream($call);

        $stream->writeAll($requests);

        $this->assertSame($call, $stream->getBidiStreamingCall());
        $this->assertSame([], iterator_to_array($stream->closeWriteAndReadAll()));
        $this->assertEquals($requests, $call->popReceivedCalls());
    }

    public function testWriteObjectsSuccess()
    {
        $requests = [
            BidiStreamTest::createStatus(Grpc\STATUS_OK, 'request1'),
            BidiStreamTest::createStatus(Grpc\STATUS_OK, 'request2')
        ];
        $responses = [];
        $call = new MockBidiStreamingCall($responses, '\google\rpc\Status::deserialize');
        $stream = new BidiStream($call);

        $stream->writeAll($requests);

        $this->assertSame($call, $stream->getBidiStreamingCall());
        $this->assertSame([], iterator_to_array($stream->closeWriteAndReadAll()));
        $this->assertEquals($requests, $call->popReceivedCalls());
    }

    public function testAlternateReadWriteObjectsSuccess()
    {
        $requests = [
            BidiStreamTest::createStatus(Grpc\STATUS_OK, 'request1'),
            BidiStreamTest::createStatus(Grpc\STATUS_OK, 'request2'),
            BidiStreamTest::createStatus(Grpc\STATUS_OK, 'request3')
        ];
        $responses = [
            BidiStreamTest::createStatus(Grpc\STATUS_OK, 'response1'),
            BidiStreamTest::createStatus(Grpc\STATUS_OK, 'response2'),
            BidiStreamTest::createStatus(Grpc\STATUS_OK, 'response3'),
            BidiStreamTest::createStatus(Grpc\STATUS_OK, 'response4')
        ];
        $serializedResponses = [];
        foreach ($responses as $response) {
            $serializedResponses[] = $response->serialize();
        }
        $call = new MockBidiStreamingCall($serializedResponses, '\google\rpc\Status::deserialize');
        $stream = new BidiStream($call);

        $index = 0;
        foreach ($requests as $request) {
            $stream->write($request);
            $response = $stream->read();
            $this->assertEquals($response, $responses[$index]);
            $index++;
        }
        $stream->closeWrite();
        $response = $stream->read();
        while (!is_null($response)) {
            $this->assertEquals($response, $responses[$index]);
            $index++;
            $response = $stream->read();
        }

        $this->assertSame($call, $stream->getBidiStreamingCall());
        $this->assertEquals($requests, $call->popReceivedCalls());
    }

    /**
     * @expectedException \Google\GAX\ApiException
     * @expectedExceptionMessage write failure without close
     */
    public function testWriteFailureWithoutClose()
    {
        $request = 'request';
        $responses = [null];
        $call = new MockBidiStreamingCall(
            $responses,
            null,
            new MockStatus(Grpc\STATUS_INTERNAL, 'write failure without close')
        );
        $stream = new BidiStream($call);

        $this->assertSame($call, $stream->getBidiStreamingCall());
        $stream->write($request);

        try {
            $stream->read();
        } finally {
            $this->assertEquals([$request], $call->popReceivedCalls());
        }
    }

    public function testResourcesSuccess()
    {
        $resources = ['resource1', 'resource2', 'resource3'];
        $responses = [
            MockPageStreamingResponse::createPageStreamingResponse('nextPageToken1', ['resource1']),
            MockPageStreamingResponse::createPageStreamingResponse('nextPageToken1', ['resource2', 'resource3'])
        ];
        $call = new MockBidiStreamingCall($responses);
        $stream = new BidiStream($call, [
            'resourcesField' => 'getResourcesList'
        ]);

        $this->assertSame($call, $stream->getBidiStreamingCall());
        $this->assertEquals($resources, iterator_to_array($stream->closeWriteAndReadAll()));
    }

    private static function createStatus($code, $message)
    {
        $status = new Status();
        $status->setCode($code)->setMessage($message);
        return $status;
    }
}
