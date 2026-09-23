<?php

namespace AlibabaCloud\Endpoint\Tests;

use AlibabaCloud\Endpoint\Endpoint;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class EndpointTest extends TestCase
{
    public function testGetEndpointWhenInvalidProduct()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name cannot be empty.');
        Endpoint::getEndpointRules('', '', '', '');
    }

    public function testGetEndpointWhenInvalidEndpointType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid EndpointType');
        Endpoint::getEndpointRules('ecs', '', 'fake endpoint type', '');
    }

    public function testGetEndpointWhenInvalidRegionId()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('RegionId is empty, please set a valid RegionId');
        Endpoint::getEndpointRules('ecs', '', Endpoint::ENDPOINT_TYPE_REGIONAL, '');
    }

    public function testGetEndpointCentral()
    {
        $endpoint = Endpoint::getEndpointRules('ecs', '', Endpoint::ENDPOINT_TYPE_CENTRAL);
        $this->assertEquals('ecs.aliyuncs.com', $endpoint);
    }

    public function testGetEndpointRegional()
    {
        $endpoint = Endpoint::getEndpointRules('ecs', 'cn-hangzhou', Endpoint::ENDPOINT_TYPE_REGIONAL);
        $this->assertEquals('ecs.cn-hangzhou.aliyuncs.com', $endpoint);
    }

    public function testGetEndpointRegionalWithNetwork()
    {
        $endpoint = Endpoint::getEndpointRules('ecs', 'cn-hangzhou', Endpoint::ENDPOINT_TYPE_REGIONAL, 'internal');
        $this->assertEquals('ecs-internal.cn-hangzhou.aliyuncs.com', $endpoint);
    }

    public function testGetEndpointRegionalWithSuffix()
    {
        $endpoint = Endpoint::getEndpointRules('ecs', 'cn-hangzhou', Endpoint::ENDPOINT_TYPE_REGIONAL, 'internal', 'test');
        $this->assertEquals('ecs-test-internal.cn-hangzhou.aliyuncs.com', $endpoint);
    }
}
