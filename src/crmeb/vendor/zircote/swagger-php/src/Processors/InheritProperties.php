<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Components;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use OpenApi\UNDEFINED;
use Traversable;

/**
 * Copy the annotated properties from parent classes;.
 */
class InheritProperties
{
    public function __invoke(Analysis $analysis)
    {
        /* @var  $schemas Schema[] */
        $schemas = $analysis->getAnnotationsOfType(Schema::class);

        $processedSchemas = [];

        foreach ($schemas as $schema) {
            if ($schema->_context->is('class')) {
                if (in_array($schema->_context, $processedSchemas, true)) {
                    // we should process only first schema in the same context
                    continue;
                }

                $processedSchemas[] = $schema->_context;

                $existing = [];
                if (is_array($schema->properties) || $schema->properties instanceof Traversable) {
                    foreach ($schema->properties as $property) {
                        if ($property->property) {
                            $existing[] = $property->property;
                        }
                    }
                }
                $classes = $analysis->getSuperClasses($schema->_context->fullyQualifiedName($schema->_context->class));
                foreach ($classes as $class) {
                    if ($class['context']->annotations) {
                        foreach ($class['context']->annotations as $annotation) {
                            if ($annotation instanceof Schema && $annotation->schema !== UNDEFINED) {
                                $this->addAllOfProperty($schema, $annotation);

                                continue 2;
                            }
                        }
                    }

                    foreach ($class['properties'] as $property) {
                        if (is_array($property->annotations) === false && !($property->annotations instanceof Traversable)) {
                            continue;
                        }
                        foreach ($property->annotations as $annotation) {
                            if ($annotation instanceof Property && in_array($annotation->property, $existing) === false) {
                                $existing[] = $annotation->property;
                                $schema->merge([$annotation], true);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Add schema to child schema allOf property.
     */
    private function addAllOfProperty(Schema $childSchema, Schema $parentSchema)
    {
        // clone (child) properties (except when they are already in allOf)
        $currentSchema = new Schema(['_context' => $childSchema->_context]);
        $currentSchema->mergeProperties($childSchema);

        $defaultValues = get_class_vars(Schema::class);
        foreach (array_keys(get_object_vars($currentSchema)) as $property) {
            $childSchema->$property = $defaultValues[$property];
        }

        $childSchema->schema = $currentSchema->schema;
        $currentSchema->schema = UNDEFINED;

        if ($childSchema->allOf === UNDEFINED) {
            $childSchema->allOf = [];
        }

        if ($currentSchema->allOf !== UNDEFINED) {
            foreach ($currentSchema->allOf as $ii => $schema) {
                if ($schema->schema === UNDEFINED && $schema->properties !== UNDEFINED) {
                    // move properties from allOf back up into schema
                    $currentSchema->properties = $schema->properties;
                } elseif ($schema->ref !== UNDEFINED && $schema->ref != Components::SCHEMA_REF.$parentSchema->schema) {
                    // keep other schemas
                    $childSchema->allOf[] = $schema;
                }
            }
            $currentSchema->allOf = UNDEFINED;
        }

        $childSchema->allOf[] = new Schema([
            '_context' => $parentSchema->_context,
            'ref' => Components::SCHEMA_REF.$parentSchema->schema,
        ]);
        if ($currentSchema->properties !== UNDEFINED) {
            $childSchema->allOf[] = $currentSchema;
        }
    }
}
