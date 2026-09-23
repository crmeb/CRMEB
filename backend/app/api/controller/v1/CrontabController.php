<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\api\controller\v1;

use app\services\order\OrderCenterServices;
use app\services\order\OrderTakeServices;
use app\services\product\CenterProductServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\system\crontab\SystemCrontabServices;

/**
 * 定时任务控制器
 * @author 吴汐
 * @email 442384644@qq.com
 * @date 2023/02/21
 */
class CrontabController
{
    /**
     * 定时任务调用接口
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/02/17
     */
    public function crontabRun()
    {
        app()->make(SystemCrontabServices::class)->crontabApiRun();
    }

    /**
     * 检测定时任务是否正常，必须6秒执行一次
     */
    public function crontabCheck()
    {
        file_put_contents(root_path() . 'runtime/.timer', time());
    }

    /**
     * 未支付自动取消订单
     * 通过 CrontabTaskRegistry 分发至产品模块定时任务（mark=orderCancel，与系统定时器一致），
     * 避免 app 层直接引用 modules 层
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderUnpaidCancel()
    {
        $registry = app()->make(\crmeb\crontab\CrontabTaskRegistry::class);
        if ($registry->has('orderCancel')) {
            $registry->get('orderCancel')->handle();
        }
    }

    /**
     * 拼团到期订单处理
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function pinkExpiration()
    {
        //通过事件代理处理拼团到期，避免 app 层直接引用 modules
        event('ActivityDataProxy', ['mode' => 'combination', 'sub_service' => 'pink', 'action' => 'statusPink', 'args' => []]);
    }

    /**
     * 自动解绑上级绑定
     */
    public function distributionUnbind()
    {
        event('DistributionUnbindCrontab');
    }

    /**
     * 更新直播商品状态
     */
    public function syncGoodStatus()
    {
        //通过事件代理同步直播商品状态，避免 app 层直接引用 modules
        event('ChannelServiceProxy', ['endpoint' => 'wechat_mini', 'service' => 'LiveGoodsServices', 'action' => 'syncGoodStatus', 'args' => []]);
    }

    /**
     * 更新直播间状态
     */
    public function syncRoomStatus()
    {
        //通过事件代理同步直播间状态，避免 app 层直接引用 modules
        event('ChannelServiceProxy', ['endpoint' => 'wechat_mini', 'service' => 'LiveRoomServices', 'action' => 'syncRoomStatus', 'args' => []]);
    }

    /**
     * 自动收货
     */
    public function autoTakeOrder()
    {
        /** @var OrderTakeServices $services */
        $services = app()->make(OrderTakeServices::class);
        $services->autoTakeOrder();
    }

    /**
     * 查询预售到期商品自动下架
     */
    public function downAdvance()
    {
        /** @var CenterProductServices $product */
        $product = app()->make(CenterProductServices::class);
        $product->downAdvance();
    }

    /**
     * 自动好评
     */
    public function autoComment()
    {
        /** @var OrderCenterServices $orderServices */
        $orderServices = app()->make(OrderCenterServices::class);
        $orderServices->autoComment();
    }

    /**
     * 清除昨日海报
     * @throws \Exception
     */
    public function emptyYesterdayAttachment()
    {
        /** @var SystemAttachmentServices $attach */
        $attach = app()->make(SystemAttachmentServices::class);
        $attach->emptyYesterdayAttachment();
    }
}
