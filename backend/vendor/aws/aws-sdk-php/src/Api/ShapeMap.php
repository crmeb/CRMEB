<?php
namespace Aws\Api;

/**
 * Builds shape based on shape references.
 */
class ShapeMap implements \ArrayAccess
{
    /** @var array */
    private $definitions;

    /** @var Shape[] */
    private $simple;

    /**
     * @param array $shapeModels Associative array of shape definitions.
     */
    public function __construct(array $shapeModels)
    {
        $this->definitions = $shapeModels;
    }

    /**
     * Get an array of shape names.
     *
     * @return array
     */
    public function getShapeNames()
    {
        return array_keys($this->definitions);
    }

    /**
     * Resolve a shape reference
     *
     * @param array $shapeRef Shape reference shape
     *
     * @return Shape
     * @throws \InvalidArgumentException
     */
    public function resolve(array $shapeRef)
    {
        $shape = $shapeRef['shape'];

        if (!isset($this->definitions[$shape])) {
            throw new \InvalidArgumentException('Shape not found: ' . $shape);
        }

        $isSimple = count($shapeRef) == 1;
        if ($isSimple && isset($this->simple[$shape])) {
            return $this->simple[$shape];
        }

        $shapeDefinition = $this->definitions[$shape];
        $definition = $shapeRef + $shapeDefinition;
        // Property to know whether the locationName was set at member level
        // or the structure level.
        if (isset($shapeDefinition['locationName'])) {
            $definition['locationNameAtStructureLevel'] = true;
        }

        $definition['name'] = $definition['shape'];
        if (isset($definition['shape'])) {
            unset($definition['shape']);
        }

        $result = Shape::create($definition, $this);

        if ($isSimple) {
            $this->simple[$shape] = $result;
        }

        return $result;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->definitions[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->definitions[$offset] ?? null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws \BadMethodCallException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \BadMethodCallException(
            'ShapeMap is read-only and cannot be modified.'
        );
    }

    /**
     * @param mixed $offset
     * @throws \BadMethodCallException
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new \BadMethodCallException(
            'ShapeMap is read-only and cannot be modified.'
        );
    }
}
