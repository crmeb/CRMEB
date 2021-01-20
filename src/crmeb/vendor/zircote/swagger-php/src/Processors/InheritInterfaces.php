<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Components;
use OpenApi\Annotations\Schema;

class InheritInterfaces
{
    public function __invoke(Analysis $analysis)
    {
        $schemas = $analysis->getAnnotationsOfType(Schema::class);
        foreach ($schemas as $schema) {
            if ($schema->_context->is('class')) {
                $interfaces = $analysis->getInterfacesOfClass($schema->_context->fullyQualifiedName($schema->_context->class), true);
                foreach ($interfaces as $interface) {
                    if ($analysis->getSchemaForSource($interface['context']->fullyQualifiedName($interface['interface']))) {
                        if ($schema->allOf === UNDEFINED) {
                            $schema->allOf = [];
                        }
                        $schema->allOf[] = new Schema([
                            '_context' => $interface['context']->_context,
                            'ref' => Components::SCHEMA_REF.$interface['interface'],
                        ]);
                    }
                }
            }
        }
    }
}
