<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Annotations;

/**
 * @Annotation
 * Shorthand for a json response.
 *
 * Use as an Schema inside a Response and the MediaType "application/json" will be generated.
 */
class JsonContent extends Schema
{

    /**
     * @var object
     */
    public $example = UNDEFINED;

    /**
     * @var object
     */
    public $examples = UNDEFINED;

    /**
     * {@inheritdoc}
     */
    public static $_parents = [];

    /**
     * {@inheritdoc}
     */
    public static $_nested = [
        Discriminator::class => 'discriminator',
        Items::class => 'items',
        Property::class => ['properties', 'property'],
        ExternalDocumentation::class => 'externalDocs',
        Xml::class => 'xml',
        AdditionalProperties::class => 'additionalProperties',
    ];
}
