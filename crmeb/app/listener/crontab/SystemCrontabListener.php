<?php

namespace app\listener\crontab;

use app\services\system\crontab\CrontabRunServices;
use app\services\system\crontab\SystemCrontabServices;
use crmeb\interfaces\ListenerInterface;
use think\helper\Str;
use Workerman\Crontab\Crontab;

/**
 * 系统定时任务
 */
class SystemCrontabListener implements ListenerInterface
{
    public function handle($event): void
    {
        $systemCrontabServices = app()->make(SystemCrontabServices::class);
        $crontabRunServices = app()->make(CrontabRunServices::class);

        //自动写入文件方便检测是否启动定时任务命令
        new Crontab('*/6 * * * * *', function () {
            file_put_contents(root_path() . 'runtime/.timer', time());
        });

        $list = $systemCrontabServices->selectList(['is_del' => 0, 'is_open' => 1])->toArray();
        foreach ($list as &$item) {
            //转化小驼峰
            $functionName = Str::camel($item['mark']);
            //获取定时任务时间字符串
            $timeStr = $this->getTimerStr($item);
            new Crontab($timeStr, function () use ($crontabRunServices, $functionName) {
                $crontabRunServices->$functionName();
            });
        }
    }

    /**
     * 获取定时任务时间表达式
     * 0   1   2   3   4   5
     * |   |   |   |   |   |
     * |   |   |   |   |   +------ day of week (0 - 6) (Sunday=0)
     * |   |   |   |   +------ month (1 - 12)
     * |   |   |   +-------- day of month (1 - 31)
     * |   |   +---------- hour (0 - 23)
     * |   +------------ min (0 - 59)
     * +-------------- sec (0-59)[可省略，如果没有0位,则最小时间粒度是分钟]
     * @param $data
     * @return string
     */
    public function getTimerStr($data): string
    {
        $timeStr = '';
        switch ($data['type']) {
            case 1:// 每隔几秒
                $timeStr = '*/' . $data['second'] . ' * * * * *';
                break;
            case 2:// 每隔几分
                $timeStr = '0 */' . $data['minute'] . ' * * * *';
                break;
            case 3:// 每隔几时第几分钟执行
                $timeStr = '0 ' . $data['minute'] . ' */' . $data['hour'] . ' * * *';
                break;
            case 4:// 每隔几日第几小时第几分钟执行
                $timeStr = '0 ' . $data['minute'] . ' ' . $data['hour'] . ' */' . $data['day'] . ' * *';
                break;
            case 5:// 每日几时几分几秒
                $timeStr = $data['second'] . ' ' . $data['minute'] . ' ' . $data['hour'] . ' * * *';
                break;
            case 6:// 每周周几几时几分几秒
                $timeStr = $data['second'] . ' ' . $data['minute'] . ' ' . $data['hour'] . ' * * ' . ($data['week'] == 7 ? 0 : $data['week']);
                break;
            case 7:// 每月几日几时几分几秒
                $timeStr = $data['second'] . ' ' . $data['minute'] . ' ' . $data['hour'] . ' ' . $data['day'] . ' * *';
                break;
        }
        return $timeStr;
    }
}
