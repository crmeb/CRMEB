<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Annotations;

/**
 * @Annotation
 */
class Property extends Schema
{
    /**
     * The key into Schema->properties array.
     *
     * @var string
     */
    public $property = UNDEFINED;

    /**
     * {@inheritdoc}
     */
    public static $_parents = [
        AdditionalProperties::class,
        Schema::class,
        JsonContent::class,
        XmlContent::class,
        Property::class,
        Items::class,
    ];

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
