<?php

namespace Aws\EndpointV2\Bdd;

use Aws\EndpointV2\Ruleset\RulesetEndpoint;
use Aws\Exception\UnresolvedEndpointException;

/**
 * Walks an endpoint BDD to produce a {@see RulesetEndpoint} or throw an
 * {@see UnresolvedEndpointException}.
 *
 * The traversal follows the smithy rules-engine reference algorithm:
 * each reference is either a node pointer (optionally complemented with a
 * negative sign), one of the two terminals (`1` / `-1`), or a result pointer
 * (offset by {@see self::RESULT_OFFSET}). Nodes are laid out as contiguous
 * triples `[conditionIndex, highRef, lowRef]` inside the ruleset's flat
 * node array.
 *
 * @internal
 */
final class BddEvaluator
{
    private const TERMINAL_TRUE = 1;
    private const TERMINAL_FALSE = -1;
    private const RESULT_OFFSET = 100_000_000;

    public function __construct(
        private readonly BddRuleset $ruleset,
        private readonly BddResultResolver $resultResolver
    ) {
    }

    /**
     * Resolves an endpoint from the BDD for the given input parameters.
     *
     * @throws UnresolvedEndpointException when resolution reaches the no-match
     *     terminal or an error result rule.
     */
    public function evaluate(array $inputParameters): RulesetEndpoint
    {
        $this->ruleset->applyParameterDefaults($inputParameters);

        $nodes = $this->ruleset->getNodes();
        $conditions = $this->ruleset->getConditions();
        $library = $this->ruleset->standardLibrary;

        $ref = $this->ruleset->getRoot();

        while (true) {
            if ($ref >= self::RESULT_OFFSET) {
                return $this->resultResolver->resolve(
                    $ref - self::RESULT_OFFSET,
                    $inputParameters
                );
            }

            if ($ref === self::TERMINAL_TRUE || $ref === self::TERMINAL_FALSE) {
                // Throws exception
                $this->resultResolver->resolveNoMatch($inputParameters);
            }

            $isComplement = $ref < 0;
            $base = ($isComplement ? -$ref : $ref) * 3 - 3;

            $condIndex = $nodes[$base];
            $value = $library->callFunction(
                $conditions[$condIndex],
                $inputParameters
            );

            $condResult = ($value !== null && $value !== false);
            // Complement edges invert the high/low selection without
            // duplicating nodes in the BDD.
            $ref = ($condResult xor $isComplement)
                ? $nodes[$base + 1]
                : $nodes[$base + 2];
        }
    }
}
