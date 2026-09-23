<?php

namespace think\tests;

use PHPUnit\Framework\TestCase;
use Mockery as m;
use think\Cookie;
use think\response\Html;
use think\response\Json;

class ResponseTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testHtmlResponseCreation()
    {
        $cookie = m::mock(Cookie::class);
        $response = new Html($cookie, 'test content');
        $this->assertInstanceOf(Html::class, $response);
        $this->assertEquals('test content', $response->getData());
    }

    public function testJsonResponseCreation()
    {
        $cookie = m::mock(Cookie::class);
        $data = ['key' => 'value'];
        $response = new Json($cookie, $data);
        $this->assertInstanceOf(Json::class, $response);
        $this->assertEquals($data, $response->getData());
    }

    public function testResponseCode()
    {
        $cookie = m::mock(Cookie::class);
        $response = new Html($cookie, 'test', 200);
        $this->assertEquals(200, $response->getCode());
        
        $response->code(404);
        $this->assertEquals(404, $response->getCode());
    }

    public function testResponseHeaders()
    {
        $cookie = m::mock(Cookie::class);
        $response = new Html($cookie, 'test');
        
        $response->header(['Content-Type' => 'text/html']);
        $headers = $response->getHeader();
        $this->assertEquals('text/html', $headers['Content-Type']);
        
        $response->header(['X-Custom' => 'value']);
        $headers = $response->getHeader();
        $this->assertEquals('value', $headers['X-Custom']);
    }

    public function testResponseData()
    {
        $cookie = m::mock(Cookie::class);
        $response = new Html($cookie, 'initial');
        $this->assertEquals('initial', $response->getData());
        
        $response->data('updated');
        $this->assertEquals('updated', $response->getData());
    }

    public function testResponseStatusMethods()
    {
        $cookie = m::mock(Cookie::class);
        $response = new Html($cookie, '', 200);
        $this->assertEquals(200, $response->getCode());
        
        $response->code(404);
        $this->assertEquals(404, $response->getCode());
        
        $response->code(500);
        $this->assertEquals(500, $response->getCode());
    }

    public function testContentTypeMethod()
    {
        $cookie = m::mock(Cookie::class);
        $response = new Html($cookie, 'test');
        
        $response->contentType('application/json', 'utf-8');
        $headers = $response->getHeader();
        $this->assertEquals('application/json; charset=utf-8', $headers['Content-Type']);
    }

    public function testLastModified()
    {
        $cookie = m::mock(Cookie::class);
        $response = new Html($cookie, 'test');
        $time = '2025-01-01 10:00:00';
        
        $response->lastModified($time);
        $headers = $response->getHeader();
        $this->assertArrayHasKey('Last-Modified', $headers);
    }

    public function testETag()
    {
        $cookie = m::mock(Cookie::class);
        $response = new Html($cookie, 'test');
        $etag = 'test-etag';
        
        $response->eTag($etag);
        $headers = $response->getHeader();
        $this->assertEquals('test-etag', $headers['ETag']);
    }
}