<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Adapter;

use RuntimeException;

/**
 * Adapter factory and registry.
 *
 * Used for registering adapters and creating instances of adapters.
 *
 * @author Woody Gilk <woody.gilk@gmail.com>
 */
class AdapterFactory
{
    /**
     * @var \Phinx\Db\Adapter\AdapterFactory|null
     */
    protected static $instance;

    /**
     * Get the factory singleton instance.
     *
     * @return \Phinx\Db\Adapter\AdapterFactory
     */
    public static function instance()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Class map of database adapters, indexed by PDO::ATTR_DRIVER_NAME.
     *
     * @var array<string, \Phinx\Db\Adapter\AdapterInterface|string>
     * @phpstan-var array<string, \Phinx\Db\Adapter\AdapterInterface|class-string<\Phinx\Db\Adapter\AdapterInterface>>
     */
    protected $adapters = [
        'mysql' => 'Phinx\Db\Adapter\MysqlAdapter',
        'pgsql' => 'Phinx\Db\Adapter\PostgresAdapter',
        'sqlite' => 'Phinx\Db\Adapter\SQLiteAdapter',
        'sqlsrv' => 'Phinx\Db\Adapter\SqlServerAdapter',
    ];

    /**
     * Class map of adapters wrappers, indexed by name.
     *
     * @var array<string, \Phinx\Db\Adapter\WrapperInterface|string>
     */
    protected $wrappers = [
        'prefix' => 'Phinx\Db\Adapter\TablePrefixAdapter',
        'proxy' => 'Phinx\Db\Adapter\ProxyAdapter',
        'timed' => 'Phinx\Db\Adapter\TimedOutputAdapter',
    ];

    /**
     * Register an adapter class with a given name.
     *
     * @param string $name Name
     * @param object|string $class Class
     * @throws \RuntimeException
     * @return $this
     */
    public function registerAdapter(string $name, $class)
    {
        if (!is_subclass_of($class, 'Phinx\Db\Adapter\AdapterInterface')) {
            throw new RuntimeException(sprintf(
                'Adapter class "%s" must implement Phinx\\Db\\Adapter\\AdapterInterface',
                is_string($class) ? $class : get_class($class)
            ));
        }
        $this->adapters[$name] = $class;

        return $this;
    }

    /**
     * Get an adapter class by name.
     *
     * @param string $name Name
     * @throws \RuntimeException
     * @return object|string
     * @phpstan-return object|class-string<\Phinx\Db\Adapter\AdapterInterface>
     */
    protected function getClass(string $name)
    {
        if (empty($this->adapters[$name])) {
            throw new RuntimeException(sprintf(
                'Adapter "%s" has not been registered',
                $name
            ));
        }

        return $this->adapters[$name];
    }

    /**
     * Get an adapter instance by name.
     *
     * @param string $name Name
     * @param array<string, mixed> $options Options
     * @return \Phinx\Db\Adapter\AdapterInterface
     */
    public function getAdapter(string $name, array $options): AdapterInterface
    {
        $class = $this->getClass($name);

        return new $class($options);
    }

    /**
     * Add or replace a wrapper with a fully qualified class name.
     *
     * @param string $name Name
     * @param object|string $class Class
     * @throws \RuntimeException
     * @return $this
     */
    public function registerWrapper(string $name, $class)
    {
        if (!is_subclass_of($class, 'Phinx\Db\Adapter\WrapperInterface')) {
            throw new RuntimeException(sprintf(
                'Wrapper class "%s" must be implement Phinx\\Db\\Adapter\\WrapperInterface',
                is_string($class) ? $class : get_class($class)
            ));
        }
        $this->wrappers[$name] = $class;

        return $this;
    }

    /**
     * Get a wrapper class by name.
     *
     * @param string $name Name
     * @throws \RuntimeException
     * @return \Phinx\Db\Adapter\WrapperInterface|string
     */
    protected function getWrapperClass(string $name)
    {
        if (empty($this->wrappers[$name])) {
            throw new RuntimeException(sprintf(
                'Wrapper "%s" has not been registered',
                $name
            ));
        }

        return $this->wrappers[$name];
    }

    /**
     * Get a wrapper instance by name.
     *
     * @param string $name Name
     * @param \Phinx\Db\Adapter\AdapterInterface $adapter Adapter
     * @return \Phinx\Db\Adapter\AdapterWrapper
     */
    public function getWrapper(string $name, AdapterInterface $adapter): AdapterWrapper
    {
        $class = $this->getWrapperClass($name);

        return new $class($adapter);
    }
}
