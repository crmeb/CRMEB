<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
namespace think\console\command\optimize;

use Exception;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\db\PDOConnection;
use Throwable;

class Schema extends Command
{
    use Discoverable;

    protected function configure()
    {
        $this->setName('optimize:schema')
            ->addArgument('dir', Argument::OPTIONAL, 'dir name .')
            ->addOption('connection', null, Option::VALUE_REQUIRED, 'connection name .')
            ->addOption('table', null, Option::VALUE_REQUIRED, 'table name .')
            ->setDescription('Build database schema cache.');
    }

    protected function execute(Input $input, Output $output)
    {
        try {
            if ($table = $input->hasOption('table')) {
                $this->cacheTable($table, $input->getOption('connection'));
            } else {
                $dirs = ((array) $input->getArgument('dir')) ?: $this->getDefaultDirs();
                foreach ($dirs as $dir) {
                    $this->cacheModel($dir);
                }
            }
        } catch (Throwable $e) {
            return $output->error($e->getMessage());
        }

        $output->info('Succeed!');
    }

    protected function buildModelSchema(string $class): void
    {
        $reflect = new ReflectionClass($class);
        if ($reflect->isAbstract() || ! $reflect->isSubclassOf('\think\Model')) {
            return;
        }
        try {
            /** @var \think\Model $model */
            $model = new $class;
            $connection = $model->db()->getConnection();
            if ($connection instanceof PDOConnection) {
                $table = $model->getTable();
                //预读字段信息
                $connection->getSchemaInfo($table, true);
            }
        } catch (Exception $e) {
        }
    }

    protected function buildDataBaseSchema(PDOConnection $connection, array $tables, string $dbName): void
    {
        foreach ($tables as $table) {
            //预读字段信息
            $connection->getSchemaInfo("{$dbName}.{$table}", true);
        }
    }

    /**
     * 缓存表
     */
    private function cacheTable(string $table, ?string $connectionName = null): void
    {
        $connection = $this->app->db->connect($connectionName);
        if (! $connection instanceof PDOConnection) {
            throw new InvalidArgumentException('only PDO connection support schema cache!');
        }

        if (str_contains($table, '.')) {
            [$dbName, $table] = explode('.', $table);
        } else {
            $dbName = $connection->getConfig('database');
        }

        if ($table == '*') {
            $table = $connection->getTables($dbName);
        }

        $this->buildDataBaseSchema($connection, (array) $table, $dbName);
    }

    /**
     * 缓存模型
     */
    private function cacheModel(?string $dir = null): void
    {
        if ($dir) {
            $modelDir = $this->app->getAppPath() . $dir . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR;
            $namespace = 'app\\' . $dir;
        } else {
            $modelDir = $this->app->getAppPath() . 'model' . DIRECTORY_SEPARATOR;
            $namespace = 'app';
        }

        if (! is_dir($modelDir)) {
            throw new InvalidArgumentException("{$modelDir} directory does not exist");
        }

        /** @var SplFileInfo[] $iterator */
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($modelDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $fileInfo) {
            $relativePath = substr($fileInfo->getRealPath(), strlen($modelDir));
            if (! str_ends_with($relativePath, '.php')) {
                continue;
            }
            // 去除 .php
            $relativePath = substr($relativePath, 0, -4);

            $class = '\\' . $namespace . '\\model\\' . str_replace('/', '\\', $relativePath);
            if (! class_exists($class)) {
                continue;
            }

            $this->buildModelSchema($class);
        }
    }

    /**
     * 获取默认目录名
     * @return array<int, ?string>
     */
    private function getDefaultDirs(): array
    {
        // 包含默认的模型目录
        $dirs = [null];
        if ($this->isInstalledMultiApp()) {
            $dirs = array_merge($dirs, $this->discoveryMultiAppDirs('model'));
        }
        return $dirs;
    }
}
