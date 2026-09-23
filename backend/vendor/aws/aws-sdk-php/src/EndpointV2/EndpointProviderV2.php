<?php

namespace Aws\EndpointV2;

use Aws\EndpointV2\Bdd\BddEvaluator;
use Aws\EndpointV2\Bdd\BddResultResolver;
use Aws\EndpointV2\Bdd\BddRuleset;
use Aws\EndpointV2\Ruleset\Ruleset;
use Aws\EndpointV2\Ruleset\RulesetEndpoint;
use Aws\Exception\UnresolvedEndpointException;
use Aws\LruArrayCache;

/**
 * Given a service's ruleset and client-provided input parameters, provides
 * either an object reflecting the properties of a resolved endpoint,
 * or throws an error.
 *
 * Supports both the classic decision tree ruleset (`endpointRuleSet` trait)
 * and the binary decision diagram ruleset (`endpointBdd` trait). A raw
 * definition array is always interpreted as a tree ruleset; to use a BDD,
 * construct a {@see BddRuleset} and hand it in directly.
 */
class EndpointProviderV2
{
    /** @var Ruleset|null */
    private $ruleset;

    /** @var BddRuleset|null */
    private $bddRuleset;

    /** @var BddEvaluator|null */
    private $bddEvaluator;

    /** @var LruArrayCache */
    private $cache;

    /**
     * @param array|Ruleset|BddRuleset $ruleset A parsed ruleset instance, or
     *     a raw tree ruleset array from the service model.
     * @param array $partitions AWS partitions data. Ignored when $ruleset is
     *     already a parsed instance, since the instance carries its own
     *     partition data.
     */
    public function __construct($ruleset, array $partitions)
    {
        if ($ruleset instanceof BddRuleset) {
            $this->bddRuleset = $ruleset;
            $this->bddEvaluator = new BddEvaluator(
                $ruleset,
                new BddResultResolver($ruleset)
            );
        } elseif ($ruleset instanceof Ruleset) {
            $this->ruleset = $ruleset;
        } elseif (is_array($ruleset)) {
            $this->ruleset = new Ruleset($ruleset, $partitions);
        } else {
            throw new \InvalidArgumentException(
                'EndpointProviderV2 expects an array, Ruleset, or BddRuleset'
                . ' but received ' . (is_object($ruleset)
                    ? get_class($ruleset)
                    : gettype($ruleset))
            );
        }

        $this->cache = new LruArrayCache(100);
    }

    /**
     * Returns the parsed tree ruleset for services using the legacy
     * `endpointRuleSet` trait. Returns null when the provider was built from
     * an `endpointBdd` trait.
     *
     * @return Ruleset|null
     */
    public function getRuleset()
    {
        return $this->ruleset;
    }

    /**
     * Returns the parsed BDD ruleset for services using the `endpointBdd`
     * trait, or null when the provider was built from a tree ruleset.
     */
    public function getBddRuleset(): ?BddRuleset
    {
        return $this->bddRuleset;
    }

    /**
     * Given input parameters, determines the correct endpoint or an error
     * to be thrown for a given request.
     *
     * @return RulesetEndpoint
     * @throws UnresolvedEndpointException
     */
    public function resolveEndpoint(array $inputParameters)
    {
        $hashedParams = $this->hashInputParameters($inputParameters);
        $match = $this->cache->get($hashedParams);

        if (!is_null($match)) {
            return $match;
        }

        $endpoint = $this->bddEvaluator !== null
            ? $this->bddEvaluator->evaluate($inputParameters)
            : $this->ruleset->evaluate($inputParameters);

        // This condition just applies to endpoint resolution
        // through the decision tree evaluation process.
        if ($endpoint === false) {
            throw new UnresolvedEndpointException(
                'Unable to resolve an endpoint using the provider arguments: '
                . json_encode($inputParameters)
            );
        }

        $this->cache->set($hashedParams, $endpoint);

        return $endpoint;
    }

    private function hashInputParameters($inputParameters)
    {
        return md5(serialize($inputParameters));
    }

    /**
     * @return array
     */
    public function getActiveParameters(): array
    {
        if ($this->bddRuleset !== null) {
            return $this->bddRuleset->getParameters();
        }

        return $this->ruleset->getParameters();
    }
}
