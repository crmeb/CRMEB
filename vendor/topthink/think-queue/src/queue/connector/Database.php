<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\queue\connector;

use Carbon\Carbon;
use stdClass;
use think\Db;
use think\db\Query;
use think\queue\Connector;
use think\queue\InteractsWithTime;
use think\queue\job\Database as DatabaseJob;

class Database extends Connector
{

    use InteractsWithTime;

    protected $db;

    /**
     * The database table that holds the jobs.
     *
     * @var string
     */
    protected $table;

    /**
     * The name of the default queue.
     *
     * @var string
     */
    protected $default;

    /**
     * The expiration time of a job.
     *
     * @var int|null
     */
    protected $retryAfter = 60;

    public function __construct(Db $db, $table, $default = 'default', $retryAfter = 60)
    {
        $this->db         = $db;
        $this->table      = $table;
        $this->default    = $default;
        $this->retryAfter = $retryAfter;
    }

    public static function __make(Db $db, $config)
    {
        return new self($db, $config['table'], $config['queue'], $config['retry_after'] ?? 60);
    }

    public function size($queue = null)
    {
        $this->db->name($this->table)
            ->where('queue', $this->getQueue($queue))
            ->count();
    }

    public function push($job, $data = '', $queue = null)
    {
        return $this->pushToDatabase($queue, $this->createPayload($job, $data));
    }

    public function pushRaw($payload, $queue = null, array $options = [])
    {
        return $this->pushToDatabase($queue, $payload);
    }

    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->pushToDatabase($queue, $this->createPayload($job, $data), $delay);
    }

    public function bulk($jobs, $data = '', $queue = null)
    {
        $queue = $this->getQueue($queue);

        $availableAt = $this->availableAt();

        return $this->db->name($this->table)->insertAll(collect((array) $jobs)->map(
            function ($job) use ($queue, $data, $availableAt) {
                return [
                    'queue'        => $queue,
                    'attempts'     => 0,
                    'reserved_at'  => null,
                    'available_at' => $availableAt,
                    'created_at'   => $this->currentTime(),
                    'payload'      => $this->createPayload($job, $data),
                ];
            }
        )->all());
    }

    /**
     * 重新发布任务
     *
     * @param string   $queue
     * @param StdClass $job
     * @param int      $delay
     * @return mixed
     */
    public function release($queue, $job, $delay)
    {
        return $this->pushToDatabase($queue, $job->payload, $delay, $job->attempts);
    }

    /**
     * Push a raw payload to the database with a given delay.
     *
     * @param \DateTime|int $delay
     * @param string|null   $queue
     * @param string        $payload
     * @param int           $attempts
     * @return mixed
     */
    protected function pushToDatabase($queue, $payload, $delay = 0, $attempts = 0)
    {
        return $this->db->name($this->table)->insertGetId([
            'queue'        => $this->getQueue($queue),
            'attempts'     => $attempts,
            'reserved_at'  => null,
            'available_at' => $this->availableAt($delay),
            'created_at'   => $this->currentTime(),
            'payload'      => $payload,
        ]);
    }

    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        return $this->db->transaction(function () use ($queue) {

            if ($job = $this->getNextAvailableJob($queue)) {

                $job = $this->markJobAsReserved($job);

                return new DatabaseJob($this->app, $this, $job, $this->connection, $queue);
            }
        });
    }

    /**
     * 获取下个有效任务
     *
     * @param string|null $queue
     * @return StdClass|null
     */
    protected function getNextAvailableJob($queue)
    {

        $job = $this->db->name($this->table)
            ->lock(true)
            ->where('queue', $this->getQueue($queue))
            ->where(function (Query $query) {
                $query->where(function (Query $query) {
                    $query->whereNull('reserved_at')
                        ->where('available_at', '<=', $this->currentTime());
                });

                //超时任务重试
                $expiration = Carbon::now()->subSeconds($this->retryAfter)->getTimestamp();

                $query->whereOr(function (Query $query) use ($expiration) {
                    $query->where('reserved_at', '<=', $expiration);
                });
            })
            ->order('id', 'asc')
            ->find();

        return $job ? (object) $job : null;
    }

    /**
     * 标记任务正在执行.
     *
     * @param stdClass $job
     * @return stdClass
     */
    protected function markJobAsReserved($job)
    {
        $this->db->name($this->table)->where('id', $job->id)->update([
            'reserved_at' => $job->reserved_at = $this->currentTime(),
            'attempts'    => ++$job->attempts,
        ]);

        return $job;
    }

    /**
     * 删除任务
     *
     * @param string $id
     * @return void
     */
    public function deleteReserved($id)
    {
        $this->db->transaction(function () use ($id) {
            if ($this->db->name($this->table)->lock(true)->find($id)) {
                $this->db->name($this->table)->where('id', $id)->delete();
            }
        });
    }

    protected function getQueue($queue)
    {
        return $queue ?: $this->default;
    }
}
