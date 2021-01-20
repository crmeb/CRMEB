<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Annotations;

/**
 * @Annotation
 * A "Response Object": https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.0.md#response-object
 *
 * Describes a single response from an API Operation, including design-time, static links to operations based on the response.
 */
class Response extends AbstractAnnotation
{
    /**
     * $ref See https://swagger.io/docs/specification/using-ref/.
     *
     * @var string
     */
    public $ref = UNDEFINED;

    /**
     * The key into Operations->responses array.
     *
     * @var string a HTTP Status Code or "default"
     */
    public $response = UNDEFINED;

    /**
     * A short description of the response.
     * CommonMark syntax may be used for rich text representation.
     *
     * @var string
     */
    public $description = UNDEFINED;

    /**
     * Maps a header name to its definition.
     * RFC7230 states header names are case insensitive. https://tools.ietf.org/html/rfc7230#page-22
     * If a response header is defined with the name "Content-Type", it shall be ignored.
     *
     * @var Header[]
     */
    public $headers = UNDEFINED;

    /**
     * A map containing descriptions of potential response payloads.
     * The key is a media type or media type range and the value describes it.
     * For responses that match multiple keys, only the most specific key is applicable. e.g. text/plain overrides text/*.
     *
     * @var MediaType[]
     */
    public $content = UNDEFINED;

    /**
     * A map of operations links that can be followed from the response.
     * The key of the map is a short name for the link, following the naming constraints of the names for Component Objects.
     *
     * @var array
     */
    public $links = UNDEFINED;

    /**
     * {@inheritdoc}
     */
    public static $_required = ['description'];

    /**
     * {@inheritdoc}
     */
    public static $_types = [
        'description' => 'string',
    ];

    /**
     * {@inheritdoc}
     */
    public static $_nested = [
        MediaType::class => ['content', 'mediaType'],
        Header::class => ['headers', 'header'],
        Link::class => ['links', 'link'],
    ];

    /**
     * {@inheritdoc}
     */
    public static $_parents = [
        Components::class,
        Operation::class,
        Get::class,
        Post::class,
        Put::class,
        Patch::class,
        Delete::class,
        Head::class,
        Options::class,
        Trace::class,
    ];
}
