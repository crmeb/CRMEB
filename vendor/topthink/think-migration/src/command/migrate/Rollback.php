<?php
// +----------------------------------------------------------------------
// | TopThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangyajun <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\migration\command\migrate;

use Phinx\Migration\MigrationInterface;
use think\console\input\Option as InputOption;
use think\console\Input;
use think\console\Output;
use think\migration\command\Migrate;

class Rollback extends Migrate
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migrate:rollback')
             ->setDescription('Rollback the last or to a specific migration')
             ->addOption('--target', '-t', InputOption::VALUE_REQUIRED, 'The version number to rollback to')
             ->addOption('--date', '-d', InputOption::VALUE_REQUIRED, 'The date to rollback to')
             ->addOption('--force', '-f', InputOption::VALUE_NONE, 'Force rollback to ignore breakpoints')
             ->setHelp(<<<EOT
The <info>migrate:rollback</info> command reverts the last migration, or optionally up to a specific version

<info>php console migrate:rollback</info>
<info>php console migrate:rollback -t 20111018185412</info>
<info>php console migrate:rollback -d 20111018</info>
<info>php console migrate:rollback -v</info>

EOT
             );
    }

    /**
     * Rollback the migration.
     *
     * @param Input  $input
     * @param Output $output
     * @return void
     */
    protected function execute(Input $input, Output $output)
    {
        $version = $input->getOption('target');
        $date    = $input->getOption('date');
        $force   = !!$input->getOption('force');

        // rollback the specified environment
        $start = microtime(true);
        if (null !== $date) {
            $this->rollbackToDateTime(new \DateTime($date), $force);
        } else {
            $this->rollback($version, $force);
        }
        $end = microtime(true);

        $output->writeln('');
        $output->writeln('<comment>All Done. Took ' . sprintf('%.4fs', $end - $start) . '</comment>');
    }

    protected function rollback($version = null, $force = false)
    {
        $migrations = $this->getMigrations();
        $versionLog = $this->getVersionLog();
        $versions   = array_keys($versionLog);

        ksort($migrations);
        sort($versions);

        // Check we have at least 1 migration to revert
        if (empty($versions) || $version == end($versions)) {
            $this->output->writeln('<error>No migrations to rollback</error>');
            return;
        }

        // If no target version was supplied, revert the last migration
        if (null === $version) {
            // Get the migration before the last run migration
            $prev    = count($versions) - 2;
            $version = $prev < 0 ? 0 : $versions[$prev];
        } else {
            // Get the first migration number
            $first = $versions[0];

            // If the target version is before the first migration, revert all migrations
            if ($version < $first) {
                $version = 0;
            }
        }

        // Check the target version exists
        if (0 !== $version && !isset($migrations[$version])) {
            $this->output->writeln("<error>Target version ($version) not found</error>");
            return;
        }

        // Revert the migration(s)
        krsort($migrations);
        foreach ($migrations as $migration) {
            if ($migration->getVersion() <= $version) {
                break;
            }

            if (in_array($migration->getVersion(), $versions)) {
                if (isset($versionLog[$migration->getVersion()]) && 0 != $versionLog[$migration->getVersion()]['breakpoint'] && !$force) {
                    $this->output->writeln('<error>Breakpoint reached. Further rollbacks inhibited.</error>');
                    break;
                }
                $this->executeMigration($migration, MigrationInterface::DOWN);
            }
        }
    }

    protected function rollbackToDateTime(\DateTime $dateTime, $force = false)
    {
        $versions   = $this->getVersions();
        $dateString = $dateTime->format('YmdHis');
        sort($versions);

        $earlierVersion      = null;
        $availableMigrations = array_filter($versions, function ($version) use ($dateString, &$earlierVersion) {
            if ($version <= $dateString) {
                $earlierVersion = $version;
            }
            return $version >= $dateString;
        });

        if (count($availableMigrations) > 0) {
            if (is_null($earlierVersion)) {
                $this->output->writeln('Rolling back all migrations');
                $migration = 0;
            } else {
                $this->output->writeln('Rolling back to version ' . $earlierVersion);
                $migration = $earlierVersion;
            }
            $this->rollback($migration, $force);
        }
    }
}
