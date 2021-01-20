<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Annotations;

/**
 * @Annotation
 * An "Info Object": https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.0.md#info-object
 *
 * The object provides metadata about the API.
 * The metadata may be used by the clients if needed, and may be presented in editing or documentation generation tools for convenience.
 */
class Info extends AbstractAnnotation
{
    /**
     * The title of the application.
     *
     * @var string
     */
    public $title = UNDEFINED;

    /**
     * A short description of the application. CommonMark syntax may be used for rich text representation.
     *
     * @var string
     */
    public $description = UNDEFINED;

    /**
     * A URL to the Terms of Service for the API. must be in the format of a url.
     *
     * @var string
     */
    public $termsOfService = UNDEFINED;

    /**
     * The contact information for the exposed API.
     *
     * @var Contact
     */
    public $contact = UNDEFINED;

    /**
     * The license information for the exposed API.
     *
     * @var License
     */
    public $license = UNDEFINED;

    /**
     * The version of the OpenAPI document (which is distinct from the OpenAPI Specification version or the API implementation version).
     *
     * @var string
     */
    public $version = UNDEFINED;

    /**
     * {@inheritdoc}
     */
    public static $_required = ['title', 'version'];

    /**
     * {@inheritdoc}
     */
    public static $_types = [
        'title' => 'string',
        'version' => 'string',
        'description' => 'string',
        'termsOfService' => 'string',
    ];

    /**
     * {@inheritdoc}
     */
    public static $_nested = [
        Contact::class => 'contact',
        License::class => 'license',
    ];

    /**
     * {@inheritdoc}
     */
    public static $_parents = [
        OpenApi::class,
    ];
}
