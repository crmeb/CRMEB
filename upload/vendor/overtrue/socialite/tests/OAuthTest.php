<?php

/*
 * This file is part of the overtrue/socialite.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Mockery as m;
use Overtrue\Socialite\AccessTokenInterface;
use Overtrue\Socialite\Providers\AbstractProvider;
use Overtrue\Socialite\User;
use Symfony\Component\HttpFoundation\Request;

class OAuthTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testRedirectGeneratesTheProperSymfonyRedirectResponse()
    {
        $request = Request::create('foo');
        $request->setSession($session = m::mock('Symfony\Component\HttpFoundation\Session\SessionInterface'));
        $session->shouldReceive('put')->once();
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $response = $provider->redirect();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame('http://auth.url', $response->getTargetUrl());
    }

    public function testRedirectUrl()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('A', 40), 'code' => 'code']);
        $request->setSession($session = m::mock('Symfony\Component\HttpFoundation\Session\SessionInterface'));

        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret');
        $this->assertNull($provider->getRedirectUrl());

        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect_uri');
        $this->assertSame('redirect_uri', $provider->getRedirectUrl());
        $provider->setRedirectUrl('overtrue.me');
        $this->assertSame('overtrue.me', $provider->getRedirectUrl());

        $provider->withRedirectUrl('http://overtrue.me');
        $this->assertSame('http://overtrue.me', $provider->getRedirectUrl());
    }

    public function testUserReturnsAUserInstanceForTheAuthenticatedRequest()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('A', 40), 'code' => 'code']);
        $request->setSession($session = m::mock('Symfony\Component\HttpFoundation\Session\SessionInterface'));

        $session->shouldReceive('get')->once()->with('state')->andReturn(str_repeat('A', 40));
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect_uri');
        $provider->http = m::mock('StdClass');
        $provider->http->shouldReceive('post')->once()->with('http://token.url', [
            'headers' => ['Accept' => 'application/json'], 'form_params' => ['client_id' => 'client_id', 'client_secret' => 'client_secret', 'code' => 'code', 'redirect_uri' => 'redirect_uri'],
        ])->andReturn($response = m::mock('StdClass'));
        $response->shouldReceive('getBody')->once()->andReturn('{"access_token":"access_token"}');
        $user = $provider->user();

        $this->assertInstanceOf('Overtrue\Socialite\User', $user);
        $this->assertSame('foo', $user->getId());
    }

    /**
     * @expectedException \Overtrue\Socialite\InvalidStateException
     */
    public function testExceptionIsThrownIfStateIsInvalid()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('B', 40), 'code' => 'code']);
        $request->setSession($session = m::mock('Symfony\Component\HttpFoundation\Session\SessionInterface'));
        $session->shouldReceive('get')->once()->with('state')->andReturn(str_repeat('A', 40));
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $user = $provider->user();
    }

    /**
     * @expectedException \Overtrue\Socialite\AuthorizeFailedException
     * @expectedExceptionMessage Authorize Failed: {"error":"scope is invalid"}
     */
    public function testExceptionisThrownIfAuthorizeFailed()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('A', 40), 'code' => 'code']);
        $request->setSession($session = m::mock('Symfony\Component\HttpFoundation\Session\SessionInterface'));
        $session->shouldReceive('get')->once()->with('state')->andReturn(str_repeat('A', 40));
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect_uri');
        $provider->http = m::mock('StdClass');
        $provider->http->shouldReceive('post')->once()->with('http://token.url', [
            'headers' => ['Accept' => 'application/json'], 'form_params' => ['client_id' => 'client_id', 'client_secret' => 'client_secret', 'code' => 'code', 'redirect_uri' => 'redirect_uri'],
        ])->andReturn($response = m::mock('StdClass'));
        $response->shouldReceive('getBody')->once()->andReturn('{"error":"scope is invalid"}');
        $user = $provider->user();
    }

    /**
     * @expectedException \Overtrue\Socialite\InvalidStateException
     */
    public function testExceptionIsThrownIfStateIsNotSet()
    {
        $request = Request::create('foo', 'GET', ['state' => 'state', 'code' => 'code']);
        $request->setSession($session = m::mock('Symfony\Component\HttpFoundation\Session\SessionInterface'));
        $session->shouldReceive('get')->once()->with('state');
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $user = $provider->user();
    }

    public function testDriverName()
    {
        $request = Request::create('foo', 'GET', ['state' => 'state', 'code' => 'code']);
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect');

        $this->assertSame('OAuthTwoTest', $provider->getName());
    }
}

class OAuthTwoTestProviderStub extends AbstractProvider
{
    public $http;

    protected function getAuthUrl($state)
    {
        return 'http://auth.url';
    }

    protected function getTokenUrl()
    {
        return 'http://token.url';
    }

    protected function getUserByToken(AccessTokenInterface $token)
    {
        return ['id' => 'foo'];
    }

    protected function mapUserToObject(array $user)
    {
        return new User(['id' => $user['id']]);
    }

    /**
     * Get a fresh instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        if ($this->http) {
            return $this->http;
        }

        return $this->http = m::mock('StdClass');
    }
}
