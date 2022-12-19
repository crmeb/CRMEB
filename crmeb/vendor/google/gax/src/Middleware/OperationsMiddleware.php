<?php
/*
 * Copyright 2018 Google LLC
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
namespace Google\ApiCore\Middleware;

use Google\ApiCore\Call;
use Google\ApiCore\OperationResponse;
use Google\Protobuf\Internal\Message;

/**
 * Middleware which wraps the response in an OperationResponse object.
 */
class OperationsMiddleware
{
    /** @var callable */
    private $nextHandler;

    /** @var object */
    private $operationsClient;

    /** @var array */
    private $descriptor;

    public function __construct(
        callable $nextHandler,
        $operationsClient,
        array $descriptor
    ) {
        $this->nextHandler = $nextHandler;
        $this->operationsClient = $operationsClient;
        $this->descriptor = $descriptor;
    }

    public function __invoke(Call $call, array $options)
    {
        $next = $this->nextHandler;
        return $next(
            $call,
            $options
        )->then(function (Message $response) {
            $options = $this->descriptor + [
                'lastProtoResponse' => $response
            ];
            $operationNameMethod = isset($options['operationNameMethod'])
                ? $options['operationNameMethod'] : 'getName';
            $operationName = call_user_func([$response, $operationNameMethod]);
            return new OperationResponse($operationName, $this->operationsClient, $options);
        });
    }
}
