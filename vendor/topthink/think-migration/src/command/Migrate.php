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

namespace think\migration\command;

use Phinx\Db\Adapter\AdapterFactory;
use Phinx\Db\Adapter\ProxyAdapter;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\MigrationInterface;
use Phinx\Util\Util;
use think\console\Input;
use think\console\input\Option as InputOption;
use think\console\Output;
use think\facade\Env;
use think\migration\Command;
use think\migration\Migrator;

abstract class Migrate extends Command
{
    /**
     * @var array
     */
    protected $migrations;

    public function __construct($name = null)
    {

        parent::__construct($name);

        $this->addOption('--config', null, InputOption::VALUE_REQUIRED, 'The database config name', 'database');
    }

    /**
     * 初始化
     * @param Input  $input  An InputInterface instance
     * @param Output $output An OutputInterface instance
     */
    protected function initialize(Input $input, Output $output)
    {
        $this->config = $input->getOption('config');
    }

    protected function getPath()
    {
        return $this->getConfig('path', Env::get('root_path') . 'database') . DIRECTORY_SEPARATOR . 'migrations' . ('database' !== $this->config ? DIRECTORY_SEPARATOR . $this->config : '');
    }

    protected function executeMigration(MigrationInterface $migration, $direction = MigrationInterface::UP)
    {
        $this->output->writeln('');
        $this->output->writeln(' ==' . ' <info>' . $migration->getVersion() . ' ' . $migration->getName() . ':</info>' . ' <comment>' . (MigrationInterface::UP === $direction ? 'migrating' : 'reverting') . '</comment>');

        // Execute the migration and log the time elapsed.
        $start = microtime(true);

        $startTime = time();
        $direction = (MigrationInterface::UP === $direction) ? MigrationInterface::UP : MigrationInterface::DOWN;
        $migration->setAdapter($this->getAdapter());

        // begin the transaction if the adapter supports it
        if ($this->getAdapter()->hasTransactions()) {
            $this->getAdapter()->beginTransaction();
        }

        // Run the migration
        if (method_exists($migration, MigrationInterface::CHANGE)) {
            if (MigrationInterface::DOWN === $direction) {
                // Create an instance of the ProxyAdapter so we can record all
                // of the migration commands for reverse playback
                /** @var ProxyAdapter $proxyAdapter */
                $proxyAdapter = AdapterFactory::instance()->getWrapper('proxy', $this->getAdapter());
                $migration->setAdapter($proxyAdapter);
                /** @noinspection PhpUndefinedMethodInspection */
                $migration->change();
                $proxyAdapter->executeInvertedCommands();
                $migration->setAdapter($this->getAdapter());
            } else {
                /** @noinspection PhpUndefinedMethodInspection */
                $migration->change();
            }
        } else {
            $migration->{$direction}();
        }

        // commit the transaction if the adapter supports it
        if ($this->getAdapter()->hasTransactions()) {
            $this->getAdapter()->commitTransaction();
        }

        // Record it in the database
        $this->getAdapter()
            ->migrated($migration, $direction, date('Y-m-d H:i:s', $startTime), date('Y-m-d H:i:s', time()));

        $end = microtime(true);

        $this->output->writeln(' ==' . ' <info>' . $migration->getVersion() . ' ' . $migration->getName() . ':</info>' . ' <comment>' . (MigrationInterface::UP === $direction ? 'migrated' : 'reverted') . ' ' . sprintf('%.4fs', $end - $start) . '</comment>');
    }

    protected function getVersionLog()
    {
        return $this->getAdapter()->getVersionLog();
    }

    protected function getVersions()
    {
        return $this->getAdapter()->getVersions();
    }

    protected function getMigrations()
    {
        if (null === $this->migrations) {
            $phpFiles = glob($this->getPath() . DIRECTORY_SEPARATOR . '*.php', defined('GLOB_BRACE') ? GLOB_BRACE : 0);

            // filter the files to only get the ones that match our naming scheme
            $fileNames = [];
            /** @var Migrator[] $versions */
            $versions = [];

            foreach ($phpFiles as $filePath) {
                if (Util::isValidMigrationFileName(basename($filePath))) {
                    $version = Util::getVersionFromFileName(basename($filePath));

                    if (isset($versions[$version])) {
                        throw new \InvalidArgumentException(sprintf('Duplicate migration - "%s" has the same version as "%s"', $filePath, $versions[$version]->getVersion()));
                    }

                    // convert the filename to a class name
                    $class = Util::mapFileNameToClassName(basename($filePath));

                    if (isset($fileNames[$class])) {
                        throw new \InvalidArgumentException(sprintf('Migration "%s" has the same name as "%s"', basename($filePath), $fileNames[$class]));
                    }

                    $fileNames[$class] = basename($filePath);

                    // load the migration file
                    /** @noinspection PhpIncludeInspection */
                    require_once $filePath;
                    if (!class_exists($class)) {
                        throw new \InvalidArgumentException(sprintf('Could not find class "%s" in file "%s"', $class, $filePath));
                    }

                    // instantiate it
                    $migration = new $class($version, $this->input, $this->output);

                    if (!($migration instanceof AbstractMigration)) {
                        throw new \InvalidArgumentException(sprintf('The class "%s" in file "%s" must extend \Phinx\Migration\AbstractMigration', $class, $filePath));
                    }

                    $versions[$version] = $migration;
                }
            }

            ksort($versions);
            $this->migrations = $versions;
        }

        return $this->migrations;
    }
}
