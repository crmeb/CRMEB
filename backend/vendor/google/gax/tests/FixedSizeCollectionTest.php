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

use Google\GAX\Page;
use Google\GAX\FixedSizeCollection;
use Google\GAX\PageStreamingDescriptor;
use Google\GAX\UnitTests\Mocks\MockStub;
use Google\GAX\UnitTests\Mocks\MockStatus;
use Google\GAX\UnitTests\Mocks\MockPageStreamingRequest;
use Google\GAX\UnitTests\Mocks\MockPageStreamingResponse;
use PHPUnit_Framework_TestCase;
use Grpc;

class FixedSizeCollectionTest extends PHPUnit_Framework_TestCase
{
    private static function createPage($responseSequence)
    {
        $mockRequest = MockPageStreamingRequest::createPageStreamingRequest('token', 3);
        $stub = MockStub::createWithResponseSequence($responseSequence);
        $descriptor = new PageStreamingDescriptor([
            'requestPageTokenField' => 'pageToken',
            'requestPageSizeField' => 'pageSize',
            'responsePageTokenField' => 'nextPageToken',
            'resourceField' => 'resource'
        ]);
        $mockApiCall = function () use ($stub) {
            list($response, $status) =
                call_user_func_array(array($stub, 'takeAction'), func_get_args())->wait();
            return $response;
        };
        return new Page([$mockRequest, [], []], $mockApiCall, $descriptor);
    }

    public function testFixedCollectionMethods()
    {
        $responseA = MockPageStreamingResponse::createPageStreamingResponse(
            'nextPageToken1',
            ['resource1', 'resource2']
        );
        $responseB = MockPageStreamingResponse::createPageStreamingResponse(
            'nextPageToken2',
            ['resource3', 'resource4', 'resource5']
        );
        $responseC = MockPageStreamingResponse::createPageStreamingResponse(
            'nextPageToken3',
            ['resource6', 'resource7']
        );
        $responseD = MockPageStreamingResponse::createPageStreamingResponse(
            '',
            ['resource8', 'resource9']
        );
        $page = FixedSizeCollectionTest::createPage([
            [$responseA, new MockStatus(Grpc\STATUS_OK, '')],
            [$responseB, new MockStatus(Grpc\STATUS_OK, '')],
            [$responseC, new MockStatus(Grpc\STATUS_OK, '')],
            [$responseD, new MockStatus(Grpc\STATUS_OK, '')],
        ]);

        $fixedSizeCollection = new FixedSizeCollection($page, 5);

        $this->assertEquals($fixedSizeCollection->getCollectionSize(), 5);
        $this->assertEquals($fixedSizeCollection->hasNextCollection(), true);
        $this->assertEquals($fixedSizeCollection->getNextPageToken(), 'nextPageToken2');
        $results = iterator_to_array($fixedSizeCollection);
        $this->assertEquals(
            $results,
            ['resource1', 'resource2', 'resource3', 'resource4', 'resource5']
        );

        $nextCollection = $fixedSizeCollection->getNextCollection();

        $this->assertEquals($nextCollection->getCollectionSize(), 4);
        $this->assertEquals($nextCollection->hasNextCollection(), false);
        $this->assertEquals($nextCollection->getNextPageToken(), '');
        $results = iterator_to_array($nextCollection);
        $this->assertEquals(
            $results,
            ['resource6', 'resource7', 'resource8', 'resource9']
        );
    }
}
