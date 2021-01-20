<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Annotations;

use Exception;
use JsonSerializable;
use OpenApi\Analyser;
use OpenApi\Context;
use OpenApi\Logger;
use stdClass;
use Symfony\Component\Yaml\Yaml;

/**
 * The openapi annotation base class.
 */
abstract class AbstractAnnotation implements JsonSerializable
{
    /**
     * While the OpenAPI Specification tries to accommodate most use cases, additional data can be added to extend the specification at certain points.
     * For further details see https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.0.md#specificationExtensions
     * The keys inside the array will be prefixed with `x-`.
     *
     * @var array
     */
    public $x = UNDEFINED;

    /**
     * @var Context
     */
    public $_context;

    /**
     * Annotations that couldn't be merged by mapping or postprocessing.
     *
     * @var array
     */
    public $_unmerged = [];

    /**
     * The properties which are required by [the spec](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.0.md).
     *
     * @var array
     */
    public static $_required = [];

    /**
     * Specify the type of the property.
     * Examples:
     *   'name' => 'string' // a string
     *   'required' => 'boolean', // true or false
     *   'tags' => '[string]', // array containing strings
     *   'in' => ["query", "header", "path", "formData", "body"] // must be one on these
     *   'oneOf' => [Schema::class] // array of schema objects.
     *
     * @var array
     */
    public static $_types = [];

    /**
     * Declarative mapping of Annotation types to properties.
     * Examples:
     *   Info::clas => 'info', // Set @OA\Info annotation as the info property.
     *   Parameter::clas => ['parameters'],  // Append @OA\Parameter annotations the parameters array.
     *   PathItem::clas => ['paths', 'path'],  // Append @OA\PathItem annotations the paths array and use path as key.
     *
     * @var array
     */
    public static $_nested = [];

    /**
     * Reverse mapping of $_nested with the allowed parent annotations.
     *
     * @var string[]
     */
    public static $_parents = [];

    /**
     * List of properties are blacklisted from the JSON output.
     *
     * @var array
     */
    public static $_blacklist = ['_context', '_unmerged'];

