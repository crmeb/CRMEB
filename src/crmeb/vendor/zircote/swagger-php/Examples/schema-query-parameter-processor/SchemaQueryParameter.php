<?php

namespace SchemaQueryParameterProcessor;

use OpenApi\Analysis;
use OpenApi\Annotations\Components;
use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Parameter;
use OpenApi\Annotations\Schema;
use const OpenApi\UNDEFINED;

/**
 * Custom processor to translate the vendor tag `query-args-$ref` into query parameter annotations.
 *
 * Details for the parameters are taken from the referenced schema.
 */
class SchemaQueryParameter
{
    const X_QUERY_AGS_REF = 'query-args-$ref';

    public function __invoke(Analysis $analysis)
    {
        $schemas = $analysis->getAnnotationsOfType(Schema::class, true);
        $operations = $analysis->getAnnotationsOfType(Operation::class);

        foreach ($operations as $operation) {
            if ($operation->x !== UNDEFINED && array_key_exists(self::X_QUERY_AGS_REF, $operation->x)) {
                if ($schema = $this->schemaForRef($schemas, $operation->x[self::X_QUERY_AGS_REF])) {
                    $this->expandQueryArgs($operation, $schema);
                    $this->cleanUp($operation);
                }
            }
        }
    }

    /**
     * Find schema for the given ref.
     */
    protected function schemaForRef(array $schemas, string $ref)
    {
        foreach ($schemas as $schema) {
            if (Components::SCHEMA_REF . $schema->schema === $ref) {
                return $schema;
            }
        }

        return null;
    }

    /**
     * Expand the given operation by injecting parameters for all properties of the given schema.
     */
    protected function expandQueryArgs(Operation $operation, Schema $schema)
    {
        if ($schema->properties === UNDEFINED || !$schema->properties) {
            return;
        }

        $operation->parameters = $operation->parameters === UNDEFINED ? [] : $operation->parameters;
        foreach ($schema->properties as $property) {
            $parameter = new Parameter([
                'name' => $property->property,
                'in' => 'query',
                'required' => false,
            ]);
            $operation->parameters[] = $parameter;
        }
    }

    /**
     * Clean up.
     */
    protected function cleanUp($operation)
    {
        unset($operation->x[self::X_QUERY_AGS_REF]);
        if (!$operation->x) {
            $operation->x = UNDEFINED;
        }
    }
}

