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

use PHPUnit_Framework_TestCase;
use Google\GAX\UnitTests\Mocks\MockGrpcCredentialsHelper;
use Google\GAX\UnitTests\Mocks\MockCredentialsLoader;

class GrpcCredentialsHelperTest extends PHPUnit_Framework_TestCase
{
    private $defaultScope = ['my-scope'];
    private $defaultTokens = [
        [
            'access_token' => 'accessToken',
            'expires_in' => '100',
        ],
        [
            'access_token' => 'accessToken2',
            'expires_in' => '100'
        ],
    ];

    public function testCreateCallCredentialsCallbackDefault()
    {
        $grpcCredentialsHelper = new MockGrpcCredentialsHelper($this->defaultScope);
        $callback = $grpcCredentialsHelper->createCallCredentialsCallback();
        $callbackResult = $callback();
        $this->assertEquals(['Bearer adcAccessToken'], $callbackResult['authorization']);
    }

    public function testCreateCallCredentialsCallbackCustomCredsLoader()
    {
        $grpcCredentialsHelper = new MockGrpcCredentialsHelper(
            $this->defaultScope,
            ['credentialsLoader' => new MockCredentialsLoader(
                $this->defaultScope,
                $this->defaultTokens
            )]
        );
        $callback = $grpcCredentialsHelper->createCallCredentialsCallback();
        $callbackResult = $callback();
        $this->assertEquals(['Bearer accessToken'], $callbackResult['authorization']);
    }

    public function testCreateCallCredentialsCallbackDisableCaching()
    {
        $grpcCredentialsHelper = new MockGrpcCredentialsHelper(
            $this->defaultScope,
            ['credentialsLoader' => new MockCredentialsLoader(
                $this->defaultScope,
                $this->defaultTokens
            ),
            'enableCaching' => false]
        );
        $callback = $grpcCredentialsHelper->createCallCredentialsCallback();
        $callbackResult = $callback();
        $this->assertEquals(['Bearer accessToken'], $callbackResult['authorization']);
        $callback = $grpcCredentialsHelper->createCallCredentialsCallback();
        $callbackResult = $callback();
        $this->assertEquals(['Bearer accessToken2'], $callbackResult['authorization']);
    }

    public function testCreateCallCredentialsCallbackCaching()
    {
        $grpcCredentialsHelper = new MockGrpcCredentialsHelper(
            $this->defaultScope,
            ['credentialsLoader' => new MockCredentialsLoader(
                $this->defaultScope,
                $this->defaultTokens
            )]
        );
        $callback = $grpcCredentialsHelper->createCallCredentialsCallback();
        $callbackResult = $callback();
        $this->assertEquals(['Bearer accessToken'], $callbackResult['authorization']);
        $callback = $grpcCredentialsHelper->createCallCredentialsCallback();
        $callbackResult = $callback();
        $this->assertEquals(['Bearer accessToken'], $callbackResult['authorization']);
    }

    public function testCreateStubWithDefaultSslCreds()
    {
        $grpcCredentialsHelper = new MockGrpcCredentialsHelper($this->defaultScope);
        $createStubCallback = function ($hostname, $stubOpts) {
            return ['hostname' => $hostname, 'stubOpts' => $stubOpts];
        };
        $createStubResult = $grpcCredentialsHelper->createStub(
            $createStubCallback,
            'my-service-address',
            8443
        );
        $this->assertEquals('my-service-address:8443', $createStubResult['hostname']);
        $this->assertEquals('DummySslCreds', $createStubResult['stubOpts']['credentials']);
        $this->assertEquals(
            'my-service-address:8443',
            $createStubResult['stubOpts']['grpc.ssl_target_name_override']
        );
    }

    public function testCreateStubWithExplicitSslCreds()
    {
        $grpcCredentialsHelper = new MockGrpcCredentialsHelper($this->defaultScope);
        $createStubCallback = function ($hostname, $stubOpts) {
            return ['hostname' => $hostname, 'stubOpts' => $stubOpts];
        };
        $createStubResult = $grpcCredentialsHelper->createStub(
            $createStubCallback,
            'my-service-address',
            8443,
            ['sslCreds' => 'provided-creds']
        );
        $this->assertEquals('my-service-address:8443', $createStubResult['hostname']);
        $this->assertEquals('provided-creds', $createStubResult['stubOpts']['credentials']);
        $this->assertEquals(
            'my-service-address:8443',
            $createStubResult['stubOpts']['grpc.ssl_target_name_override']
        );
    }

    public function testCreateStubWithInsecureSslCreds()
    {
        $grpcCredentialsHelper = new MockGrpcCredentialsHelper($this->defaultScope);
        $createStubCallback = function ($hostname, $stubOpts) {
            return ['hostname' => $hostname, 'stubOpts' => $stubOpts];
        };
        $insecureCreds = \Grpc\ChannelCredentials::createInsecure();
        $createStubResult = $grpcCredentialsHelper->createStub(
            $createStubCallback,
            'my-service-address',
            8443,
            ['sslCreds' => $insecureCreds]
        );
        $this->assertEquals('my-service-address:8443', $createStubResult['hostname']);
        $this->assertEquals($insecureCreds, $createStubResult['stubOpts']['credentials']);
        $this->assertEquals(
            'my-service-address:8443',
            $createStubResult['stubOpts']['grpc.ssl_target_name_override']
        );
    }
}
