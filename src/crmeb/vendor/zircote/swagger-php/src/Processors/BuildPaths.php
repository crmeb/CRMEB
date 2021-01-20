<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Operation;
use OpenApi\Annotations\PathItem;
use OpenApi\Context;
use OpenApi\Logger;

/**
 * Build the openapi->paths using the detected @OA\PathItem and @OA\Operations (like @OA\Get, @OA\Post, etc).
 */
class BuildPaths
{
    public function __invoke(Analysis $analysis)
    {
        $paths = [];
        // Merge @OA\PathItems with the same path.
        if ($analysis->openapi->paths !== UNDEFINED) {
            foreach ($analysis->openapi->paths as $annotation) {
                if (empty($annotation->path)) {
                    Logger::notice($annotation->identity().' is missing required property "path" in '.$annotation->_context);
                } elseif (isset($paths[$annotation->path])) {
                    $paths[$annotation->path]->mergeProperties($annotation);
                    $analysis->annotations->detach($annotation);
                } else {
                    $paths[$annotation->path] = $annotation;
                }
            }
        }

        // Merge @OA\Operations into existing @OA\PathItems or create a new one.
        $operations = $analysis->unmerged()->getAnnotationsOfType(Operation::class);
        foreach ($operations as $operation) {
            if ($operation->path) {
                if (empty($paths[$operation->path])) {
                    $paths[$operation->path] = new PathItem(
                        [
                            'path' => $operation->path,
                            '_context' => new Context(['generated' => true], $operation->_context),
                        ]
                    );
                    $analysis->annotations->attach($paths[$operation->path]);
                }
                if ($paths[$operation->path]->merge([$operation])) {
                    Logger::notice('Unable to merge '.$operation->identity().' in '.$operation->_context);
                }
            }
        }
        if (count($paths)) {
            $analysis->openapi->paths = array_values($paths);
        }
    }
}
