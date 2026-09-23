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

namespace Google\GAX;

use Exception;
use Grpc;
use InvalidArgumentException;

/**
 * Holds constants necessary for interacting with gRPC.
 */
class GrpcConstants
{
    private static $statusCodeNames;
    private static $statusCodesToNames;

    /**
     * This should not be called outside of the implementation file.
     */
    private static function initStatusCodeNames()
    {
        if (!empty(self::$statusCodeNames)) {
            throw new Exception("GrpcConstants::initStatusCodeNames called more than once");
        }
        self::$statusCodeNames = [
            'ABORTED' => Grpc\STATUS_ABORTED,
            'ALREADY_EXISTS' => Grpc\STATUS_ALREADY_EXISTS,
            'CANCELLED' => Grpc\STATUS_CANCELLED,
            'DATA_LOSS' => Grpc\STATUS_DATA_LOSS,
            'DEADLINE_EXCEEDED' => Grpc\STATUS_DEADLINE_EXCEEDED,
            'FAILED_PRECONDITION' => Grpc\STATUS_FAILED_PRECONDITION,
            'INTERNAL' => Grpc\STATUS_INTERNAL,
            'INVALID_ARGUMENT' => Grpc\STATUS_INVALID_ARGUMENT,
            'NOT_FOUND' => Grpc\STATUS_NOT_FOUND,
            'OK' => Grpc\STATUS_OK,
            'OUT_OF_RANGE' => Grpc\STATUS_OUT_OF_RANGE,
            'PERMISSION_DENIED' => Grpc\STATUS_PERMISSION_DENIED,
            'RESOURCE_EXHAUSTED' => Grpc\STATUS_RESOURCE_EXHAUSTED,
            'UNAUTHENTICATED' => Grpc\STATUS_UNAUTHENTICATED,
            'UNAVAILABLE' => Grpc\STATUS_UNAVAILABLE,
            'UNIMPLEMENTED' => Grpc\STATUS_UNIMPLEMENTED,
            'UNKNOWN' => Grpc\STATUS_UNKNOWN
        ];
        self::$statusCodesToNames = array_flip(self::$statusCodeNames);
    }

    /**
     * Provides an array that maps from status code name to an object
     * representing that status code.
     */
    public static function getStatusCodeNames()
    {
        if (!self::$statusCodeNames) {
            self::initStatusCodeNames();
        }
        return self::$statusCodeNames;
    }

    /**
     * Provides an array that maps from status codes to status names.
     * @return array
     */
    public static function getStatusCodesToNamesMap()
    {
        if (!self::$statusCodesToNames) {
            self::initStatusCodeNames();
        }
        return self::$statusCodesToNames;
    }

    /**
     * @param string $name
     * @return int
     */
    public static function getCodeFromStatusName($name)
    {
        $codeNames = self::getStatusCodeNames();
        if (!isset($codeNames[$name])) {
            return -1;
        }
        return $codeNames[$name];
    }

    /**
     * @param int $code
     * @return string
     */
    public static function getStatusNameFromCode($code)
    {
        $codeNames = self::getStatusCodesToNamesMap();
        if (!isset($codeNames[$code])) {
            return "UNEXPECTED_CODE";
        }
        return $codeNames[$code];
    }
}
