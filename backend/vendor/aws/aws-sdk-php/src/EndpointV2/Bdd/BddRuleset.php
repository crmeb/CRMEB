<?php

namespace Aws\EndpointV2\Bdd;

use Aws\EndpointV2\Ruleset\RulesetParameter;
use Aws\EndpointV2\Ruleset\RulesetStandardLibrary;
use Aws\Exception\UnresolvedEndpointException;

/**
 * Parsed form of the `smithy.rules#endpointBdd` trait. Reuses
 * {@see RulesetParameter} so parameter coercion and validation behave
 * identically to the tree-based ruleset.
 *
 * Instances are immutable after construction. A single instance is shared
 * across all requests for a given service/client pair.
 *
 * @internal
 */
final class BddRuleset
{
    private const REQUIRED_FIELDS = [
        'conditions', 'results', 'nodes', 'root', 'nodeCount'
    ];

    /** @var array<string, RulesetParameter> */
    private array $parameters;

    /** @var array<int, array> */
    private array $conditions;

    /** @var array<int, array> */
    private array $results;

    /** @var int[] Flat triples: [condIdx, hi, lo, condIdx, hi, lo, ...] */
    private array $nodes;

    private int $root;

    public readonly RulesetStandardLibrary $standardLibrary;

    public function __construct(array $definition, array $partitions)
    {
        foreach (self::REQUIRED_FIELDS as $key) {
            if (!array_key_exists($key, $definition)) {
                throw new UnresolvedEndpointException(
                    "Endpoint BDD definition is missing `{$key}`."
                );
            }
        }

        $this->parameters = $this->buildParameters($definition['parameters'] ?? []);
        $this->conditions = $definition['conditions'];
        $this->results = $definition['results'];
        $this->root = (int) $definition['root'];
        $nodeCount = $definition['nodeCount'];
        $this->nodes = BddNodeDecoder::decode(
            (string) $definition['nodes'],
            $nodeCount
        );

        $this->standardLibrary = new RulesetStandardLibrary($partitions);
    }

    /**
     * @return array<string, RulesetParameter>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return array<int, array>
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @return array<int, array>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @return int[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function getRoot(): int
    {
        return $this->root;
    }

    /**
     * Applies parameter defaults and type checks. Mirrors the tree ruleset so
     * services migrating from one shape to the other see identical input
     * validation behavior.
     */
    public function applyParameterDefaults(array &$inputParameters): void
    {
        foreach ($this->parameters as $name => $param) {
            $value = $inputParameters[$name] ?? null;

            if (is_null($value) && !is_null($param->getDefault())) {
                $inputParameters[$name] = $param->getDefault();
            } elseif (!is_null($value)) {
                $param->validateInputParam($value);
            }
        }
    }

    private function buildParameters(array $parameters): array
    {
        $built = [];
        foreach ($parameters as $name => $definition) {
            $built[$name] = new RulesetParameter($name, $definition);
        }
        return $built;
    }
}
