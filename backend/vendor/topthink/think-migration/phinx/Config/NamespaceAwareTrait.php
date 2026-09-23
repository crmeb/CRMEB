<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Config;

/**
 * Trait implemented NamespaceAwareInterface.
 *
 * @package Phinx\Config
 * @author Andrey N. Mokhov
 */
trait NamespaceAwareTrait
{
    /**
     * Gets the paths to search for migration files.
     *
     * @return string[]
     */
    abstract public function getMigrationPaths(): array;

    /**
     * Gets the paths to search for seed files.
     *
     * @return string[]
     */
    abstract public function getSeedPaths(): array;

    /**
     * Search $needle in $haystack and return key associate with him.
     *
     * @param string $needle Needle
     * @param string[] $haystack Haystack
     * @return string|null
     */
    protected function searchNamespace(string $needle, array $haystack): ?string
    {
        $needle = realpath($needle);
        $haystack = array_map('realpath', $haystack);

        $key = array_search($needle, $haystack, true);

        return is_string($key) ? trim($key, '\\') : null;
    }

    /**
     * Get Migration Namespace associated with path.
     *
     * @param string $path Path
     * @return string|null
     */
    public function getMigrationNamespaceByPath(string $path): ?string
    {
        $paths = $this->getMigrationPaths();

        return $this->searchNamespace($path, $paths);
    }

    /**
     * Get Seed Namespace associated with path.
     *
     * @param string $path Path
     * @return string|null
     */
    public function getSeedNamespaceByPath(string $path): ?string
    {
        $paths = $this->getSeedPaths();

        return $this->searchNamespace($path, $paths);
    }
}
