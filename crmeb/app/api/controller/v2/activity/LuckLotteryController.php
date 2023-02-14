<?php


namespace app\api\controller\v2\activity;


use app\Request;
use app\services\activity\lottery\LuckLotteryRecordServices;
use app\services\activity\lottery\LuckLotteryServices;
use app\services\other\QrcodeServices;
use app\services\wechat\WechatServices;
use crmeb\services\CacheService;

class LuckLotteryController
{
    protected $services;

    public function __construct(LuckLotteryServices $services)
    {
        $this->services = $services;
    }

    /**
     * 抽奖活动信息
     * @param Request $request
     * @param $factor
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function LotteryInfo(Request $request, $factor)
    {
        if (!$factor) return app('json')->fail(100100);
        $lottery = $this->services->getFactorLottery((int)$factor, '*', ['prize'], true);
        if (!$lottery) {
            return app('json')->fail(410318);
        }
        $uid = (int)$request->uid();
        $lottery = $lottery->toArray();
        $lotteryData = ['lottery' => $lottery];
        $this->services->checkoutUserAuth($uid, (int)$lottery['id'], [], $lottery);
        $lotteryData['lottery_num'] = $this->services->getLotteryNum($uid, (int)$lottery['id'], [], $lottery);
        $all_record = $user_record = [];
        if ($lottery['is_all_record'] || $lottery['is_personal_record']) {
            /** @var LuckLotteryRecordServices $lotteryRecordServices */
            $lotteryRecordServices = app()->make(LuckLotteryRecordServices::class);
            if ($lottery['is_all_record']) {
                $all_record = $lotteryRecordServices->getWinList(['lottery_id' => $lottery['id']]);
            }
            if ($lottery['is_personal_record']) {
                $user_record = $lotteryRecordServices->getWinList(['lottery_id' => $lottery['id'], 'uid' => $uid]);
            }
        }
        $lotteryData['all_record'] = $all_record;
        $lotteryData['user_record'] = $user_record;
        return app('json')->success($lotteryData);
    }

    /**
     * 参与抽奖
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function luckLottery(Request $request)
    {
        [$id, $type] = $request->postMore([
            ['id', 0],
            ['type', 0]
        ], true);

        $uid = (int)$request->uid();
        $key = 'lucklotter_limit_' . $uid;
        if (CacheService::get($key)) {
            return app('json')->fail('您求的频率太过频繁,请稍后请求!');
        }
        CacheService::set('lucklotter_limit_' . $uid, $uid, 1);

        if ($type == 5 && request()->isWechat()) {
            /** @var WechatServices $wechat */
            $wechat = app()->make(WechatServices::class);
            $subscribe = $wechat->get(['user_type' => 'wechat', 'uid' => $request->uid(), 'subscribe' => 1]);
            if (!$subscribe) {
                $url = '';
                /** @var QrcodeServices $qrcodeService */
                $qrcodeService = app()->make(QrcodeServices::class);
                $url = $qrcodeService->getTemporaryQrcode('luckLottery-5', $request->uid())->url;
                return app('json')->success(410024, ['code' => 'subscribe', 'url' => $url]);
            }
        }
        if (!$id) {
            return app('json')->fail(100100);
        }

        return app('json')->success($this->services->luckLottery($uid, $id));
    }

    /**
     * 领取奖品
     * @param Request $request
     * @return mixed
     */
    public function lotteryReceive(Request $request, LuckLotteryRecordServices $lotteryRecordServices)
    {
        [$id, $name, $phone, $address, $mark] = $request->postMore([
            ['id', 0],
            ['name', ''],
            ['phone', ''],
            ['address', ''],
            ['mark', '']
        ], true);
        if (!$id) {
            return app('json')->fail(100100);
        }
        $uid = (int)$request->uid();
        return app('json')->success($lotteryRecordServices->receivePrize($uid, $id, compact('name', 'phone', 'address', 'mark')) ? 410319 : 410320);
    }

    /**
     * 获取中奖记录
     * @param Request $request
     * @param LuckLotteryRecordServices $lotteryRecordServices
     * @return mixed
     */
    public function lotteryRecord(Request $request, LuckLotteryRecordServices $lotteryRecordServices)
    {
        $uid = (int)$request->uid();
        return app('json')->success($lotteryRecordServices->getRecord($uid));
    }
}
