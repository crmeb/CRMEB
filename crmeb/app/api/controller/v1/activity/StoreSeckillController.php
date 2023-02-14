<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\api\controller\v1\activity;

use app\Request;
use app\services\activity\seckill\StoreSeckillServices;
use app\services\other\QrcodeServices;
use crmeb\services\GroupDataService;

/**
 * 秒杀商品类
 * Class StoreSeckillController
 * @package app\api\controller\activity
 */
class StoreSeckillController
{

    protected $services;

    public function __construct(StoreSeckillServices $services)
    {
        $this->services = $services;
    }

    /**
     * 秒杀商品时间区间
     * @return mixed
     */
    public function index()
    {
        //秒杀时间段
        $seckillTime = GroupDataService::getData('routine_seckill_time') ?? [];
        $seckillTimeIndex = -1;
        $timeCount = count($seckillTime);//总数
        $unTimeCunt = 0;//即将开始
        if ($timeCount) {
            $today = strtotime(date('Y-m-d'));
            $currentHour = date('H');
            foreach ($seckillTime as $key => &$value) {
                $activityEndHour = bcadd((int)$value['time'], (int)$value['continued'], 0);
                if ($activityEndHour > 24) {
                    $value['time'] = strlen((int)$value['time']) == 2 ? (int)$value['time'] . ':00' : '0' . (int)$value['time'] . ':00';
                    $value['state'] = '即将开始';
                    $value['status'] = 2;
                    $value['stop'] = (int)bcadd($today, bcmul($activityEndHour, 3600, 0));
                } else {
                    if ($currentHour >= (int)$value['time'] && $currentHour < $activityEndHour) {
                        $value['time'] = strlen((int)$value['time']) == 2 ? (int)$value['time'] . ':00' : '0' . (int)$value['time'] . ':00';
                        $value['state'] = '抢购中';
                        $value['stop'] = (int)bcadd($today, bcmul($activityEndHour, 3600, 0));
                        $value['status'] = 1;
                        if ($seckillTimeIndex == -1) $seckillTimeIndex = $key;
                    } else if ($currentHour < (int)$value['time']) {
                        $value['time'] = strlen((int)$value['time']) == 2 ? (int)$value['time'] . ':00' : '0' . (int)$value['time'] . ':00';
                        $value['state'] = '即将开始';
                        $value['status'] = 2;
                        $value['stop'] = (int)bcadd($today, bcmul($activityEndHour, 3600, 0));
                        $unTimeCunt += 1;
                    } else if ($currentHour >= $activityEndHour) {
                        $value['time'] = strlen((int)$value['time']) == 2 ? (int)$value['time'] . ':00' : '0' . (int)$value['time'] . ':00';
                        $value['state'] = '已结束';
                        $value['status'] = 0;
                        $value['stop'] = (int)bcadd($today, bcmul($activityEndHour, 3600, 0));
                    }
                }
            }
            //有时间段但是都不在抢购中
            if ($seckillTimeIndex == -1 && $currentHour <= (int)$seckillTime[$timeCount - 1]['time'] ?? 0) {
                if ($currentHour < (int)$seckillTime[0]['time'] ?? 0) {//当前时间
                    $seckillTimeIndex = 0;
                } elseif ($unTimeCunt) {//存在未开始的
                    foreach ($seckillTime as $key => $item) {
                        if ($item['status'] == 2) {
                            $seckillTimeIndex = $key;
                            break;
                        }
                    }
                } else {
                    $seckillTimeIndex = $timeCount - 1;
                }
            }
        }
        $data['lovely'] = sys_config('seckill_header_banner');
        if (strstr($data['lovely'], 'http') === false && strlen(trim($data['lovely']))) $data['lovely'] = sys_config('site_url') . $data['lovely'];
        $data['lovely'] = str_replace('\\', '/', $data['lovely']);
        $data['seckillTime'] = $seckillTime;
        $data['seckillTimeIndex'] = $seckillTimeIndex;
        return app('json')->success($data);
    }

    /**
     * 秒杀商品列表
     * @param $time
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lst($time)
    {
        if (!$time) return app('json')->fail(100100);
        $seckillInfo = $this->services->getListByTime($time);
        return app('json')->success(get_thumb_water($seckillInfo));
    }

    /**
     * 秒杀商品详情
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function detail(Request $request, $id)
    {
        $data = $this->services->seckillDetail($request, $id);
        return app('json')->success($data);
    }

    /**
     * 获取秒杀小程序二维码
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function code(Request $request, $id)
    {
        /** @var QrcodeServices $qrcodeService */
        $qrcodeService = app()->make(QrcodeServices::class);
        $url = $qrcodeService->getRoutineQrcodePath($id, $request->uid(), 2);
        if ($url) {
            return app('json')->success(['code' => $url]);
        } else {
            return app('json')->success(['code' => '']);
        }
    }
}
