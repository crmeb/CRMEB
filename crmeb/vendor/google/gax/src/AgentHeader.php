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

namespace Google\ApiCore;

/**
 * Class containing functions used to build the Agent header.
 */
class AgentHeader
{
    const AGENT_HEADER_KEY = 'x-goog-api-client';
    const UNKNOWN_VERSION = '';

    /**
     * @param array $headerInfo {
     *     Optional.
     *
     *     @type string $phpVersion the PHP version.
     *     @type string $libName the name of the client application.
     *     @type string $libVersion the version of the client application.
     *     @type string $gapicVersion the code generator version of the GAPIC library.
     *     @type string $apiCoreVersion the ApiCore version.
     *     @type string $grpcVersion the gRPC version.
     *     @type string $restVersion the REST transport version (typically same as the
     *           ApiCore version).
     *     @type string $protobufVersion the protobuf version in format 'x.y.z+a' where both 'x.y.z'
     *           and '+a' are optional, and where 'a' is a single letter representing the
     *           implementation type of the protobuf runtime. It is recommended to use 'c' for a C
     *           implementation, and 'n' for the native language implementation (PHP).
     * }
     * @return array Agent header array
     */
    public static function buildAgentHeader(array $headerInfo)
    {
        $metricsHeaders = [];

        // The ordering of the headers is important. We use the fact that $metricsHeaders is an
        // ordered dict. The desired ordering is:
        //      - phpVersion (gl-php/)
        //      - clientName (e.g. gccl/)
        //      - gapicVersion (gapic/)
        //      - apiCoreVersion (gax/)
        //      - grpcVersion (grpc/)
        //      - restVersion (rest/)
        //      - protobufVersion (pb/)

        $phpVersion = isset($headerInfo['phpVersion'])
            ? $headerInfo['phpVersion']
            : phpversion();
        $metricsHeaders['gl-php'] = $phpVersion;

        if (isset($headerInfo['libName'])) {
            $clientVersion = isset($headerInfo['libVersion'])
                ? $headerInfo['libVersion']
                : self::UNKNOWN_VERSION;
            $metricsHeaders[$headerInfo['libName']] = $clientVersion;
        }

        $codeGenVersion = isset($headerInfo['gapicVersion'])
            ? $headerInfo['gapicVersion']
            : self::UNKNOWN_VERSION;
        $metricsHeaders['gapic'] = $codeGenVersion;

        $apiCoreVersion = isset($headerInfo['apiCoreVersion'])
            ? $headerInfo['apiCoreVersion']
            : Version::getApiCoreVersion();
        $metricsHeaders['gax'] = $apiCoreVersion;

        // Context on library type identification (between gRPC+REST and REST-only):
        // This uses the gRPC extension's version if 'grpcVersion' is not set, so we
        // cannot use the presence of 'grpcVersion' to determine whether or not a library
        // is gRPC+REST or REST-only. However, we cannot use the extension's presence
        // either, since some clients may have the extension installed but opt to use a
        // REST-only library (e.g. GCE).
        // TODO: Should we stop sending empty gRPC headers?
        $grpcVersion = isset($headerInfo['grpcVersion'])
            ? $headerInfo['grpcVersion']
            : phpversion('grpc');
        $metricsHeaders['grpc'] = $grpcVersion;

        $restVersion = isset($headerInfo['restVersion'])
            ? $headerInfo['restVersion']
            : $apiCoreVersion;
        $metricsHeaders['rest'] = $restVersion;

        // The native version is not set by default because it is complex and costly to retrieve.
        // Users can override this default behavior if needed.
        $protobufVersion = isset($headerInfo['protobufVersion'])
            ? $headerInfo['protobufVersion']
            : (phpversion('protobuf') ? phpversion('protobuf') . '+c' : '+n');
        $metricsHeaders['pb'] = $protobufVersion;

        $metricsList = [];
        foreach ($metricsHeaders as $key => $value) {
            $metricsList[] = $key . "/" . $value;
        }
        return [self::AGENT_HEADER_KEY => [implode(" ", $metricsList)]];
    }

    /**
     * Reads the gapic version string from a VERSION file. In order to determine the file
     * location, this method follows this procedure:
     * - accepts a class name $callingClass
     * - identifies the file defining that class
     * - searches up the directory structure for the 'src' directory
     * - looks in the directory above 'src' for a file named VERSION
     *
     * @param string $callingClass
     * @return string the gapic version
     * @throws \ReflectionException
     */
    public static function readGapicVersionFromFile(string $callingClass)
    {
        $callingClassFile = (new \ReflectionClass($callingClass))->getFileName();
        $versionFile = substr(
            $callingClassFile,
            0,
            strrpos($callingClassFile, DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR)
        ) . DIRECTORY_SEPARATOR . 'VERSION';

        return Version::readVersionFile($versionFile);
    }
}
