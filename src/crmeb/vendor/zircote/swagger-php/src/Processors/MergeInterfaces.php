<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use Traversable;

class MergeInterfaces
{
    public function __invoke(Analysis $analysis)
    {
        $schemas = $analysis->getAnnotationsOfType(Schema::class);
        foreach ($schemas as $schema) {
            if ($schema->_context->is('class')) {
                $existing = [];
                $interfaces = $analysis->getInterfacesOfClass($schema->_context->fullyQualifiedName($schema->_context->class));
                foreach ($interfaces as $interface) {
                    foreach ($interface['context']->annotations as $annotation) {
                        if ($annotation instanceof Property && !in_array($annotation->_context->property, $existing)) {
                            $existing[] = $annotation->_context->property;
                            $schema->merge([$annotation], true);
                        }
                    }

                    foreach ($interface['methods'] as $method) {
                        if (is_array($method->annotations) || $method->annotations instanceof Traversable) {
                            foreach ($method->annotations as $annotation) {
                                if ($annotation instanceof Property && !in_array($annotation->_context->property, $existing)) {
                                    $existing[] = $annotation->_context->property;
                                    $schema->merge([$annotation], true);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
