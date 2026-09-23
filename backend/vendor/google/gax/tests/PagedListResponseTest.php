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

use Google\GAX\PagedListResponse;
use Google\GAX\PageStreamingDescriptor;
use Google\GAX\UnitTests\Mocks\MockStub;
use Google\GAX\UnitTests\Mocks\MockPageStreamingRequest;
use Google\GAX\UnitTests\Mocks\MockPageStreamingResponse;
use PHPUnit_Framework_TestCase;

class PagedListResponseTest extends PHPUnit_Framework_TestCase
{
    public function testNextPageToken()
    {
        $mockRequest = MockPageStreamingRequest::createPageStreamingRequest('mockToken');
        $descriptor = new PageStreamingDescriptor([
            'requestPageTokenField' => 'pageToken',
            'responsePageTokenField' => 'nextPageToken',
            'resourceField' => 'resource'
        ]);
        $response = MockPageStreamingResponse::createPageStreamingResponse('nextPageToken1', ['resource1']);
        $stub = MockStub::create($response);
        $mockApiCall = function () use ($stub) {
            list($response, $status) =
                call_user_func_array(array($stub, 'takeAction'), func_get_args())->wait();
            return $response;
        };
        $pageAccessor = new PagedListResponse([$mockRequest, [], []], $mockApiCall, $descriptor);
        $page = $pageAccessor->getPage();
        $this->assertEquals($page->getNextPageToken(), 'nextPageToken1');
        $this->assertEquals(iterator_to_array($page->getIterator()), ['resource1']);
    }
}
