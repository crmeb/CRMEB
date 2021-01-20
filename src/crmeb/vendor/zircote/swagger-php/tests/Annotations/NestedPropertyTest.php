<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests\Annotations;

use OpenApi\Processors\AugmentProperties;
use OpenApi\Processors\AugmentSchemas;
use OpenApi\Processors\MergeIntoComponents;
use OpenApi\Processors\MergeIntoOpenApi;
use OpenApi\Tests\OpenApiTestCase;
use const OpenApi\UNDEFINED;

class NestedPropertyTest extends OpenApiTestCase
{
    public function testNestedProperties()
    {
        $analysis = $this->analysisFromFixtures('NestedProperty.php');
        $analysis->process(new MergeIntoOpenApi());
        $analysis->process(new MergeIntoComponents());
        $analysis->process(new AugmentSchemas());
        $analysis->process(new AugmentProperties());

        $this->assertCount(1, $analysis->openapi->components->schemas);
        $schema = $analysis->openapi->components->schemas[0];
        $this->assertEquals('NestedProperty', $schema->schema);
        $this->assertCount(1, $schema->properties);

        $parentProperty = $schema->properties[0];
        $this->assertEquals('parentProperty', $parentProperty->property);
        $this->assertCount(1, $parentProperty->properties);

        $babyProperty = $parentProperty->properties[0];
        $this->assertEquals('babyProperty', $babyProperty->property);
        $this->assertCount(1, $babyProperty->properties);

        $theBabyOfBaby = $babyProperty->properties[0];
        $this->assertEquals('theBabyOfBaby', $theBabyOfBaby->property);
        $this->assertCount(1, $theBabyOfBaby->properties);

        // verbose not-recommend notations
        $theBabyOfBabyBaby = $theBabyOfBaby->properties[0];
        $this->assertEquals('theBabyOfBabyBaby', $theBabyOfBabyBaby->property);
        $this->assertSame(UNDEFINED, $theBabyOfBabyBaby->properties);
    }
}
