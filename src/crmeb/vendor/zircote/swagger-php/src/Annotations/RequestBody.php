<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Annotations;

/**
 * @Annotation
 * A "Response Object": https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.0.md#requestBodyObject
 *
 * Describes a single response from an API Operation, including design-time, static links to operations based on the
 * response.
 */
class RequestBody extends AbstractAnnotation
{
    public $ref = UNDEFINED;

    /**
     * Request body model name.
     *
     * @var string
     */
    public $request = UNDEFINED;

    /**
     * A brief description of the parameter.
     * This could contain examples of use.
     * CommonMark syntax may be used for rich text representation.
     *
     * @var string
     */
    public $description = UNDEFINED;

    /**
     * Determines whether this parameter is mandatory.
     * If the parameter location is "path", this property is required and its value must be true.
     * Otherwise, the property may be included and its default value is false.
     *
     * @var bool
     */
    public $required = UNDEFINED;

    /**
     * The content of the request body.
     * The key is a media type or media type range and the value describes it. For requests that match multiple keys,
     * only the most specific key is applicable. e.g. text/plain overrides text/*.
     *
     * @var MediaType[]
     */
    public $content = UNDEFINED;

    /**
     * {@inheritdoc}
     */
    public static $_types = [
        'description' => 'string',
        'required' => 'boolean',
        'request' => 'string',
    ];

    public static $_parents = [
        Components::class,
        Delete::class,
        Get::class,
        Head::class,
        Operation::class,
        Options::class,
        Patch::class,
        Post::class,
        Trace::class,
        Put::class,
    ];

    /**
     * {@inheritdoc}
     */
    public static $_nested = [
        MediaType::class => ['content', 'mediaType'],
    ];
}
