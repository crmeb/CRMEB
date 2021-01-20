<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests;

use OpenApi\Analysis;
use OpenApi\Annotations\Info;
use OpenApi\Annotations\Response;
use OpenApi\Context;

class RefTest extends OpenApiTestCase
{
    public function testRef()
    {
        $openapi = $this->createOpenApiWithInfo();
        $info = $openapi->ref('#/info');
        $this->assertInstanceOf(Info::class, $info);

        $comment = <<<END
@OA\Get(
    path="/api/~/endpoint",
    @OA\Response(response="default", description="A response")
)
END;
        $openapi->merge($this->parseComment($comment));
        $analysis = new Analysis();
        $analysis->addAnnotation($openapi, Context::detect());
        $analysis->process();

        $analysis->validate();
        // escape / as ~1
        // escape ~ as ~0
        $response = $openapi->ref('#/paths/~1api~1~0~1endpoint/get/responses/default');
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('A response', $response->description);
    }
}
