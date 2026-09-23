<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
namespace think\migration;

use InvalidArgumentException;
use Phinx\Config\Config;
use Phinx\Db\Adapter\AdapterFactory;

abstract class Command extends \think\console\Command
{
    protected $adapter;

    public function getAdapter()
    {
        if (isset($this->adapter)) {
            return $this->adapter;
        }

        $options = $this->getDbConfig();

        $adapter = AdapterFactory::instance()->getAdapter($options['adapter'], $options);

        if ($adapter->hasOption('table_prefix') || $adapter->hasOption('table_suffix')) {
            $adapter = AdapterFactory::instance()->getWrapper('prefix', $adapter);
        }

        $adapter->setInput($this->input);
        $adapter->setOutput($this->output);

        $this->adapter = $adapter;

        return $adapter;
    }

    /**
     * 获取数据库配置
     * @return array
     */
    protected function getDbConfig(): array
    {
        $default = $this->app->config->get('database.default');

        $config = $this->app->config->get("database.connections.{$default}");

        if (0 == $config['deploy']) {
            $dbConfig = [
                'adapter'      => $config['type'],
                'host'         => $config['hostname'],
                'name'         => $config['database'],
                'user'         => $config['username'],
                'pass'         => $config['password'],
                'port'         => $config['hostport'],
                'charset'      => $config['charset'],
                'suffix'       => $config['suffix'] ?? '',
                'table_prefix' => $config['prefix'],
            ];
        } else {
            $dbConfig = [
                'adapter'      => explode(',', $config['type'])[0],
                'host'         => explode(',', $config['hostname'])[0],
                'name'         => explode(',', $config['database'])[0],
                'user'         => explode(',', $config['username'])[0],
                'pass'         => explode(',', $config['password'])[0],
                'port'         => explode(',', $config['hostport'])[0],
                'charset'      => explode(',', $config['charset'])[0],
                'suffix'       => explode(',', $config['suffix'] ?? '')[0],
                'table_prefix' => explode(',', $config['prefix'])[0],
            ];
        }

        $table = $this->app->config->get('database.migration_table', 'migrations');

        $dbConfig['migration_table'] = $dbConfig['table_prefix'] . $table;
        $dbConfig['version_order']   = Config::VERSION_ORDER_CREATION_TIME;

        return $dbConfig;
    }

    protected function verifyMigrationDirectory(string $path)
    {
        if (!is_dir($path)) {
            throw new InvalidArgumentException(sprintf('Migration directory "%s" does not exist', $path));
        }

        if (!is_writable($path)) {
            throw new InvalidArgumentException(sprintf('Migration directory "%s" is not writable', $path));
        }
    }
}
