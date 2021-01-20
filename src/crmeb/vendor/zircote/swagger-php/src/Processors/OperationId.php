<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Operation;

/**
 * Generate the OperationId based on the context of the OpenApi annotation.
 */
class OperationId
{
    public function __invoke(Analysis $analysis)
    {
        $allOperations = $analysis->getAnnotationsOfType(Operation::class);

        foreach ($allOperations as $operation) {
            if ($operation->operationId !== UNDEFINED) {
                continue;
            }
            $context = $operation->_context;
            if ($context && $context->method) {
                $source = $context->class ?? $context->interface ?? $context->trait;
                if ($source) {
                    if ($context->namespace) {
                        $operation->operationId = $context->namespace.'\\'.$source.'::'.$context->method;
                    } else {
                        $operation->operationId = $source.'::'.$context->method;
                    }
                } else {
                    $operation->operationId = $context->method;
                }
            }
        }
    }
}
