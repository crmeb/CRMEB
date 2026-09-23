<?php

namespace Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Overtrue\Socialite\Exceptions\FeiShu\InvalidTicketException;
use Overtrue\Socialite\Exceptions\InvalidTokenException;
use Overtrue\Socialite\Providers\FeiShu;
use PHPUnit\Framework\TestCase;

class FeiShuTest extends TestCase
{
    public function test_provider_can_create_correct()
    {
        // one way
        $config = [
            'app_id' => 'xxxxx',
            'app_secret' => 'yyyyy',
            'app_mode' => 'internal',
        ];
        $f = new FeiShu($config);
        $rf = new \ReflectionObject($f);

        $this->assertEquals('xxxxx', $f->getClientId());
        $this->assertEquals('yyyyy', $f->getClientSecret());

        $rfProperty = $rf->getProperty('isInternalApp');
        $rfProperty->setAccessible(true);
        $this->assertEquals(true, $rfProperty->getValue($f));

        // diff filed way
        $config = [
            'client_id' => 'xxxxx',
            'client_secret' => 'yyyyy',
            'mode' => 'internal',
        ];

        $f = new FeiShu($config);
        $rf = new \ReflectionObject($f);

        $this->assertEquals('xxxxx', $f->getClientId());
        $this->assertEquals('yyyyy', $f->getClientSecret());
        $rfProperty = $rf->getProperty('isInternalApp');
        $rfProperty->setAccessible(true);
        $this->assertEquals(true, $rfProperty->getValue($f));

        // no mode config way
        $config = [
            'client_id' => 'xxxxx',
            'client_secret' => 'yyyyy',
        ];

        $f = new FeiShu($config);
        $rf = new \ReflectionObject($f);

        $this->assertEquals('xxxxx', $f->getClientId());
        $this->assertEquals('yyyyy', $f->getClientSecret());
        $rfProperty = $rf->getProperty('isInternalApp');
        $rfProperty->setAccessible(true);
        $this->assertEquals(false, $rfProperty->getValue($f));
    }

    public function test_provider_with_internal_app_mode_work()
    {
        $config = [
            'client_id' => 'xxxxx',
            'client_secret' => 'yyyyy',
        ];

        $f = new FeiShu($config);
        $rf = new \ReflectionObject($f);

        $rfProperty = $rf->getProperty('isInternalApp');
        $rfProperty->setAccessible(true);

        $f->withInternalAppMode();
        $this->assertEquals(true, $rfProperty->getValue($f));

        $f->withDefaultMode();
        $this->assertEquals(false, $rfProperty->getValue($f));
    }

    public function test_provider_with_app_ticket_work()
    {
        $config = [
            'client_id' => 'xxxxx',
            'client_secret' => 'yyyyy',
        ];

        $f = new FeiShu($config);
        $f->withAppTicket('app_ticket');
        $this->assertEquals('app_ticket', $f->getConfig()->get('app_ticket'));
    }

    public function test_provider_with_app_access_token_work()
    {
        $config = [
            'client_id' => 'xxxxx',
            'client_secret' => 'yyyyy',
        ];

        $f = new FeiShu($config);
        $f->withAppAccessToken('my_app_access_token');
        $this->assertEquals('my_app_access_token', $f->getConfig()->get('app_access_token'));
    }

