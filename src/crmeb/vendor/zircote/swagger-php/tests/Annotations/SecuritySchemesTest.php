<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests\Annotations;

use OpenApi\Annotations\Info;
use OpenApi\Annotations\SecurityScheme;
use OpenApi\Annotations\Server;
use OpenApi\Tests\OpenApiTestCase;

/**
 * Class SecuritySchemesTest.
 *
 * Security openapi test
 */
class SecuritySchemesTest extends OpenApiTestCase
{
    /**
     * Test parse servers.
     */
    public function testParseServers()
    {
        $comment = <<<INFO
/**
 * @OA\Info(
 *     title="Simple api",
 *     description="Simple api description",
 * )
 * @OA\Server(
 *     url="http://example.com",
 *     description="First host"
 * )
 * @OA\Server(
 *     url="http://example-second.com",
 *     description="Second host"
 * )
 */

INFO;
        $analysis = $this->analysisFromDockBlock($comment);

        $this->assertCount(3, $analysis);
        $this->assertInstanceOf(Info::class, $analysis[0]);
        $this->assertInstanceOf(Server::class, $analysis[1]);
        $this->assertInstanceOf(Server::class, $analysis[2]);

        $this->assertEquals('http://example.com', $analysis[1]->url);
        $this->assertEquals('First host', $analysis[1]->description);

        $this->assertEquals('http://example-second.com', $analysis[2]->url);
        $this->assertEquals('Second host', $analysis[2]->description);
    }

    /**
     * Test parse security scheme.
     */
    public function testImplicitFlowAnnotation()
    {
        $comment = <<<SCHEME
/**
 * @OA\SecurityScheme(
 *     @OA\Flow(
 *         flow="implicit",
 *         tokenUrl="http://auth.test.com/token",
 *         refreshUrl="http://auth.test.com/refresh-token"
 *     ),
 *     securityScheme="oauth2",
 *     in="header",
 *     type="oauth2",
 *     description="Oauth2 security",
 *     name="oauth2",
 *     scheme="https",
 *     bearerFormat="bearer",
 *     openIdConnectUrl="http://test.com",
 * )
 */
SCHEME;

        $analysis = $this->analysisFromDockBlock($comment);
        $this->assertCount(1, $analysis);
        /** @var \OpenApi\Annotations\SecurityScheme $security */
        $security = $analysis[0];
        $this->assertInstanceOf(SecurityScheme::class, $security);

        $this->assertCount(1, $security->flows);
        $this->assertEquals('implicit', $security->flows[0]->flow);
        $this->assertEquals('http://auth.test.com/token', $security->flows[0]->tokenUrl);
        $this->assertEquals('http://auth.test.com/refresh-token', $security->flows[0]->refreshUrl);
    }

    public function testMultipleAnnotations()
    {
        $comment = <<<SCHEME
/**
 * @OA\SecurityScheme(
 *     @OA\Flow(
 *         flow="implicit",
 *         tokenUrl="http://auth.test.com/token",
 *         refreshUrl="http://auth.test.com/refresh-token"
 *     ),
 *     @OA\Flow(
 *         flow="client_credentials",
 *         authorizationUrl="http://authClient.test.com",
 *         tokenUrl="http://authClient.test.com/token",
 *         refreshUrl="http://authClient.test.com/refresh-token"
 *     ),
 *     securityScheme="oauth2",
 *     in="header",
 *     type="oauth2",
 *     description="Oauth2 security",
 *     name="oauth2",
 *     scheme="https",
 *     bearerFormat="bearer",
 *     openIdConnectUrl="http://test.com",
 * )
 */
SCHEME;

        $analysis = $this->analysisFromDockBlock($comment);
        $this->assertCount(1, $analysis);
        /** @var \OpenApi\Annotations\SecurityScheme $security */
        $security = $analysis[0];

        $this->assertCount(2, $security->flows);
        $this->assertEquals('implicit', $security->flows[0]->flow);
        $this->assertEquals('http://auth.test.com/token', $security->flows[0]->tokenUrl);
        $this->assertEquals('http://auth.test.com/refresh-token', $security->flows[0]->refreshUrl);
        $this->assertEquals('client_credentials', $security->flows[1]->flow);
        $this->assertEquals('http://authClient.test.com', $security->flows[1]->authorizationUrl);
        $this->assertEquals('http://authClient.test.com/token', $security->flows[1]->tokenUrl);
        $this->assertEquals('http://authClient.test.com/refresh-token', $security->flows[1]->refreshUrl);
    }
}
