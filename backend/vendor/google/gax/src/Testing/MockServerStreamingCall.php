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

namespace Google\GAX\Testing;

use Google\GAX\ApiException;
use google\rpc\Code;
use google\rpc\Status;
use Grpc;

/**
 * The MockServerStreamingCall class is used to mock out the \Grpc\ServerStreamingCall class
 * (https://github.com/grpc/grpc/blob/master/src/php/lib/Grpc/ServerStreamingCall.php)
 */
class MockServerStreamingCall
{
    private $responses;
    private $deserialize;
    private $status;

    /**
     * MockServerStreamingCall constructor.
     * @param mixed[] $responses A list of response objects.
     * @param callable|null $deserialize An optional deserialize method for the response object.
     * @param Status|null $status An optional status object. If set to null, a status of OK is used.
     */
    public function __construct($responses, $deserialize = null, $status = null)
    {
        $this->responses = $responses;
        if (is_null($deserialize)) {
            $deserialize = function ($resp) {
                return $resp;
            };
        }
        $this->deserialize = $deserialize;
        if (is_null($status)) {
            $status = new Status();
            $status->setCode(Code::OK);
        }
        $this->status = $status;
    }

    public function responses()
    {
        while (count($this->responses) > 0) {
            $resp = array_shift($this->responses);
            yield call_user_func($this->deserialize, $resp);
        }
    }

    public function getStatus()
    {
        if (count($this->responses) > 0) {
            throw new ApiException(
                "Calls to getStatus() will block if all responses are not read",
                Grpc\STATUS_INTERNAL
            );
        }
        return $this->status;
    }
}
