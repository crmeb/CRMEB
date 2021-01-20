<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests\Annotations;

use OpenApi\Tests\OpenApiTestCase;

class ResponseTest extends OpenApiTestCase
{
    public function testMisspelledDefault()
    {
        $this->validateMisspelledAnnotation('Default');
    }

    public function testMisspelledRangeDefinition()
    {
        $this->validateMisspelledAnnotation('5xX');
    }

    public function testWrongRangeDefinition()
    {
        $this->validateMisspelledAnnotation('6XX');
    }

    protected function validateMisspelledAnnotation(string $response = '')
    {
        $annotations = $this->parseComment(
            '@OA\Get(@OA\Response(response="'.$response.'", description="description"))'
        );
        /*
         * @see Annotations/Operation.php:187
         */
        $this->assertOpenApiLogEntryContains(
            'Invalid value "'.$response.'" for @OA\Response()->response, expecting "default"'
            .', a HTTP Status Code or HTTP '
        );
        $annotations[0]->validate();
    }
}
