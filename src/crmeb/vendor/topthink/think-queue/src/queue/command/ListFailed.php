<?php

namespace think\queue\command;

use think\console\Command;
use think\console\Table;
use think\helper\Arr;

class ListFailed extends Command
{
    /**
     * The table headers for the command.
     *
     * @var array
     */
    protected $headers = ['ID', 'Connection', 'Queue', 'Class', 'Failed At'];

    protected function configure()
    {
        $this->setName('queue:failed')
            ->setDescription('List all of the failed queue jobs');
    }

    public function handle()
    {
        if (count($jobs = $this->getFailedJobs()) === 0) {
            $this->output->info('No failed jobs!');
            return;
        }
        $this->displayFailedJobs($jobs);
    }

    /**
     * Display the failed jobs in the console.
     *
     * @param array $jobs
     * @return void
     */
    protected function displayFailedJobs(array $jobs)
    {
        $table = new Table();
        $table->setHeader($this->headers);
        $table->setRows($jobs);

        $this->table($table);
    }

    /**
     * Compile the failed jobs into a displayable format.
     *
     * @return array
     */
    protected function getFailedJobs()
    {
        $failed = $this->app['queue.failer']->all();

        return collect($failed)->map(function ($failed) {
            return $this->parseFailedJob((array) $failed);
        })->filter()->all();
    }

    /**
     * Parse the failed job row.
     *
     * @param array $failed
     * @return array
     */
    protected function parseFailedJob(array $failed)
    {
        $row = array_values(Arr::except($failed, ['payload', 'exception']));

        array_splice($row, 3, 0, $this->extractJobName($failed['payload']));

        return $row;
    }

    /**
     * Extract the failed job name from payload.
     *
     * @param string $payload
     * @return string|null
     */
    private function extractJobName($payload)
    {
        $payload = json_decode($payload, true);

        if ($payload && (!isset($payload['data']['command']))) {
            return $payload['job'] ?? null;
        } elseif ($payload && isset($payload['data']['command'])) {
            return $this->matchJobName($payload);
        }
    }

    /**
     * Match the job name from the payload.
     *
     * @param array $payload
     * @return string
     */
    protected function matchJobName($payload)
    {
        preg_match('/"([^"]+)"/', $payload['data']['command'], $matches);

        if (isset($matches[1])) {
            return $matches[1];
        }

        return $payload['job'] ?? null;
    }
}
