<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Annotations;

use Exception;
use OpenApi\Analysis;
use OpenApi\Logger;

/**
 * @Annotation
 * This is the root document object for the API specification.
 *
 * A  "OpenApi Object": https://github.com/OAI/OpenAPI-Specification/blob/OpenAPI.next/versions/3.0.md#openapi-object
 */
class OpenApi extends AbstractAnnotation
{
    /**
     * The semantic version number of the OpenAPI Specification version that the OpenAPI document uses.
     * The openapi field should be used by tooling specifications and clients to interpret the OpenAPI document.
     * This is not related to the API info.version string.
     *
     * @var string
     */
    public $openapi = '3.0.0';

    /**
     * Provides metadata about the API. The metadata may be used by tooling as required.
     *
     * @var Info
     */
    public $info = UNDEFINED;

    /**
     * An array of Server Objects, which provide connectivity information to a target server.
     * If the servers property is not provided, or is an empty array, the default value would be a Server Object with a url value of /.
     *
     * @var Server[]
     */
    public $servers = UNDEFINED;

    /**
     * The available paths and operations for the API.
     *
     * @var PathItem[]
     */
    public $paths = UNDEFINED;

    /**
     * An element to hold various components for the specification.
     *
     * @var Components
     */
    public $components = UNDEFINED;

    /**
     * Lists the required security schemes to execute this operation.
     * The name used for each property must correspond to a security scheme declared
     * in the Security Schemes under the Components Object.
     * Security Requirement Objects that contain multiple schemes require that
     * all schemes must be satisfied for a request to be authorized.
     * This enables support for scenarios where multiple query parameters or
     * HTTP headers are required to convey security information.
     * When a list of Security Requirement Objects is defined on the Open API object or
     * Operation Object, only one of Security Requirement Objects in the list needs to
     * be satisfied to authorize the request.
     *
     * @var array
     */
    public $security = UNDEFINED;

    /**
     * A list of tags used by the specification with additional metadata.
     * The order of the tags can be used to reflect on their order by the parsing tools.
     * Not all tags that are used by the Operation Object must be declared.
     * The tags that are not declared may be organized randomly or based on the tools' logic.
     * Each tag name in the list must be unique.
     *
     * @var Tag[]
     */
    public $tags = UNDEFINED;

    /**
     * Additional external documentation.
     *
     * @var ExternalDocumentation
     */
    public $externalDocs = UNDEFINED;

    /**
     * @var Analysis
     */
    public $_analysis = UNDEFINED;

    /**
     * {@inheritdoc}
     */
    public static $_blacklist = ['_context', '_unmerged', '_analysis'];

    /**
     * {@inheritdoc}
     */
    public static $_required = ['openapi', 'info', 'paths'];

    /**
     * {@inheritdoc}
     */
    public static $_nested = [
        Info::class => 'info',
        Server::class => ['servers'],
        PathItem::class => ['paths', 'path'],
        Components::class => 'components',
        Tag::class => ['tags'],
        ExternalDocumentation::class => 'externalDocs',
    ];

    /**
     * {@inheritdoc}
     */
    public static $_types = [];

    /**
     * {@inheritdoc}
     */
    public function validate($parents = null, $skip = null, $ref = null)
    {
        if ($parents !== null || $skip !== null || $ref !== null) {
            Logger::notice('Nested validation for '.$this->identity().' not allowed');

            return false;
        }

        return parent::validate([], [], '#');
    }

    /**
     * Save the OpenAPI documentation to a file.
     *
     * @param string $filename
     *
     * @throws Exception
     */
    public function saveAs($filename, $format = 'auto')
    {
        if ($format === 'auto') {
            $format =   strtolower(substr($filename, -5)) === '.json' ? 'json' : 'yaml';
        }
        if (strtolower($format) === 'json') {
            $content = $this->toJson();
        } else {
            $content = $this->toYaml();
        }
        if (file_put_contents($filename, $content) === false) {
            throw new Exception('Failed to saveAs("'.$filename.'", "'.$format.'")');
        }
    }

    /**
     * Look up an annotation with a $ref url.
     *
     * @param string $ref The $ref value, for example: "#/components/schemas/Product"
     *
     * @throws Exception
     */
    public function ref($ref)
    {
        if (substr($ref, 0, 2) !== '#/') {
            // @todo Add support for external (http) refs?
            throw new Exception('Unsupported $ref "'.$ref.'", it should start with "#/"');
        }

        return $this->resolveRef($ref, '#/', $this, []);
    }

    /**
     * Recursive helper for ref().
     *
     * @param *     $container the container to resolve the ref in
     * @param array $mapping
     */
    private static function resolveRef($ref, $resolved, $container, $mapping)
    {
        if ($ref === $resolved) {
            return $container;
        }
        $path = substr($ref, strlen($resolved));
        $slash = strpos($path, '/');

        $subpath = $slash === false ? $path : substr($path, 0, $slash);
        $property = self::unescapeRef($subpath);
        $unresolved = $slash === false ? $resolved.$subpath : $resolved.$subpath.'/';

        if (is_object($container)) {
            if (property_exists($container, $property) === false) {
                throw new Exception('$ref "'.$ref.'" not found');
            }
            if ($slash === false) {
                return $container->$property;
            }
            $mapping = [];
            if ($container instanceof AbstractAnnotation) {
                foreach ($container::$_nested as $nestedClass => $nested) {
                    if (is_string($nested) === false && count($nested) === 2 && $nested[0] === $property) {
                        $mapping[$nestedClass] = $nested[1];
                    }
                }
            }

            return self::resolveRef($ref, $unresolved, $container->$property, $mapping);
        } elseif (is_array($container)) {
            if (array_key_exists($property, $container)) {
                return self::resolveRef($ref, $unresolved, $container[$property], []);
            }
            foreach ($mapping as $nestedClass => $keyField) {
                foreach ($container as $key => $item) {
                    if (is_numeric($key) && is_object($item) && $item instanceof $nestedClass && (string) $item->$keyField === $property) {
                        return self::resolveRef($ref, $unresolved, $item, []);
                    }
                }
            }
        }
        throw new Exception('$ref "'.$unresolved.'" not found');
    }

    /**
     * Decode the $ref escape characters.
     *
     * https://swagger.io/docs/specification/using-ref/
     * https://tools.ietf.org/html/rfc6901#page-3
     */
    private static function unescapeRef($encoded)
    {
        $decoded = '';
        $length = strlen($encoded);
        for ($i = 0; $i < $length; $i++) {
            $char = $encoded[$i];
            if ($char === '~' && $i !== $length - 1) {
                $next = $encoded[$i + 1];
                if ($next === '0') { // escaped `~`
                    $decoded .= '~';
                    $i++;
                } elseif ($next === '1') { // escaped `/`
                    $decoded .= '/';
                    $i++;
                } else {
                    // this ~ had special meaning :-(
                    $decoded .= $char;
                }
            } else {
                $decoded .= $char;
            }
        }

        return $decoded;
    }
}
