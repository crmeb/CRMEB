<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests\Annotations;

use OpenApi\StaticAnalyser;
use OpenApi\Tests\OpenApiTestCase;

class ItemsTest extends OpenApiTestCase
{
    public function testItemTypeArray()
    {
        $annotations = $this->parseComment('@OA\Items(type="array")');
        $this->assertOpenApiLogEntryContains('@OA\Items() is required when @OA\Items() has type "array" in ');
        $annotations[0]->validate();
    }

    public function testSchemaTypeArray()
    {
        $annotations = $this->parseComment('@OA\Schema(type="array")');
        $this->assertOpenApiLogEntryContains('@OA\Items() is required when @OA\Schema() has type "array" in ');
        $annotations[0]->validate();
    }

    public function testTypeObject()
    {
        $this->countExceptions = 1;
        $notAllowedInQuery = $this->parseComment('@OA\Parameter(name="param",in="query",@OA\Schema(type="array",@OA\Items(type="object")))');
        $this->assertOpenApiLogEntryContains('@OA\Items()->type="object" not allowed inside a @OA\Parameter() must be "string", "number", "integer", "boolean", "array" in ');
        $notAllowedInQuery[0]->validate();
    }

    public function testRefDefinitionInProperty()
    {
        $analyser = new StaticAnalyser();
        $analysis = $analyser->fromFile($this->fixtures('UsingVar.php')[0]);
        $analysis->process();

        $this->assertCount(2, $analysis->openapi->components->schemas);
        $this->assertEquals('UsingVar', $analysis->openapi->components->schemas[0]->schema);
        $this->assertIsArray($analysis->openapi->components->schemas[0]->properties);
        $this->assertCount(2, $analysis->openapi->components->schemas[0]->properties);
        $this->assertEquals('name', $analysis->openapi->components->schemas[0]->properties[0]->property);
        $this->assertEquals('createdAt', $analysis->openapi->components->schemas[0]->properties[1]->property);
        $this->assertEquals('#/components/schemas/date', $analysis->openapi->components->schemas[0]->properties[1]->ref);
    }
}
