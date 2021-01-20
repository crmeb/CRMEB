<?php

namespace think\queue\command;

use stdClass;
use think\console\Command;
use think\console\input\Argument;
use think\helper\Arr;

class Retry extends Command
{
    protected function configure()
    {
        $this->setName('queue:retry')
            ->addArgument('id', Argument::IS_ARRAY | Argument::REQUIRED, 'The ID of the failed job or "all" to retry all jobs')
            ->setDescription('Retry a failed queue job');
    }

    public function handle()
    {
        foreach ($this->getJobIds() as $id) {
            $job = $this->app['queue.failer']->find($id);

            if (is_null($job)) {
                $this->output->error("Unable to find failed job with ID [{$id}].");
            } else {
                $this->retryJob($job);

                $this->output->info("The failed job [{$id}] has been pushed back onto the queue!");

                $this->app['queue.failer']->forget($id);
            }
        }
    }

    /**
     * Retry the queue job.
     *
     * @param stdClass $job
     * @return void
     */
    protected function retryJob($job)
    {
        $this->app['queue']->connection($job['connection'])->pushRaw(
            $this->resetAttempts($job['payload']), $job['queue']
        );
    }

    /**
     * Reset the payload attempts.
     *
     * Applicable to Redis jobs which store attempts in their payload.
     *
     * @param string $payload
     * @return string
     */
    protected function resetAttempts($payload)
    {
        $payload = json_decode($payload, true);

        if (isset($payload['attempts'])) {
            $payload['attempts'] = 0;
        }

        return json_encode($payload);
    }

    /**
     * Get the job IDs to be retried.
     *
     * @return array
     */
    protected function getJobIds()
    {
        $ids = (array) $this->input->getArgument('id');

        if (count($ids) === 1 && $ids[0] === 'all') {
            $ids = Arr::pluck($this->app['queue.failer']->all(), 'id');
        }

        return $ids;
    }
}
