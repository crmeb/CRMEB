<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi;

use OpenApi\Annotations as OA;

/**
 * Allows to serialize/de-serialize annotations from/to JSON.
 *
 * @see https://github.com/zircote/swagger-php
 */
class Serializer
{
    private static $VALID_ANNOTATIONS = [
        OA\AdditionalProperties::class,
        OA\Components::class,
        OA\Contact::class,
        OA\Delete::class,
        OA\Discriminator::class,
        OA\Examples::class,
        OA\ExternalDocumentation::class,
        OA\Flow::class,
        OA\Get::class,
        OA\Head::class,
        OA\Header::class,
        OA\Info::class,
        OA\Items::class,
        OA\JsonContent::class,
        OA\License::class,
        OA\Link::class,
        OA\MediaType::class,
        OA\OpenApi::class,
        OA\Operation::class,
        OA\Options::class,
        OA\Parameter::class,
        OA\Patch::class,
        OA\PathItem::class,
        OA\Post::class,
        OA\Property::class,
        OA\Put::class,
        OA\RequestBody::class,
        OA\Response::class,
        OA\Schema::class,
        OA\SecurityScheme::class,
        OA\Server::class,
        OA\ServerVariable::class,
        OA\Tag::class,
        OA\Trace::class,
        OA\Xml::class,
        OA\XmlContent::class,
    ];

    public static function isValidAnnotationClass($className)
    {
        return in_array($className, static::$VALID_ANNOTATIONS);
    }

    /**
     * Serialize.
     *
     *
     * @return string
     */
    public function serialize(OA\AbstractAnnotation $annotation)
    {
        return json_encode($annotation);
    }

    /**
     * Deserialize a string.
     *
     * @param $jsonString
     * @param $className
     *
     * @throws \Exception
     *
     * @return OA\AbstractAnnotation
     */
    public function deserialize($jsonString, $className)
    {
        if (!$this->isValidAnnotationClass($className)) {
            throw new \Exception($className.' is not defined in OpenApi PHP Annotations');
        }

        return $this->doDeserialize(json_decode($jsonString), $className);
    }

    /**
     * Deserialize a file.
     *
     * @param $filename
     * @param $className
     *
     * @throws \Exception
     *
     * @return OA\AbstractAnnotation
     */
    public function deserializeFile($filename, $className = 'OpenApi\Annotations\OpenApi')
    {
        if (!$this->isValidAnnotationClass($className)) {
            throw new \Exception($className.' is not defined in OpenApi PHP Annotations');
        }

        return $this->doDeserialize(json_decode(file_get_contents($filename)), $className);
    }

    /**
     * Do deserialization.
     *
     * @param string $class the class name of annotation
     *
     * @return OA\AbstractAnnotation
     */
    protected function doDeserialize(\stdClass $c, $class)
    {
        $annotation = new $class([]);
        foreach ($c as $property => $value) {
            if ($property === '$ref') {
                $property = 'ref';
            }

            if (substr($property, 0, 2) === 'x-') {
                if ($annotation->x === UNDEFINED) {
                    $annotation->x = [];
                }
                $custom = substr($property, 2);
                $annotation->x[$custom] = $value;
            } else {
                $annotation->$property = $this->doDeserializeProperty($annotation, $property, $value);
            }
        }

        return $annotation;
    }

    /**
     * Deserialize the annotation's property.
     *
     * @param string $property
     */
    protected function doDeserializeProperty(OA\AbstractAnnotation $annotation, $property, $value)
    {
        // property is primitive type
        if (array_key_exists($property, $annotation::$_types)) {
            return $this->doDeserializeBaseProperty($annotation::$_types[$property], $value);
        }

        // property is embedded annotation
        // note: this does not support custom nested annotation classes
        foreach ($annotation::$_nested as $nestedClass => $declaration) {
            // property is an annotation
            if (is_string($declaration) && $declaration === $property) {
                if (is_object($value)) {
                    return $this->doDeserialize($value, $nestedClass);
                } else {
                    return $value;
                }
            }

            // property is an annotation array
            if (is_array($declaration) && count($declaration) === 1 && $declaration[0] === $property) {
                $annotationArr = [];
                foreach ($value as $v) {
                    $annotationArr[] = $this->doDeserialize($v, $nestedClass);
                }

                return $annotationArr;
            }

            // property is an annotation hash map
            if (is_array($declaration) && count($declaration) === 2 && $declaration[0] === $property) {
                $key = $declaration[1];
                $annotationHash = [];
                foreach ($value as $k => $v) {
                    $annotation = $this->doDeserialize($v, $nestedClass);
                    $annotation->$key = $k;
                    $annotationHash[$k] = $annotation;
                }

                return $annotationHash;
            }
        }

        return $value;
    }

    /**
     * Deserialize base annotation property.
     *
     * @param string $type  The property type
     * @param mixed  $value The value to deserialization
     *
     * @return array|OA\AbstractAnnotation
     */
    protected function doDeserializeBaseProperty($type, $value)
    {
        $isAnnotationClass = is_string($type) && is_subclass_of(trim($type, '[]'), OA\AbstractAnnotation::class);

        if ($isAnnotationClass) {
            $isArray = strpos($type, '[') === 0 && substr($type, -1) === ']';

            if ($isArray) {
                $annotationArr = [];
                $class = trim($type, '[]');

                foreach ($value as $v) {
                    $annotationArr[] = $this->doDeserialize($v, $class);
                }

                return $annotationArr;
            }

            return $this->doDeserialize($value, $type);
        }

        return $value;
    }
}
