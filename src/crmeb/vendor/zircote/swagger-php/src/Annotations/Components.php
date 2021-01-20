<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Annotations;

/**
 * @Annotation
 * A Components Object: https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.0.md#components-object
 *
 * Holds a set of reusable objects for different aspects of the OA.
 * All objects defined within the components object will have no effect on the API unless they are explicitly referenced from properties outside the components object.
 */
class Components extends AbstractAnnotation
{
    /**
     * Schema reference.
     *
     * @var string
     */
    const SCHEMA_REF = '#/components/schemas/';

    /**
     * Reusable Schemas.
     *
     * @var Schema[]
     */
    public $schemas = UNDEFINED;

    /**
     * Reusable Responses.
     *
     * @var Response[]
     */
    public $responses = UNDEFINED;

    /**
     * Reusable Parameters.
     *
     * @var Parameter[]
     */
    public $parameters = UNDEFINED;

    /**
     * Reusable Examples.
     *
     * @var Examples[]
     */
    public $examples = UNDEFINED;

    /**
     * Reusable Request Bodys.
     *
     * @var RequestBody[]
     */
    public $requestBodies = UNDEFINED;

    /**
     * Reusable Headers.
     *
     * @var Header[]
     */
    public $headers = UNDEFINED;

    /**
     * Reusable Security Schemes.
     *
     * @var SecurityScheme[]
     */
    public $securitySchemes = UNDEFINED;

    /**
     * Reusable Links.
     *
     * @var Link[]
     */
    public $links = UNDEFINED;

    /**
     * Reusable Callbacks.
     *
     * @var callback[]
     */
    public $callbacks = UNDEFINED;

    /**
     * {@inheritdoc}
     */
    public static $_parents = [
        OpenApi::class,
    ];

    /**
     * {@inheritdoc}
     */
    public static $_nested = [
        Schema::class => ['schemas', 'schema'],
        Response::class => ['responses', 'response'],
        Parameter::class => ['parameters', 'parameter'],
        RequestBody::class => ['requestBodies', 'request'],
        Examples::class => ['examples', 'example'],
        Header::class => ['headers', 'header'],
        SecurityScheme::class => ['securitySchemes', 'securityScheme'],
        Link::class => ['links', 'link'],
    ];
}
