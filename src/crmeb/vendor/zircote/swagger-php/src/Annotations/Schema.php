<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Annotations;

use OpenApi\Logger;

/**
 * @Annotation
 * The definition of input and output data types.
 * These types can be objects, but also primitives and arrays.
 * This object is based on the [JSON Schema Specification](http://json-schema.org) and uses a predefined subset of it.
 * On top of this subset, there are extensions provided by this specification to allow for more complete documentation.
 *
 * A "Schema Object": https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.0.md#schemaObject
 * JSON Schema: http://json-schema.org/
 */
class Schema extends AbstractAnnotation
{
    /**
     * $ref See https://swagger.io/docs/specification/using-ref/.
     *
     * @var string
     */
    public $ref = UNDEFINED;

    /**
     * The key into Components->schemas array.
     *
     * @var string
     */
    public $schema = UNDEFINED;

    /**
     * Can be used to decorate a user interface with information about the data produced by this user interface. preferrably be short.
     *
     * @var string
     */
    public $title = UNDEFINED;

    /**
     * A description will provide explanation about the purpose of the instance described by this schema.
     *
     * @var string
     */
    public $description = UNDEFINED;

    /**
     * An object instance is valid against "maxProperties" if its number of properties is less than, or equal to, the value of this property.
     *
     * @var int
     */
    public $maxProperties = UNDEFINED;

    /**
     * An object instance is valid against "minProperties" if its number of properties is greater than, or equal to, the value of this property.
     *
     * @var int
     */
    public $minProperties = UNDEFINED;

    /**
     * An object instance is valid against this property if its property set contains all elements in this property's array value.
     *
     * @var string[]
     */
    public $required = UNDEFINED;

    /**
     * @var Property[]
     */
    public $properties = UNDEFINED;

    /**
     * The type of the schema/property. The value MUST be one of "string", "number", "integer", "boolean", "array" or "object".
     *
     * @var string
     */
    public $type = UNDEFINED;

    /**
     * The extending format for the previously mentioned type. See Data Type Formats for further details.
     *
     * @var string
     */
    public $format = UNDEFINED;

    /**
     * Required if type is "array". Describes the type of items in the array.
     *
     * @var Items
     */
    public $items = UNDEFINED;

    /**
     * @var string Determines the format of the array if type array is used. Possible values are: csv - comma separated values foo,bar. ssv - space separated values foo bar. tsv - tab separated values foo\tbar. pipes - pipe separated values foo|bar. multi - corresponds to multiple parameter instances instead of multiple values for a single instance foo=bar&foo=baz. This is valid only for parameters in "query" or "formData". Default value is csv.
     */
    public $collectionFormat = UNDEFINED;

    /**
     * Sets a default value to the parameter. The type of the value depends on the defined type. See http://json-schema.org/latest/json-schema-validation.html#anchor101.
     */
    public $default = UNDEFINED;

    /**
     * See http://json-schema.org/latest/json-schema-validation.html#anchor17.
     *
     * @var number
     */
    public $maximum = UNDEFINED;

    /**
     * See http://json-schema.org/latest/json-schema-validation.html#anchor17.
     *
     * @var bool
     */
    public $exclusiveMaximum = UNDEFINED;

    /**
     * See http://json-schema.org/latest/json-schema-validation.html#anchor21.
     *
     * @var number
     */
    public $minimum = UNDEFINED;

    /**
     * See http://json-schema.org/latest/json-schema-validation.html#anchor21.
     *
     * @var bool
     */
    public $exclusiveMinimum = UNDEFINED;

    /**
     * See http://json-schema.org/latest/json-schema-validation.html#anchor26.
     *
     * @var int
     */
    public $maxLength = UNDEFINED;

    /**
     * See http://json-schema.org/latest/json-schema-validation.html#anchor29.
     *
     * @var int
     */
    public $minLength = UNDEFINED;

    /**
     * A string instance is considered valid if the regular expression matches the instance successfully.
     *
     * @var string
     */
    public $pattern = UNDEFINED;

    /**
     * See http://json-schema.org/latest/json-schema-validation.html#anchor42.
     *
     * @var int
     */
    public $maxItems = UNDEFINED;

    /**
     * See http://json-schema.org/latest/json-schema-validation.html#anchor45.
     *
     * @var int
     */
    public $minItems = UNDEFINED;

    /**
     * See http://json-schema.org/latest/json-schema-validation.html#anchor49.
     *
     * @var bool
     */
    public $uniqueItems = UNDEFINED;

    /**
     * See http://json-schema.org/latest/json-schema-validation.html#anchor76.
     *
     * @var array
     */
    public $enum = UNDEFINED;

    /**
     * A numeric instance is valid against "multipleOf" if the result of the division of the instance by this property's value is an integer.
     *
     * @var number
     */
    public $multipleOf = UNDEFINED;

