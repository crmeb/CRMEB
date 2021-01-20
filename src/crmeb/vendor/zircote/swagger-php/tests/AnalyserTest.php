<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests;

class AnalyserTest extends OpenApiTestCase
{
    public function testParseContents()
    {
        $annotations = $this->parseComment('@OA\Parameter(description="This is my parameter")');
        $this->assertIsArray($annotations);
        $parameter = $annotations[0];
        $this->assertInstanceOf('OpenApi\Annotations\Parameter', $parameter);
        $this->assertSame('This is my parameter', $parameter->description);
    }

    public function testDeprecatedAnnotationWarning()
    {
        $this->countExceptions = 1;
        $this->assertOpenApiLogEntryContains('The annotation @SWG\Definition() is deprecated.');
        $this->parseComment('@SWG\Definition()');
    }
}
