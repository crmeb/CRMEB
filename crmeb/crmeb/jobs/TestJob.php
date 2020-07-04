<?php


namespace crmeb\jobs;


use app\models\user\User;
use crmeb\interfaces\JobInterface;
use think\facade\Db;
use think\queue\Job;

class TestJob implements JobInterface
{
    public function fire(Job $job, $data): void
    {
        $job->delete();

        if($this->doHelloJob($data))
            $job->delete();
        else {
            if ($job->attempts() > 3)
                $job->delete();
        }
    }

    public function doHelloJob($data)
    {
        [$order, $formId] = $data;
        Db::name('cache')->insert(['key'=>'test_'.rand(10,20),'result'=>json_encode($order)]);
        event('OrderPaySuccess', [$order, $formId]);
        return true;
    }
}