    /**
     * @param array $properties
     */
    public function __construct($properties)
    {
        if (isset($properties['_context'])) {
            $this->_context = $properties['_context'];
            unset($properties['_context']);
        } elseif (Analyser::$context) {
            $this->_context = Analyser::$context;
        } else {
            $this->_context = Context::detect(1);
        }
        if ($this->_context->is('annotations') === false) {
            $this->_context->annotations = [];
        }
        $this->_context->annotations[] = $this;
        $nestedContext = new Context(['nested' => $this], $this->_context);
        foreach ($properties as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
                if (is_array($value)) {
                    foreach ($value as $key => $annotation) {
                        if (is_object($annotation) && $annotation instanceof AbstractAnnotation) {
                            $this->$property[$key] = $this->nested($annotation, $nestedContext);
                        }
                    }
                }
            } elseif ($property !== 'value') {
                $this->$property = $value;
            } elseif (is_array($value)) {
                $annotations = [];
                foreach ($value as $annotation) {
                    if (is_object($annotation) && $annotation instanceof AbstractAnnotation) {
                        $annotations[] = $annotation;
                    } else {
                        Logger::notice('Unexpected field in '.$this->identity().' in '.$this->_context);
                    }
                }
                $this->merge($annotations);
            } elseif (is_object($value)) {
                $this->merge([$value]);
            } else {
                Logger::notice('Unexpected parameter in '.$this->identity());
            }
        }
    }

    public function __get($property)
    {
        $properties = get_object_vars($this);
        Logger::notice('Property "'.$property.'" doesn\'t exist in a '.$this->identity().', existing properties: "'.implode('", "', array_keys($properties)).'" in '.$this->_context);
    }

    public function __set($property, $value)
    {
        $fields = get_object_vars($this);
        foreach (static::$_blacklist as $_property) {
            unset($fields[$_property]);
        }
        Logger::notice('Unexpected field "'.$property.'" for '.$this->identity().', expecting "'.implode('", "', array_keys($fields)).'" in '.$this->_context);
        $this->$property = $value;
    }

    /**
     * Merge given annotations to their mapped properties configured in static::$_nested.
     *
     * Annotations that couldn't be merged are added to the _unmerged array.
     *
     * @param AbstractAnnotation[] $annotations
     * @param bool                 $ignore      Ignore unmerged annotations
     *
     * @return AbstractAnnotation[] The unmerged annotations
     */
    public function merge($annotations, $ignore = false)
    {
        $unmerged = [];
        $nestedContext = new Context(['nested' => $this], $this->_context);

        foreach ($annotations as $annotation) {
            $mapped = false;
            if ($details = static::matchNested(get_class($annotation))) {
                $property = $details->value;
                if (is_array($property)) {
                    $property = $property[0];
                    if ($this->$property === UNDEFINED) {
                        $this->$property = [];
                    }
                    $this->$property[] = $this->nested($annotation, $nestedContext);
                    $mapped = true;
                } elseif ($this->$property === UNDEFINED) {
                    // ignore duplicate nested if only one expected
                    $this->$property = $this->nested($annotation, $nestedContext);
                    $mapped = true;
                }
            }
            if (!$mapped) {
                $unmerged[] = $annotation;
            }
        }
        if (!$ignore) {
            foreach ($unmerged as $annotation) {
                $this->_unmerged[] = $this->nested($annotation, $nestedContext);
            }
        }

        return $unmerged;
    }

    /**
     * Merge the properties from the given object into this annotation.
     * Prevents overwriting properties that are already configured.
     *
     * @param object $object
     */
    public function mergeProperties($object)
    {
        $defaultValues = get_class_vars(get_class($this));
        $currentValues = get_object_vars($this);
        foreach ($object as $property => $value) {
            if ($property === '_context') {
                continue;
            }
            if ($currentValues[$property] === $defaultValues[$property]) { // Overwrite default values
                $this->$property = $value;
                continue;
            }
            if ($property === '_unmerged') {
                $this->_unmerged = array_merge($this->_unmerged, $value);
                continue;
            }
            if ($currentValues[$property] !== $value) { // New value is not the same?
                if ($defaultValues[$property] === $value) { // but is the same as the default?
                    continue; // Keep current, no notice
                }
                $identity = method_exists($object, 'identity') ? $object->identity() : get_class($object);
                $context1 = $this->_context;
                $context2 = property_exists($object, '_context') ? $object->_context : 'unknown';
                if (is_object($this->$property) && $this->{$property} instanceof AbstractAnnotation) {
                    $context1 = $this->$property->_context;
                }
                Logger::warning('Multiple definitions for '.$identity.'->'.$property."\n     Using: ".$context1."\n  Skipping: ".$context2);
            }
        }
    }

    /**
     * Generate the documentation in YAML format.
     *
     * @return string
     */
    public function toYaml($flags = null)
    {
        if ($flags === null) {
            $flags = Yaml::DUMP_OBJECT_AS_MAP ^ Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE;
        }

        return Yaml::dump(json_decode($this->toJson(0)), 10, 2, $flags);
    }

    /**
     * Generate the documentation in YAML format.
     *
     * @return string
     */
    public function toJson($flags = null)
    {
        if ($flags === null) {
            $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        }

        return json_encode($this, $flags);
    }

    public function __debugInfo()
    {
        $properties = [];
        foreach (get_object_vars($this) as $property => $value) {
            if ($value !== UNDEFINED) {
                $properties[$property] = $value;
            }
        }

        return $properties;
    }

    /**
     * Customize the way json_encode() renders the annotations.
     */
    public function jsonSerialize()
    {
        $data = new stdClass();

        // Strip undefined values.
        foreach (get_object_vars($this) as $property => $value) {
            if ($value !== UNDEFINED) {
                $data->$property = $value;
            }
        }

        // Strip properties that are for internal (swagger-php) use.
        foreach (static::$_blacklist as $property) {
            unset($data->$property);
        }

        // Correct empty array to empty objects.
        foreach (static::$_types as $property => $type) {
            if ($type === 'object' && is_array($data->$property) && empty($data->$property)) {
                $data->$property = new stdClass;
            }
        }

        // Inject vendor properties.
        unset($data->x);
        if (is_array($this->x)) {
            foreach ($this->x as $property => $value) {
                $prefixed = 'x-'.$property;
                $data->$prefixed = $value;
            }
        }

        // Map nested keys
        foreach (static::$_nested as $nested) {
            if (is_string($nested) || count($nested) === 1) {
                continue;
            }
            $property = $nested[0];
            if ($this->$property === UNDEFINED) {
                continue;
            }
            $keyField = $nested[1];
            $object = new stdClass();
            foreach ($this->$property as $key => $item) {
                if (is_numeric($key) === false && is_array($item)) {
                    $object->$key = $item;
                } else {
                    $key = $item->$keyField;
                    if ($key !== UNDEFINED && empty($object->$key)) {
                        if ($item instanceof JsonSerializable) {
                            $object->$key = $item->jsonSerialize();
                        } else {
                            $object->$key = $item;
                        }
                        unset($object->$key->$keyField);
                    }
                }
            }
            $data->$property = $object;
        }

        // $ref
        if (isset($data->ref)) {
            // OAS 3.0 does not allow $ref to have siblings: http://spec.openapis.org/oas/v3.0.3#fixed-fields-18
            $data = (object) ['$ref' => $data->ref];
        }

        return $data;
    }

    /**
     * Validate annotation tree, and log notices & warnings.
     *
     * @param array $parents the path of annotations above this annotation in the tree
     * @param array $skip    (prevent stack overflow, when traversing an infinite dependency graph)
     *
     * @throws Exception
     *
     * @return bool
     */
    public function validate($parents = [], $skip = [], $ref = '')
    {
        if (in_array($this, $skip, true)) {
            return true;
        }
        $valid = true;

        // Report orphaned annotations
        foreach ($this->_unmerged as $annotation) {
            if (!is_object($annotation)) {
                Logger::notice('Unexpected type: "'.gettype($annotation).'" in '.$this->identity().'->_unmerged, expecting a Annotation object');
                break;
            }

            $class = get_class($annotation);
            if ($details = static::matchNested($class)) {
                $property = $details->value;
                if (is_array($property)) {
                    Logger::notice('Only one '.Logger::shorten(get_class($annotation)).'() allowed for '.$this->identity().' multiple found, skipped: '.$annotation->_context);
                } else {
                    Logger::notice('Only one '.Logger::shorten(get_class($annotation)).'() allowed for '.$this->identity()." multiple found in:\n    Using: ".$this->$property->_context."\n  Skipped: ".$annotation->_context);
                }
            } elseif ($annotation instanceof AbstractAnnotation) {
                $message = 'Unexpected '.$annotation->identity();
                if ($class::$_parents) {
                    $message .= ', expected to be inside '.implode(', ', Logger::shorten($class::$_parents));
                }
                Logger::notice($message.' in '.$annotation->_context);
            }
            $valid = false;
        }

        // Report conflicting key
        foreach (static::$_nested as $annotationClass => $nested) {
            if (is_string($nested) || count($nested) === 1) {
                continue;
            }
            $property = $nested[0];
            if ($this->$property === UNDEFINED) {
                continue;
            }
            $keys = [];
            $keyField = $nested[1];
            foreach ($this->$property as $key => $item) {
                if (is_array($item) && is_numeric($key) === false) {
                    Logger::notice($this->identity().'->'.$property.' is an object literal, use nested '.Logger::shorten($annotationClass).'() annotation(s) in '.$this->_context);
                    $keys[$key] = $item;
                } elseif ($item->$keyField === UNDEFINED) {
                    Logger::warning($item->identity().' is missing key-field: "'.$keyField.'" in '.$item->_context);
                } elseif (isset($keys[$item->$keyField])) {
                    Logger::warning('Multiple '.$item->_identity([]).' with the same '.$keyField.'="'.$item->$keyField."\":\n  ".$item->_context."\n  ".$keys[$item->$keyField]->_context);
                } else {
                    $keys[$item->$keyField] = $item;
                }
            }
        }
        if (property_exists($this, 'ref') && $this->ref !== UNDEFINED) {
            if (substr($this->ref, 0, 2) === '#/' && count($parents) > 0 && $parents[0] instanceof OpenApi) {
                // Internal reference
                try {
                    $parents[0]->ref($this->ref);
                } catch (Exception $exception) {
                    Logger::notice($exception->getMessage().' for '.$this->identity().' in '.$this->_context);
                }
            }
        } else {
            // Report missing required fields (when not a $ref)
            foreach (static::$_required as $property) {
                if ($this->$property === UNDEFINED) {
                    $message = 'Missing required field "'.$property.'" for '.$this->identity().' in '.$this->_context;
                    foreach (static::$_nested as $class => $nested) {
                        $nestedProperty = is_array($nested) ? $nested[0] : $nested;
                        if ($property === $nestedProperty) {
                            if ($this instanceof OpenApi) {
                                $message = 'Required '.Logger::shorten($class).'() not found';
                            } elseif (is_array($nested)) {
                                $message = $this->identity().' requires at least one '.Logger::shorten($class).'() in '.$this->_context;
                            } else {
                                $message = $this->identity().' requires a '.Logger::shorten($class).'() in '.$this->_context;
                            }
                            break;
                        }
                    }
                    Logger::notice($message);
                }
            }
        }

        // Report invalid types
        foreach (static::$_types as $property => $type) {
            $value = $this->$property;
            if ($value === UNDEFINED || $value === null) {
                continue;
            }
            if (is_string($type)) {
                if ($this->validateType($type, $value) === false) {
                    $valid = false;
                    Logger::notice($this->identity().'->'.$property.' is a "'.gettype($value).'", expecting a "'.$type.'" in '.$this->_context);
                }
            } elseif (is_array($type)) { // enum?
                if (in_array($value, $type) === false) {
                    Logger::notice($this->identity().'->'.$property.' "'.$value.'" is invalid, expecting "'.implode('", "', $type).'" in '.$this->_context);
                }
            } else {
                throw new Exception('Invalid '.get_class($this).'::$_types['.$property.']');
            }
        }
        $parents[] = $this;

        return self::_validate($this, $parents, $skip, $ref) ? $valid : false;
    }

    /**
     * Recursively validate all annotation properties.
     *
     * @param array|object $fields
     * @param array        $parents the path of annotations above this annotation in the tree
     * @param array [      $skip]   Array with objects which are already validated
     *
     * @return bool
     */
    private static function _validate($fields, $parents, $skip, $baseRef)
    {
        $valid = true;
        $blacklist = [];
        if (is_object($fields)) {
            if (in_array($fields, $skip, true)) {
                return true;
            }
            $skip[] = $fields;
            $blacklist = property_exists($fields, '_blacklist') ? $fields::$_blacklist : [];
        }

        foreach ($fields as $field => $value) {
            if ($value === null || is_scalar($value) || in_array($field, $blacklist)) {
                continue;
            }
            $ref = $baseRef !== '' ? $baseRef.'/'.urlencode((string) $field) : urlencode((string) $field);
            if (is_object($value)) {
                if (method_exists($value, 'validate')) {
                    if (!$value->validate($parents, $skip, $ref)) {
                        $valid = false;
                    }
                } elseif (!self::_validate($value, $parents, $skip, $ref)) {
                    $valid = false;
                }
            } elseif (is_array($value) && !self::_validate($value, $parents, $skip, $ref)) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Return a identity for easy debugging.
     * Example: "@OA\Get(path="/pets")".
     *
     * @return string
     */
    public function identity()
    {
        return $this->_identity([]);
    }

    /**
     * Find matching nested details.
     *
     * @param string $class the class to match
     *
     * @return null|object key/value object or `null`
     */
    public static function matchNested($class)
    {
        if (array_key_exists($class, static::$_nested)) {
            return (object) ['key' => $class, 'value' => static::$_nested[$class]];
        }

        $parent = $class;
        // only consider the immediate OpenApi parent
        while (0 !== strpos($parent, 'OpenApi\\Annotations\\') && $parent = get_parent_class($parent)) {
            if ($kvp = static::matchNested($parent)) {
                return $kvp;
            }
        }

        return null;
    }

    /**
     * Helper for generating the identity().
     *
     * @param array $properties
     *
     * @return string
     */
    protected function _identity($properties)
    {
        $fields = [];
        foreach ($properties as $property) {
            $value = $this->$property;
            if ($value !== null && $value !== UNDEFINED) {
                $fields[] = $property.'='.(is_string($value) ? '"'.$value.'"' : $value);
            }
        }

        return Logger::shorten(get_class($this)).'('.implode(',', $fields).')';
    }

    /**
     * Validates the matching of the property value to a annotation type.
     *
     * @param string $type  The annotations property type
     * @param mixed  $value The property value
     *
     * @throws \Exception
     */
    private function validateType($type, $value): bool
    {
        if (substr($type, 0, 1) === '[' && substr($type, -1) === ']') { // Array of a specified type?
            if ($this->validateType('array', $value) === false) {
                return false;
            }
            $itemType = substr($type, 1, -1);
            foreach ($value as $i => $item) {
                if ($this->validateType($itemType, $item) === false) {
                    return false;
                }
            }

            return true;
        }

        if (is_subclass_of($type, AbstractAnnotation::class)) {
            $type = 'object';
        }

        return $this->validateDefaultTypes($type, $value);
    }

    /**
     * Validates default Open Api types.
     *
     * @param string $type  The property type
     * @param mixed  $value The value to validate
     *
     * @throws \Exception
     */
    private function validateDefaultTypes($type, $value): bool
    {
        switch ($type) {
            case 'string':
                return is_string($value);
            case 'boolean':
                return is_bool($value);
            case 'integer':
                return is_int($value);
            case 'number':
                return is_numeric($value);
            case 'object':
                return is_object($value);
            case 'array':
                return $this->validateArrayType($value);
            case 'scheme':
                return in_array($value, ['http', 'https', 'ws', 'wss'], true);
            default:
                throw new Exception('Invalid type "'.$type.'"');
        }
    }

    /**
     * Validate array type.
     */
    private function validateArrayType($value): bool
    {
        if (is_array($value) === false) {
            return false;
        }
        $count = 0;
        foreach ($value as $i => $item) {
            //not a array, but a hash/map
            if ($count !== $i) {
                return false;
            }
            $count++;
        }

        return true;
    }

    /**
     * Wrap the context with a reference to the annotation it is nested in.
     *
     * @param AbstractAnnotation $annotation
     * @param Context            $nestedContext
     *
     * @return AbstractAnnotation
     */
    private function nested($annotation, $nestedContext)
    {
        if (property_exists($annotation, '_context') && $annotation->_context === $this->_context) {
            $annotation->_context = $nestedContext;
        }

        return $annotation;
    }
}
