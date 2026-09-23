<?php

namespace Aws\EndpointV2;

use Aws\EndpointV2\Bdd\BddRuleset;
use Aws\EndpointV2\Ruleset\Ruleset;

/**
 * Provides Endpoint-related artifacts used for endpoint resolution
 * and testing.
 */
class EndpointDefinitionProvider
{
    /**
     * Returns a parsed ruleset for the service — either a {@see BddRuleset}
     * if a compiled BDD is shipped, or a {@see Ruleset} otherwise. Selection
     * is driven by which file is packaged, so callers get a typed object
     * rather than having to inspect the raw array.
     *
     * @param $service
     * @param $apiVersion
     * @param array $partitions
     * @param null $baseDir
     *
     * @return Ruleset|BddRuleset
     */
    public static function getParsedRuleset(
        $service,
        $apiVersion,
        array $partitions,
        $baseDir = null
    ): BddRuleset|Ruleset
    {
        $bdd = self::getEndpointBdd($service, $apiVersion, $baseDir, false);
        if ($bdd !== null) {
            return new BddRuleset($bdd, $partitions);
        }
        return new Ruleset(
            self::getEndpointRuleset($service, $apiVersion, $baseDir),
            $partitions
        );
    }

    public static function getEndpointRuleset($service, $apiVersion, $baseDir = null)
    {
        return self::getData($service, $apiVersion, 'ruleset', $baseDir);
    }

    /**
     * Returns the parsed endpoint BDD for a service, or null when
     * `$throwIfMissing` is false and no BDD file is packaged.
     */
    public static function getEndpointBdd(
        $service,
        $apiVersion,
        $baseDir = null,
        $throwIfMissing = true
    ) {
        return self::getData($service, $apiVersion, 'bdd', $baseDir, $throwIfMissing);
    }

    public static function getEndpointTests($service, $apiVersion, $baseDir = null)
    {
        return self::getData($service, $apiVersion, 'tests', $baseDir);
    }

    public static function getPartitions()
    {
        $basePath = __DIR__ . '/../data';
        $file = '/partitions.json';

        if (file_exists($basePath . $file . '.php')) {
           return require($basePath . $file . '.php');
        } else {
            return json_decode(file_get_contents($basePath . $file));
        }
    }

    private static function getData($service, $apiVersion, $type, $baseDir, $throwIfMissing = true)
    {
        $basePath = $baseDir ?:  __DIR__ . '/../data';
        $serviceDir = $basePath . "/{$service}";
        if (!is_dir($serviceDir)) {
            if (!$throwIfMissing) {
                return null;
            }
            throw new \InvalidArgumentException(
                'Invalid service name.'
            );
        }

        if ($apiVersion === 'latest') {
            $apiVersion = self::getLatest($service);
        }

        $rulesetPath = $serviceDir . '/' . $apiVersion;
        if (!is_dir($rulesetPath)) {
            if (!$throwIfMissing) {
                return null;
            }
            throw new \InvalidArgumentException(
                'Invalid api version.'
            );
        }

        $fileName = self::getFileName($type);

        if (file_exists($rulesetPath . $fileName . '.json.php')) {
            return require($rulesetPath . $fileName . '.json.php');
        } elseif (file_exists($rulesetPath . $fileName . '.json')) {
            return json_decode(file_get_contents($rulesetPath . $fileName . '.json'), true);
        }

        if (!$throwIfMissing) {
            return null;
        }

        throw new \InvalidArgumentException(
            'Specified ' . $type . ' endpoint file for ' . $service
            . ' with api version ' . $apiVersion . ' does not exist.'
        );
    }

    private static function getFileName($type): string
    {
        return match ($type) {
            'tests' => '/endpoint-tests-1',
            'bdd' => '/endpoint-bdd',
            default => '/endpoint-rule-set-1',
        };
    }

    private static function getLatest($service)
    {
        $manifest = \Aws\manifest();
        return $manifest[$service]['versions']['latest'];
    }
}
