<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Config;

use Closure;
use InvalidArgumentException;
use Phinx\Db\Adapter\SQLiteAdapter;
use Phinx\Util\Util;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use UnexpectedValueException;

/**
 * Phinx configuration class.
 *
 * @package Phinx
 * @author Rob Morgan
 */
class Config implements ConfigInterface, NamespaceAwareInterface
{
    use NamespaceAwareTrait;

    /**
     * The value that identifies a version order by creation time.
     */
    public const VERSION_ORDER_CREATION_TIME = 'creation';

    /**
     * The value that identifies a version order by execution time.
     */
    public const VERSION_ORDER_EXECUTION_TIME = 'execution';

    public const TEMPLATE_STYLE_CHANGE = 'change';
    public const TEMPLATE_STYLE_UP_DOWN = 'up_down';

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var string|null
     */
    protected $configFilePath;

    /**
     * @param array $configArray Config array
     * @param string|null $configFilePath Config file path
     */
    public function __construct(array $configArray, ?string $configFilePath = null)
    {
        $this->configFilePath = $configFilePath;
        $this->values = $this->replaceTokens($configArray);

        if (isset($this->values['feature_flags'])) {
            FeatureFlags::setFlagsFromConfig($this->values['feature_flags']);
        }
    }

    /**
     * Create a new instance of the config class using a Yaml file path.
     *
     * @param string $configFilePath Path to the Yaml File
     * @throws \RuntimeException
     * @return \Phinx\Config\ConfigInterface
     */
    public static function fromYaml(string $configFilePath): ConfigInterface
    {
        if (!class_exists('Symfony\\Component\\Yaml\\Yaml', true)) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Missing yaml parser, symfony/yaml package is not installed.');
            // @codeCoverageIgnoreEnd
        }

        $configFile = file_get_contents($configFilePath);
        $configArray = Yaml::parse($configFile);

        if (!is_array($configArray)) {
            throw new RuntimeException(sprintf(
                'File \'%s\' must be valid YAML',
                $configFilePath
            ));
        }