    public function test_config_app_access_token_skips_api_call_when_token_already_set()
    {
        $config = [
            'client_id' => 'xxxxx',
            'client_secret' => 'yyyyy',
            'mode' => 'internal',
        ];

        $f = new FeiShu($config);
        $f->withAppAccessToken('pre_fetched_token');

        $fr = new \ReflectionObject($f);
        $frClient = $fr->getProperty('httpClient');
        $frClient->setAccessible(true);
        $ff = new \ReflectionMethod(FeiShu::class, 'configAppAccessToken');
        $ff->setAccessible(true);

        // No HTTP responses needed - the API call should be skipped
        $mock = new MockHandler([]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $frClient->setValue($f, $client);

        $ff->invoke($f);
        // Token should remain unchanged
        $this->assertEquals('pre_fetched_token', $f->getConfig()->get('app_access_token'));
    }

    public function test_config_app_access_token_with_default_mode_no_app_ticket_work()
    {
        $config = [
            'client_id' => 'xxxxx',
            'client_secret' => 'yyyyy',
        ];

        $f = new FeiShu($config);
        $fr = new \ReflectionObject($f);
        $frClient = $fr->getProperty('httpClient');
        $frClient->setAccessible(true);
        $ff = new \ReflectionMethod(FeiShu::class, 'configAppAccessToken');

        $mock = new MockHandler([
            new Response(403, []),
            new Response(200, [], \json_encode([
                'app_access_token' => 'app_access_token',
            ])),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $frClient->setValue($f, $client);
        $ff->setAccessible(true);

        // 默认模式下没有 app_ticket
        $this->expectException(InvalidTicketException::class);
        $ff->invoke($f);

        $ff->invoke($f);
        $f->withAppTicket('app_ticket');
        $this->assertEquals('app_access_token', $f->getConfig()->get('app_access_token'));

        $this->expectException(InvalidTokenException::class);
        $ff->invoke($f);
    }

    public function test_config_app_access_token_with_default_mode_and_app_ticket_work_in_bad_response()
    {
        $config = [
            'client_id' => 'xxxxx',
            'client_secret' => 'yyyyy',
        ];

        $f = new FeiShu($config);
        $fr = new \ReflectionObject($f);
        $frClient = $fr->getProperty('httpClient');
        $frClient->setAccessible(true);
        $ff = new \ReflectionMethod(FeiShu::class, 'configAppAccessToken');

        $mock = new MockHandler([
            new Response(200, [], '{}'),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $frClient->setValue($f, $client);
        $ff->setAccessible(true);

        $this->expectException(InvalidTokenException::class);
        $ff->invoke($f->withAppTicket('app_ticket'));
    }

    public function test_config_app_access_token_with_default_mode_and_app_ticket_work_in_good_response()
    {
        $config = [
            'client_id' => 'xxxxx',
            'client_secret' => 'yyyyy',
        ];

        $f = new FeiShu($config);
        $fr = new \ReflectionObject($f);
        $frClient = $fr->getProperty('httpClient');
        $frClient->setAccessible(true);
        $ff = new \ReflectionMethod(FeiShu::class, 'configAppAccessToken');

        $mock = new MockHandler([
            new Response(200, [], \json_encode([
                'app_access_token' => 'app_access_token',
            ])),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $frClient->setValue($f, $client);
        $ff->setAccessible(true);

        $this->assertEquals(null, $f->getConfig()->get('app_access_token'));
        $ff->invoke($f->withAppTicket('app_ticket'));
        $this->assertEquals('app_access_token', $f->getConfig()->get('app_access_token'));
    }

    public function test_config_app_access_token_with_internal_in_bad_response()
    {
        $config = [
            'client_id' => 'xxxxx',
            'client_secret' => 'yyyyy',
            'mode' => 'internal',
        ];

        $f = new FeiShu($config);
        $fr = new \ReflectionObject($f);
        $frClient = $fr->getProperty('httpClient');
        $frClient->setAccessible(true);
        $ff = new \ReflectionMethod(FeiShu::class, 'configAppAccessToken');

        $mock = new MockHandler([
            new Response(200, [], '{}'),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $frClient->setValue($f, $client);
        $ff->setAccessible(true);

        $this->expectException(InvalidTokenException::class);
        $ff->invoke($f);
    }

    public function test_config_app_access_token_with_internal_in_good_response()
    {
        $config = [
            'client_id' => 'xxxxx',
            'client_secret' => 'yyyyy',
            'mode' => 'internal',
        ];

        $f = new FeiShu($config);
        $fr = new \ReflectionObject($f);
        $frClient = $fr->getProperty('httpClient');
        $frClient->setAccessible(true);
        $ff = new \ReflectionMethod(FeiShu::class, 'configAppAccessToken');

        $mock = new MockHandler([
            new Response(200, [], \json_encode([
                'app_access_token' => 'app_access_token',
            ])),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $frClient->setValue($f, $client);
        $ff->setAccessible(true);

        $this->assertEquals(null, $f->getConfig()->get('app_access_token'));
        $ff->invoke($f);
        $this->assertEquals('app_access_token', $f->getConfig()->get('app_access_token'));
    }
}
