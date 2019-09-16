<?php

namespace think\queue\command;

use think\console\Command;
use think\helper\Str;
use think\migration\Creator;

class FailedTable extends Command
{
    protected function configure()
    {
        $this->setName('queue:failed-table')
            ->setDescription('Create a migration for the failed queue jobs database table');
    }

    public function handle()
    {
        if (!$this->app->has('migration.creator')) {
            $this->output->error('Install think-migration first please');
            return;
        }

        $table = $this->app->config->get('queue.failed.table');

        $className = Str::studly("create_{$table}_table");

        /** @var Creator $creator */
        $creator = $this->app->get('migration.creator');

        $path = $creator->create($className);

        // Load the alternative template if it is defined.
        $contents = file_get_contents(__DIR__ . '/stubs/failed_jobs.stub');

        // inject the class names appropriate to this migration
        $contents = strtr($contents, [
            'CreateFailedJobsTable' => $className,
            '{{table}}'             => $table,
        ]);

        file_put_contents($path, $contents);

        $this->output->info('Migration created successfully!');
    }
}
