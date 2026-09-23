<?php
// +----------------------------------------------------------------------
// | TopThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangyajun <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\migration\command\migrate;

use think\console\input\Option as InputOption;
use think\console\Input;
use think\console\Output;
use think\migration\command\Migrate;

class Status extends Migrate
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migrate:status')
             ->setDescription('Show migration status')
             ->addOption('--format', '-f', InputOption::VALUE_REQUIRED, 'The output format: text or json. Defaults to text.')
             ->setHelp(<<<EOT
The <info>migrate:status</info> command prints a list of all migrations, along with their current status

<info>php think migrate:status</info>
<info>php think migrate:status -f json</info>
EOT
             );
    }

    /**
     * Show the migration status.
     *
     * @param Input  $input
     * @param Output $output
     * @return integer 0 if all migrations are up, or an error code
     */
    protected function execute(Input $input, Output $output)
    {
        $format = $input->getOption('format');

        if (null !== $format) {
            $output->writeln('<info>using format</info> ' . $format);
        }

        // print the status
        return $this->printStatus($format);
    }

    protected function printStatus($format = null)
    {
        $output     = $this->output;
        $migrations = [];
        if (count($this->getMigrations())) {
            // TODO - rewrite using Symfony Table Helper as we already have this library
            // included and it will fix formatting issues (e.g drawing the lines)
            $output->writeln('');
            $output->writeln(' Status  Migration ID    Started              Finished             Migration Name ');
            $output->writeln('----------------------------------------------------------------------------------');

            $versions      = $this->getVersionLog();
            $maxNameLength = $versions ? max(array_map(function ($version) {
                return strlen($version['migration_name']);
            }, $versions)) : 0;

            foreach ($this->getMigrations() as $migration) {
                $version = array_key_exists($migration->getVersion(), $versions) ? $versions[$migration->getVersion()] : false;
                if ($version) {
                    $status = '     <info>up</info> ';
                } else {
                    $status = '   <error>down</error> ';
                    $version = [];
                    $version['start_time'] = $version['end_time'] = $version['breakpoint'] = '';
                }
                $maxNameLength = max($maxNameLength, strlen($migration->getName()));

                $output->writeln(sprintf('%s %14.0f  %19s  %19s  <comment>%s</comment>', $status, $migration->getVersion(), $version['start_time'], $version['end_time'], $migration->getName()));

                if ($version && $version['breakpoint']) {
                    $output->writeln('         <error>BREAKPOINT SET</error>');
                }

                $migrations[] = [
                    'migration_status' => trim(strip_tags($status)),
                    'migration_id'     => sprintf('%14.0f', $migration->getVersion()),
                    'migration_name'   => $migration->getName()
                ];
                unset($versions[$migration->getVersion()]);
            }

            if (count($versions)) {
                foreach ($versions as $missing => $version) {
                    $output->writeln(sprintf('     <error>up</error>  %14.0f  %19s  %19s  <comment>%s</comment>  <error>** MISSING **</error>', $missing, $version['start_time'], $version['end_time'], str_pad($version['migration_name'], $maxNameLength, ' ')));

                    if ($version && $version['breakpoint']) {
                        $output->writeln('         <error>BREAKPOINT SET</error>');
                    }
                }
            }
        } else {
            // there are no migrations
            $output->writeln('');
            $output->writeln('There are no available migrations. Try creating one using the <info>create</info> command.');
        }

        // write an empty line
        $output->writeln('');
        if ($format !== null) {
            switch ($format) {
                case 'json':
                    $output->writeln(json_encode([
                        'pending_count' => count($this->getMigrations()),
                        'migrations'    => $migrations
                    ]));
                    break;
                default:
                    $output->writeln('<info>Unsupported format: ' . $format . '</info>');
            }
        }
    }
}
