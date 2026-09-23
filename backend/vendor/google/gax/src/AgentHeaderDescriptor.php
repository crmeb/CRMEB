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

/**
 * Encapsulates the custom GAPIC header information.
 */
class AgentHeaderDescriptor
{
    const AGENT_HEADER_KEY = 'x-goog-api-client';
    // TODO(michaelbausor): include bumping this version in a streamlined
    // release process. Issue: https://github.com/googleapis/gax-php/issues/48
    const GAX_VERSION = '0.10.0';
    const UNKNOWN_VERSION = '';

    private $metricsHeaders;

    /**
     * @param array $headerInfo {
     *     Optional.
     *
     *     @type string $phpVersion the PHP version.
     *     @type string $libName the name of the client application.
     *     @type string $libVersion the version of the client application.
     *     @type string $gapicVersion the code generator version of the GAPIC library.
     *     @type string $gaxVersion the GAX version.
     *     @type string $grpcVersion the gRPC version.
     * }
     */
    public function __construct($headerInfo)
    {
        $metricsHeaders = [];

        // The ordering of the headers is important. We use the fact that $metricsHeaders is an
        // ordered dict. The desired ordering is:
        //      - phpVersion (gl-php/)
        //      - clientName (e.g. gccl/)
        //      - gapicVersion (gapic/)
        //      - gaxVersion (gax/)
        //      - grpcVersion (grpc/)

        $phpVersion = isset($headerInfo['phpVersion'])
            ? $headerInfo['phpVersion']
            : phpversion();
        $metricsHeaders['gl-php'] = $phpVersion;

        if (isset($headerInfo['libName'])) {
            $clientVersion = isset($headerInfo['libVersion'])
                ? $headerInfo['libVersion']
                : AgentHeaderDescriptor::UNKNOWN_VERSION;
            $metricsHeaders[$headerInfo['libName']] = $clientVersion;
        }

        $codeGenVersion = isset($headerInfo['gapicVersion'])
            ? $headerInfo['gapicVersion']
            : AgentHeaderDescriptor::UNKNOWN_VERSION;
        $metricsHeaders['gapic'] = $codeGenVersion;

        $gaxVersion = isset($headerInfo['gaxVersion'])
            ? $headerInfo['gaxVersion']
            : AgentHeaderDescriptor::GAX_VERSION;
        $metricsHeaders['gax'] = $gaxVersion;

        $grpcVersion = isset($headerInfo['grpcVersion'])
            ? $headerInfo['grpcVersion']
            : phpversion('grpc');
        $metricsHeaders['grpc'] = $grpcVersion;

        $this->metricsHeaders = $metricsHeaders;
    }

    /**
     * Returns an associative array that contains GAPIC header metadata.
     */
    public function getHeader()
    {
        $metricsList = [];
        foreach ($this->metricsHeaders as $key => $value) {
            $metricsList[] = $key . "/" . $value;
        }
        return [self::AGENT_HEADER_KEY => [implode(" ", $metricsList)]];
    }

    /**
     * Returns the version string for GAX.
     */
    public static function getGaxVersion()
    {
        return self::GAX_VERSION;
    }
}
