<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests\Processors;

use OpenApi\Processors\AugmentSchemas;
use OpenApi\Processors\MergeIntoComponents;
use OpenApi\Processors\MergeIntoOpenApi;
use OpenApi\Tests\OpenApiTestCase;
use const OpenApi\UNDEFINED;

class AugmentSchemasTest extends OpenApiTestCase
{
    public function testAugmentSchemas()
    {
        $analysis = $this->analysisFromFixtures('Customer.php');
        $analysis->process(new MergeIntoOpenApi()); // create openapi->components
        $analysis->process(new MergeIntoComponents()); // Merge standalone Scheme's into openapi->components

        $this->assertCount(1, $analysis->openapi->components->schemas);
        $customer = $analysis->openapi->components->schemas[0];
        $this->assertSame(UNDEFINED, $customer->schema, 'Sanity check. No scheme was defined');
        $this->assertSame(UNDEFINED, $customer->properties, 'Sanity check. @OA\Property\'s not yet merged ');
        $analysis->process(new AugmentSchemas());
        $this->assertSame('Customer', $customer->schema, '@OA\Schema()->schema based on classname');
        $this->assertCount(9, $customer->properties, '@OA\Property()s are merged into the @OA\Schema of the class');
    }

    public function testAugmentSchemasForInterface()
    {
        $analysis = $this->analysisFromFixtures('CustomerInterface.php');
        $analysis->process(new MergeIntoOpenApi()); // create openapi->components
        $analysis->process(new MergeIntoComponents()); // Merge standalone Scheme's into openapi->components

        $this->assertCount(1, $analysis->openapi->components->schemas);
        $customer = $analysis->openapi->components->schemas[0];
        $this->assertSame(UNDEFINED, $customer->properties, 'Sanity check. @OA\Property\'s not yet merged ');
        $analysis->process(new AugmentSchemas());
        $this->assertCount(9, $customer->properties, '@OA\Property()s are merged into the @OA\Schema of the class');
    }
}
