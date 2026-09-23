<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Config;

use ArrayAccess;
use Psr\Container\ContainerInterface;

/**
 * Phinx configuration interface.
 *
 * @package Phinx
 * @author Woody Gilk
 */
interface ConfigInterface extends ArrayAccess
{
    /**
     * Returns the configuration for each environment.
     *
     * This method returns <code>null</code> if no environments exist.
     *
     * @return array|null
     */
    public function getEnvironments(): ?array;

    /**
     * Returns the configuration for a given environment.
     *
     * This method returns <code>null</code> if the specified environment
     * doesn't exist.
     *
     * @param string $name Environment Name
     * @return array|null
     */
    public function getEnvironment(string $name): ?array;

    /**
     * Does the specified environment exist in the configuration file?
     *
     * @param string $name Environment Name
     * @return bool
     */
    public function hasEnvironment(string $name): bool;

    /**
     * Gets the default environment name.
     *
     * @throws \RuntimeException
     * @return string
     */
    public function getDefaultEnvironment(): string;

    /**
     * Get the aliased value from a supplied alias.
     *
     * @param string $alias Alias
     * @return string|null
     */
    public function getAlias(string $alias): ?string;

    /**
     * Get all the aliased values.
     *
     * @return string[]
     */
    public function getAliases(): array;

    /**
     * Gets the config file path.
     *
     * @return string|null
     */
    public function getConfigFilePath(): ?string;

    /**
     * Gets the paths to search for migration files.
     *
     * @return string[]
     */
    public function getMigrationPaths(): array;

    /**
     * Gets the paths to search for seed files.
     *
     * @return string[]
     */
    public function getSeedPaths(): array;

    /**
     * Get the template file name.
     *
     * @return string|false
     */
    public function getTemplateFile();

    /**
     * Get the template class name.
     *
     * @return string|false
     */
    public function getTemplateClass();

    /**
     * Get the template style to use, either change or up_down.
     *
     * @return string
     */
    public function getTemplateStyle(): string;

    /**
     * Get the user-provided container for instantiating seeds
     *
     * @return \Psr\Container\ContainerInterface|null
     */
    public function getContainer(): ?ContainerInterface;

    /**
     * Get the data domain array.
     *
     * @return array
     */
    public function getDataDomain(): array;

    /**
     * Get the version order.
     *
     * @return string
     */
    public function getVersionOrder(): string;

    /**
     * Is version order creation time?
     *
     * @return bool
     */
    public function isVersionOrderCreationTime(): bool;

    /**
     * Get the bootstrap file path
     *
     * @return string|false
     */
    public function getBootstrapFile();

    /**
     * Gets the base class name for migrations.
     *
     * @param bool $dropNamespace Return the base migration class name without the namespace.
     * @return string
     */
    public function getMigrationBaseClassName(bool $dropNamespace = true): string;

    /**
     * Gets the base class name for seeders.
     *
     * @param bool $dropNamespace Return the base seeder class name without the namespace.
     * @return string
     */
    public function getSeedBaseClassName(bool $dropNamespace = true): string;

    /**
     * Get the seeder template file name or null if not set.
     *
     * @return string|null
     */
    public function getSeedTemplateFile(): ?string;
}
