<?php
/*
 * Copyright 2016 Google LLC
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

use Google\Rpc\Code;
use Google\Protobuf\Internal\Message;
use stdClass;

/**
 * The MockUnaryCall class is used to mock out the \Grpc\UnaryCall class
 * (https://github.com/grpc/grpc/blob/master/src/php/lib/Grpc/UnaryCall.php)
 *
 * The MockUnaryCall object is constructed with a response object, an optional deserialize
 * method, and an optional status. The response object and status are returned immediately from the
 * wait() method.
 *
 * @internal
 */
class MockUnaryCall extends \Grpc\UnaryCall
{
    use SerializationTrait;

    private $response;
    private $status;

    /**
     * MockUnaryCall constructor.
     * @param Message|string|null $response The response object.
     * @param callable|array|null $deserialize An optional deserialize method for the response object.
     * @param stdClass|null $status An optional status object. If set to null, a status of OK is used.
     */
    public function __construct($response = null, $deserialize = null, stdClass $status = null)
    {
        $this->response = $response;
        $this->deserialize = $deserialize;
        if (is_null($status)) {
            $status = new MockStatus(Code::OK);
        }
        $this->status = $status;
    }

    /**
     * Immediately return the preset response object and status.
     * @return array The response object and status.
     */
    public function wait()
    {
        return [
            $this->deserializeMessage($this->response, $this->deserialize),
            $this->status,
        ];
    }
}