    /**
     * Adds support for polymorphism.
     * The discriminator is an object name that is used to differentiate between other schemas which may satisfy the payload description.
     * See Composition and Inheritance for more details.
     *
     * @var Discriminator
     */
    public $discriminator = UNDEFINED;

    /**
     * Relevant only for Schema "properties" definitions.
     * Declares the property as "read only".
     * This means that it may be sent as part of a response but should not be sent as part of the request.
     * If the property is marked as readOnly being true and is in the required list, the required will take effect on the response only.
     * A property must not be marked as both readOnly and writeOnly being true.
     * Default value is false.
     *
     * @var bool
     */
    public $readOnly = UNDEFINED;

    /**
     * Relevant only for Schema "properties" definitions.
     * Declares the property as "write only".
     * Therefore, it may be sent as part of a request but should not be sent as part of the response.
     * If the property is marked as writeOnly being true and is in the required list, the required will take effect on the request only.
     * A property must not be marked as both readOnly and writeOnly being true.
     * Default value is false.
     *
     * @var bool
     */
    public $writeOnly = UNDEFINED;

    /**
     * This may be used only on properties schemas.
     * It has no effect on root schemas.
     * Adds additional metadata to describe the XML representation of this property.
     *
     * @var Xml
     */
    public $xml = UNDEFINED;

    /**
     * Additional external documentation for this schema.
     *
     * @var ExternalDocumentation
     */
    public $externalDocs = UNDEFINED;

    /**
     * A free-form property to include an example of an instance for this schema.
     * To represent examples that cannot be naturally represented in JSON or YAML, a string value can be used to contain the example with escaping where necessary.
     */
    public $example = UNDEFINED;

    /**
     * Allows sending a null value for the defined schema.
     * Default value is false.
     *
     * @var bool
     */
    public $nullable = UNDEFINED;

    /**
     * Specifies that a schema is deprecated and should be transitioned out of usage.
     * Default value is false.
     *
     * @var bool
     */
    public $deprecated = UNDEFINED;

    /**
     * An instance validates successfully against this property if it validates successfully against all schemas defined by this property's value.
     *
     * @var Schema[]
     */
    public $allOf = UNDEFINED;

    /**
     * An instance validates successfully against this property if it validates successfully against at least one schema defined by this property's value.
     *
     * @var Schema[]
     */
    public $anyOf = UNDEFINED;

    /**
     * An instance validates successfully against this property if it validates successfully against exactly one schema defined by this property's value.
     *
     * @var Schema[]
     */
    public $oneOf = UNDEFINED;

    /**
     * http://json-schema.org/latest/json-schema-validation.html#rfc.section.6.29.
     */
    public $not = UNDEFINED;

    /**
     * http://json-schema.org/latest/json-schema-validation.html#anchor64.
     *
     * @var bool|object
     */
    public $additionalProperties = UNDEFINED;

    /**
     * http://json-schema.org/latest/json-schema-validation.html#rfc.section.6.10.
     */
    public $additionalItems = UNDEFINED;

    /**
     * http://json-schema.org/latest/json-schema-validation.html#rfc.section.6.14.
     */
    public $contains = UNDEFINED;

    /**
     * http://json-schema.org/latest/json-schema-validation.html#rfc.section.6.19.
     */
    public $patternProperties = UNDEFINED;

    /**
     * http://json-schema.org/latest/json-schema-validation.html#rfc.section.6.21.
     */
    public $dependencies = UNDEFINED;

    /**
     * http://json-schema.org/latest/json-schema-validation.html#rfc.section.6.22.
     */
    public $propertyNames = UNDEFINED;

    /**
     * http://json-schema.org/latest/json-schema-validation.html#rfc.section.6.24.
     */
    public $const = UNDEFINED;

    /**
     * {@inheritdoc}
     */
    public static $_types = [
        'description' => 'string',
        'required' => '[string]',
        'format' => 'string',
        'collectionFormat' => ['csv', 'ssv', 'tsv', 'pipes', 'multi'],
        'maximum' => 'number',
        'exclusiveMaximum' => 'boolean',
        'minimum' => 'number',
        'exclusiveMinimum' => 'boolean',
        'maxLength' => 'integer',
        'minLength' => 'integer',
        'pattern' => 'string',
        'maxItems' => 'integer',
        'minItems' => 'integer',
        'uniqueItems' => 'boolean',
        'multipleOf' => 'integer',
        'allOf' => '['.Schema::class.']',
        'oneOf' => '['.Schema::class.']',
        'anyOf' => '['.Schema::class.']',
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

    /**
     * {@inheritdoc}
     */
    public static $_parents = [
        Components::class,
        Parameter::class,
        MediaType::class,
        Header::class,
    ];

    public function validate($parents = [], $skip = [], $ref = '')
    {
        if ($this->type === 'array' && $this->items === UNDEFINED) {
            Logger::notice('@OA\Items() is required when '.$this->identity().' has type "array" in '.$this->_context);

            return false;
        }

        return parent::validate($parents, $skip, $ref);
    }
}
