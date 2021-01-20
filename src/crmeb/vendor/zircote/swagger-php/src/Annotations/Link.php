<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Annotations;

/**
 * @Annotation
 * A "Link Object" https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.0.md#link-object
 *
 * The Link object represents a possible design-time link for a response.
 * The presence of a link does not guarantee the caller's ability to successfully invoke it, rather it provides a known relationship and traversal mechanism between responses and other operations.
 * Unlike dynamic links (i.e. links provided in the response payload), the OA linking mechanism does not require link information in the runtime response.
 * For computing links, and providing instructions to execute them, a runtime expression is used for accessing values in an operation and using them as parameters while invoking the linked operation.
 */
class Link extends AbstractAnnotation
{

    /**
     * $ref See https://swagger.io/docs/specification/using-ref/.
     *
     * @var string
     */
    public $ref = UNDEFINED;

    /**
     * The key into MediaType->links array.
     *
     * @var string
     */
    public $link = UNDEFINED;

    /**
     * A relative or absolute reference to an OA operation.
     * This field is mutually exclusive of the operationId field, and must point to an Operation Object.
     * Relative operationRef values may be used to locate an existing Operation Object in the OpenAPI definition.
     *
     * @var string
     */
    public $operationRef = UNDEFINED;

    /**
     * The name of an existing, resolvable OA operation, as defined with a unique operationId.
     * This field is mutually exclusive of the operationRef field.
     *
     * @var string
     */
    public $operationId = UNDEFINED;

    /**
     * A map representing parameters to pass to an operation as specified with operationId or identified via operationRef.
     * The key is the parameter name to be used, whereas the value can be a constant or an expression to be evaluated and passed to the linked operation.
     * The parameter name can be qualified using the parameter location [{in}.]{name} for operations that use the same parameter name in different locations (e.g. path.id).
     */
    public $parameters = UNDEFINED;

    /**
     * A literal value or {expression} to use as a request body when calling the target operation.
     */
    public $requestBody = UNDEFINED;

    /**
     * A description of the link.
     * CommonMark syntax may be used for rich text representation.
     *
     * @var string
     */
    public $description = UNDEFINED;

    /**
     * A server object to be used by the target operation.
     *
     * @var Server
     */
    public $server = UNDEFINED;

    /**
     * {@inheritdoc}
     */
    public static $_nested = [
        Server::class => 'server',
    ];

    /**
     * {@inheritdoc}
     */
    public static $_parents = [
        Components::class,
        Response::class,
    ];
}