        return new static($configArray, $configFilePath);
    }

    /**
     * Create a new instance of the config class using a JSON file path.
     *
     * @param string $configFilePath Path to the JSON File
     * @throws \RuntimeException
     * @return \Phinx\Config\ConfigInterface
     */
    public static function fromJson(string $configFilePath): ConfigInterface
    {
        if (!function_exists('json_decode')) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Need to install JSON PHP extension to use JSON config');
            // @codeCoverageIgnoreEnd
        }

        $configArray = json_decode(file_get_contents($configFilePath), true);
        if (!is_array($configArray)) {
            throw new RuntimeException(sprintf(
                'File \'%s\' must be valid JSON',
                $configFilePath
            ));
        }

        return new static($configArray, $configFilePath);
    }

    /**
     * Create a new instance of the config class using a PHP file path.
     *
     * @param string $configFilePath Path to the PHP File
     * @throws \RuntimeException
     * @return \Phinx\Config\ConfigInterface
     */
    public static function fromPhp(string $configFilePath): ConfigInterface
    {
        ob_start();
        /** @noinspection PhpIncludeInspection */
        $configArray = include $configFilePath;

        // Hide console output
        ob_end_clean();

        if (!is_array($configArray)) {
            throw new RuntimeException(sprintf(
                'PHP file \'%s\' must return an array',
                $configFilePath
            ));
        }

        return new static($configArray, $configFilePath);
    }

    /**
     * @inheritDoc
     */
    public function getEnvironments(): ?array
    {
        if (isset($this->values['environments'])) {
            $environments = [];
            foreach ($this->values['environments'] as $key => $value) {
                if (is_array($value)) {
                    $environments[$key] = $value;
                }
            }

            return $environments;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getEnvironment(string $name): ?array
    {
        $environments = $this->getEnvironments();

        if (isset($environments[$name])) {
            if (
                isset($this->values['environments']['default_migration_table'])
                && !isset($environments[$name]['migration_table'])
            ) {
                $environments[$name]['migration_table'] =
                    $this->values['environments']['default_migration_table'];
            }

            if (
                isset($environments[$name]['adapter'])
                && $environments[$name]['adapter'] === 'sqlite'
                && !empty($environments[$name]['memory'])
            ) {
                $environments[$name]['name'] = SQLiteAdapter::MEMORY;
            }

            return $this->parseAgnosticDsn($environments[$name]);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function hasEnvironment(string $name): bool
    {
        return $this->getEnvironment($name) !== null;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultEnvironment(): string
    {
        // The $PHINX_ENVIRONMENT variable overrides all other default settings
        $env = getenv('PHINX_ENVIRONMENT');
        if (!empty($env)) {
            if ($this->hasEnvironment($env)) {
                return $env;
            }

            throw new RuntimeException(sprintf(
                'The environment configuration (read from $PHINX_ENVIRONMENT) for \'%s\' is missing',
                $env
            ));
        }

        // deprecated: to be removed 0.13
        if (isset($this->values['environments']['default_database'])) {
            trigger_error('default_database in the config has been deprecated since 0.12, use default_environment instead.', E_USER_DEPRECATED);
            $this->values['environments']['default_environment'] = $this->values['environments']['default_database'];
        }

        // if the user has configured a default environment then use it,
        // providing it actually exists!
        if (isset($this->values['environments']['default_environment'])) {
            if ($this->hasEnvironment($this->values['environments']['default_environment'])) {
                return $this->values['environments']['default_environment'];
            }

            throw new RuntimeException(sprintf(
                'The environment configuration for \'%s\' is missing',
                $this->values['environments']['default_environment']
            ));
        }

        // else default to the first available one
        if (is_array($this->getEnvironments()) && count($this->getEnvironments()) > 0) {
            $names = array_keys($this->getEnvironments());

            return $names[0];
        }

        throw new RuntimeException('Could not find a default environment');
    }

    /**
     * @inheritDoc
     */
    public function getAlias($alias): ?string
    {
        return !empty($this->values['aliases'][$alias]) ? $this->values['aliases'][$alias] : null;
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return !empty($this->values['aliases']) ? $this->values['aliases'] : [];
    }

    /**
     * @inheritDoc
     */
    public function getConfigFilePath(): ?string
    {
        return $this->configFilePath;
    }

    /**
     * @inheritDoc
     * @throws \UnexpectedValueException
     */
    public function getMigrationPaths(): array
    {
        if (!isset($this->values['paths']['migrations'])) {
            throw new UnexpectedValueException('Migrations path missing from config file');
        }

        if (is_string($this->values['paths']['migrations'])) {
            $this->values['paths']['migrations'] = [$this->values['paths']['migrations']];
        }

        return $this->values['paths']['migrations'];
    }

    /**
     * @inheritDoc
     * @throws \UnexpectedValueException
     */
    public function getSeedPaths(): array
    {
        if (!isset($this->values['paths']['seeds'])) {
            throw new UnexpectedValueException('Seeds path missing from config file');
        }

        if (is_string($this->values['paths']['seeds'])) {
            $this->values['paths']['seeds'] = [$this->values['paths']['seeds']];
        }

        return $this->values['paths']['seeds'];
    }

    /**
     * @inheritdoc
     */
    public function getMigrationBaseClassName(bool $dropNamespace = true): string
    {
        $className = !isset($this->values['migration_base_class']) ? 'Phinx\Migration\AbstractMigration' : $this->values['migration_base_class'];

        return $dropNamespace ? (substr(strrchr($className, '\\'), 1) ?: $className) : $className;
    }

    /**
     * @inheritdoc
     */
    public function getSeedBaseClassName(bool $dropNamespace = true): string
    {
        $className = !isset($this->values['seed_base_class']) ? 'Phinx\Seed\AbstractSeed' : $this->values['seed_base_class'];

        return $dropNamespace ? substr(strrchr($className, '\\'), 1) : $className;
    }

    /**
     * @inheritdoc
     */
    public function getTemplateFile()
    {
        if (!isset($this->values['templates']['file'])) {
            return false;
        }

        return $this->values['templates']['file'];
    }

    /**
     * @inheritdoc
     */
    public function getTemplateClass()
    {
        if (!isset($this->values['templates']['class'])) {
            return false;
        }

        return $this->values['templates']['class'];
    }

    /**
     * @inheritdoc
     */
    public function getTemplateStyle(): string
    {
        if (!isset($this->values['templates']['style'])) {
            return self::TEMPLATE_STYLE_CHANGE;
        }

        return $this->values['templates']['style'] === self::TEMPLATE_STYLE_UP_DOWN ? self::TEMPLATE_STYLE_UP_DOWN : self::TEMPLATE_STYLE_CHANGE;
    }

    /**
     * @inheritdoc
     */
    public function getDataDomain(): array
    {
        if (!isset($this->values['data_domain'])) {
            return [];
        }

        return $this->values['data_domain'];
    }

    /**
     * @inheritDoc
     */
    public function getContainer(): ?ContainerInterface
    {
        if (!isset($this->values['container'])) {
            return null;
        }

        return $this->values['container'];
    }

    /**
     * @inheritdoc
     */
    public function getVersionOrder(): string
    {
        if (!isset($this->values['version_order'])) {
            return self::VERSION_ORDER_CREATION_TIME;
        }

        return $this->values['version_order'];
    }

    /**
     * @inheritdoc
     */
    public function isVersionOrderCreationTime(): bool
    {
        $versionOrder = $this->getVersionOrder();

        return $versionOrder == self::VERSION_ORDER_CREATION_TIME;
    }

    /**
     * @inheritdoc
     */
    public function getBootstrapFile()
    {
        if (!isset($this->values['paths']['bootstrap'])) {
            return false;
        }

        return $this->values['paths']['bootstrap'];
    }

    /**
     * Replace tokens in the specified array.
     *
     * @param array $arr Array to replace
     * @return array
     */
    protected function replaceTokens(array $arr): array
    {
        // Get environment variables
        // Depending on configuration of server / OS and variables_order directive,
        // environment variables either end up in $_SERVER (most likely) or $_ENV,
        // so we search through both
        $tokens = [];
        foreach (array_merge($_ENV, $_SERVER) as $varname => $varvalue) {
            if (strpos($varname, 'PHINX_') === 0) {
                $tokens['%%' . $varname . '%%'] = $varvalue;
            }
        }

        // Phinx defined tokens (override env tokens)
        $tokens['%%PHINX_CONFIG_PATH%%'] = $this->getConfigFilePath();
        $tokens['%%PHINX_CONFIG_DIR%%'] = $this->getConfigFilePath() !== null ? dirname($this->getConfigFilePath()) : '';

        // Recurse the array and replace tokens
        return $this->recurseArrayForTokens($arr, $tokens);
    }

    /**
     * Recurse an array for the specified tokens and replace them.
     *
     * @param array $arr Array to recurse
     * @param string[] $tokens Array of tokens to search for
     * @return array
     */
    protected function recurseArrayForTokens(array $arr, array $tokens): array
    {
        $out = [];
        foreach ($arr as $name => $value) {
            if (is_array($value)) {
                $out[$name] = $this->recurseArrayForTokens($value, $tokens);
                continue;
            }
            if (is_string($value)) {
                foreach ($tokens as $token => $tval) {
                    $value = str_replace($token, $tval ?? '', $value);
                }
                $out[$name] = $value;
                continue;
            }
            $out[$name] = $value;
        }

        return $out;
    }

    /**
     * Parse a database-agnostic DSN into individual options.
     *
     * @param array<string, mixed> $options Options
     * @return array<string, mixed>
     */
    protected function parseAgnosticDsn(array $options): array
    {
        $parsed = Util::parseDsn($options['dsn'] ?? '');
        if ($parsed) {
            unset($options['dsn']);
        }

        $options += $parsed;

        return $options;
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed $id ID
     * @param mixed $value Value
     * @return void
     */
    public function offsetSet($id, $value): void
    {
        $this->values[$id] = $value;
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed $id ID
     * @throws \InvalidArgumentException
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($id)
    {
        if (!array_key_exists($id, $this->values)) {
            throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }

        return $this->values[$id] instanceof Closure ? $this->values[$id]($this) : $this->values[$id];
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed $id ID
     * @return bool
     */
    public function offsetExists($id): bool
    {
        return isset($this->values[$id]);
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed $id ID
     * @return void
     */
    public function offsetUnset($id): void
    {
        unset($this->values[$id]);
    }

    /**
     * @inheritdoc
     */
    public function getSeedTemplateFile(): ?string
    {
        return $this->values['templates']['seedFile'] ?? null;
    }
}
