<?php
namespace Aws\Api;

/**
 * Represents a structure shape and resolve member shape references.
 */
class StructureShape extends Shape
{
    /**
     * @var Shape[]
     */
    private $members;

    public function __construct(array $definition, ShapeMap $shapeMap)
    {
        $definition['type'] = 'structure';

        if (!isset($definition['members'])) {
            $definition['members'] = [];
        }

        parent::__construct($definition, $shapeMap);
    }

    /**
     * Gets a list of all members
     *
     * @return Shape[]
     */
    public function getMembers()
    {
        if (empty($this->members)) {
            $this->generateMembersHash();
        }

        return $this->members;
    }

    /**
     * Check if a specific member exists by name.
     *
     * @param string $name Name of the member to check
     *
     * @return bool
     */
    public function hasMember($name)
    {
        return isset($this->definition['members'][$name]);
    }

    /**
     * Retrieve a member by name.
     *
     * @param string $name Name of the member to retrieve
     *
     * @return Shape
     * @throws \InvalidArgumentException if the member is not found.
     */
    public function getMember($name)
    {
        $members = $this->getMembers();

        if (!isset($members[$name])) {
            throw new \InvalidArgumentException('Unknown member ' . $name);
        }

        return $members[$name];
    }

    /**
     * Used to look up the shape's original definition.
     * ShapeMap::resolve() merges properties from both
     * member and target shape definitions, causing certain
     * properties like `locationName` to be overwritten.
     *
     * @return ShapeMap
     * @internal This method is for internal use only and should not be used
     * by external code. It may be changed or removed without notice.
     */
    public function getShapeMap(): ShapeMap
    {
        return $this->shapeMap;
    }

    /**
     * Used to look up a shape's original definition.
     *
     * @param string $name
     *
     * @return array|null
     */
    public function getOriginalDefinition(string $name): ?array
    {
        return $this->shapeMap[$name] ?? null;
    }

    private function generateMembersHash()
    {
        $this->members = [];

        foreach ($this->definition['members'] as $name => $definition) {
            $this->members[$name] = $this->shapeFor($definition);
        }
    }
}
