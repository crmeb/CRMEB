<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Processors;

use OpenApi\Analysis;

/**
 * Use the parameter->name as keyfield (parameter->parameter) when used as reusable component (openapi->components->parameters).
 */
class AugmentParameters
{
    public function __invoke(Analysis $analysis)
    {
        if ($analysis->openapi->components !== UNDEFINED && $analysis->openapi->components->parameters !== UNDEFINED) {
            $keys = [];
            $parametersWithoutKey = [];
            foreach ($analysis->openapi->components->parameters as $parameter) {
                if ($parameter->parameter !== UNDEFINED) {
                    $keys[$parameter->parameter] = $parameter;
                } else {
                    $parametersWithoutKey[] = $parameter;
                }
            }
            foreach ($parametersWithoutKey as $parameter) {
                if ($parameter->name !== UNDEFINED && empty($keys[$parameter->name])) {
                    $parameter->parameter = $parameter->name;
                    $keys[$parameter->parameter] = $parameter;
                }
            }
        }
    }
}
