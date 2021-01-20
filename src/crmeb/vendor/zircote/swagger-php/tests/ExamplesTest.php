<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests;

use OpenApi\Analysis;
use OpenApi\Logger;
use OpenApi\Processors\InheritInterfaces;
use OpenApi\Processors\InheritTraits;
use OpenApi\Processors\MergeInterfaces;
use OpenApi\Processors\MergeTraits;

class ExamplesTest extends OpenApiTestCase
{
    public function exampleMappings()
    {
        return [
            'misc' => ['misc', 'misc.yaml', []],
            'openapi-spec' => ['openapi-spec', 'openapi-spec.yaml', []],
            'petstore.swagger.io' => ['petstore.swagger.io', 'petstore.swagger.io.yaml', []],
            'petstore-3.0' => ['petstore-3.0', 'petstore-3.0.yaml', []],
            'swagger-spec/petstore' => ['swagger-spec/petstore', 'petstore.yaml', []],
            'swagger-spec/petstore-simple' => ['swagger-spec/petstore-simple', 'petstore-simple.yaml', []],
            'swagger-spec/petstore-with-external-docs' => ['swagger-spec/petstore-with-external-docs', 'petstore-with-external-docs.yaml', []],
            'using-refs' => ['using-refs', 'using-refs.yaml', []],
            'example-object' => ['example-object', 'example-object.yaml', []],
            'using-interfaces-inherit' => ['using-interfaces', 'using-interfaces-inherit.yaml', []],
            'using-interfaces-merge' => ['using-interfaces', 'using-interfaces-merge.yaml', $this->processors(InheritInterfaces::class, new MergeInterfaces())],
            'using-traits-inherit' => ['using-traits', 'using-traits-inherit.yaml', []],
            'using-traits-merge' => ['using-traits', 'using-traits-merge.yaml', $this->processors(InheritTraits::class, new MergeTraits())],
        ];
    }

    /**
     * Swap processor.
     */
    private function processors($fromClass, $to)
    {
        $processors = [];
        foreach (Analysis::processors() as $processor) {
            if ($processor instanceof $fromClass) {
                $processors[] = $to;
            } else {
                $processors[] = $processor;
            }
        }

        return $processors;
    }

    /**
     * Validate openapi definitions of the included examples.
     *
     * @dataProvider exampleMappings
     */
    public function testExamples($example, $spec, array $processors)
    {
        Logger::getInstance()->log = function ($entry, $type) {
            // ignore
        };
        $options = [];
        if ($processors) {
            $options['processors'] = $processors;
        }

        $path = __DIR__.'/../Examples/'.$example;
        $openapi = \OpenApi\scan($path, $options);
        $this->assertSpecEquals(file_get_contents($path.'/'.$spec), $openapi);
    }
}
