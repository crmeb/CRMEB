<?php

namespace Aws\EndpointV2\Bdd;

use Aws\EndpointV2\Ruleset\RulesetEndpoint;
use Aws\EndpointV2\Ruleset\RulesetStandardLibrary;
use Aws\Exception\UnresolvedEndpointException;

/**
 * Turns a BDD result reference into a {@see RulesetEndpoint} or throws the
 * appropriate {@see UnresolvedEndpointException}. The behavior matches the
 * tree evaluator's endpoint and error rules so downstream middleware cannot
 * tell which evaluator produced the result.
 *
 * Result index `0` is reserved for the implicit no-match rule defined by the
 * trait. The BDD may also reach a no-match via either terminal reference,
 * which is handled by {@see resolveNoMatch()}.
 *
 * @internal
 */
final class BddResultResolver
{
    public function __construct(
        private readonly BddRuleset $ruleset
    ) {
    }

    /**
     * @throws UnresolvedEndpointException
     */
    public function resolve(int $resultIndex, array $inputParameters): RulesetEndpoint
    {
        if ($resultIndex === 0) {
            $this->resolveNoMatch($inputParameters);
        }

        // The serialized `results` array omits the implicit no-match rule,
        // so defined result index N lives at array offset N - 1.
        $results = $this->ruleset->getResults();
        $result = $results[$resultIndex - 1] ?? null;

        if ($result === null) {
            throw new UnresolvedEndpointException(sprintf(
                'Endpoint BDD referenced unknown result index %d.',
                $resultIndex
            ));
        }

        if (isset($result['error'])) {
            $this->throwError($result, $inputParameters);
        }

        if (!isset($result['endpoint'])) {
            throw new UnresolvedEndpointException(
                'Endpoint BDD result is missing an `endpoint` or `error` block.'
            );
        }

        return $this->buildEndpoint($result['endpoint'], $inputParameters);
    }

    /**
     * @throws UnresolvedEndpointException
     */
    public function resolveNoMatch(array $inputParameters): never
    {
        throw new UnresolvedEndpointException(
            'Unable to resolve an endpoint using the provider arguments: '
            . json_encode($inputParameters)
        );
    }

    /**
     * @throws UnresolvedEndpointException
     */
    private function throwError(array $result, array $inputParameters): never
    {
        $message = $this->ruleset->standardLibrary->resolveValue(
            $result['error'],
            $inputParameters
        );
        throw new UnresolvedEndpointException((string) $message);
    }

    private function buildEndpoint(array $endpoint, array $inputParameters): RulesetEndpoint
    {
        $library = $this->ruleset->standardLibrary;

        $url = $library->resolveValue($endpoint['url'], $inputParameters);
        $properties = isset($endpoint['properties'])
            ? $this->resolveProperties($endpoint['properties'], $inputParameters, $library)
            : null;
        $headers = isset($endpoint['headers'])
            ? $this->resolveHeaders($endpoint['headers'], $inputParameters, $library)
            : null;

        return new RulesetEndpoint($url, $properties, $headers);
    }

    private function resolveProperties(
        $properties,
        array $inputParameters,
        RulesetStandardLibrary $library
    ) {
        if (is_array($properties)) {
            $resolved = [];
            foreach ($properties as $key => $value) {
                $resolved[$key] = $this->resolveProperties(
                    $value,
                    $inputParameters,
                    $library
                );
            }
            return $resolved;
        }

        // Inline some of the isTemplate check here to avoid unnecessary resolution attempts on simple strings
        if (is_string($properties) && str_contains($properties, '{') && $library->isTemplate($properties)) {
            return $library->resolveTemplateString($properties, $inputParameters);
        }

        return $properties;
    }

    private function resolveHeaders(
        array $headers,
        array $inputParameters,
        RulesetStandardLibrary $library
    ): array {
        $resolved = [];
        foreach ($headers as $name => $values) {
            $resolvedValues = [];
            foreach ($values as $value) {
                $resolvedValues[] = $library->resolveValue($value, $inputParameters);
            }
            $resolved[$name] = $resolvedValues;
        }
        return $resolved;
    }
}
