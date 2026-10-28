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

namespace app\services\system;

use crmeb\basic\BaseService;
use think\facade\Event;

class SystemEventServices extends BaseService
{
    /**
     * 事件分类前缀匹配规则
     * 按优先级排序，优先匹配前面的分类
     * 事件名以任一前缀开头即归入该分类
     */
    protected $categoryRules = [
        '渠道事件' => ['MiniProgram', 'UserOpenid', 'WechatMerchant', 'WechatSubscribe', 'OrderShipping'],
        '分销事件' => ['UserRegisterAgent', 'UserPromoter', 'OrderSpread', 'OrderDivision', 'Brokerage', 'Agent'],
        '系统事件' => ['Invoice', 'Service', 'System'],
        'HTTP事件' => ['AppInit', 'HttpRun', 'HttpEnd', 'LogLevel', 'LogWrite', 'QueueStart'],
        '定时任务事件' => ['Crontab'],
        '管理员事件' => ['AdminLogin'],
        '外部推送事件' => ['Out'],
        '支付事件' => ['Pay', 'Notify'],
        '购物车事件' => ['Cart'],
        '商品事件' => ['Product'],
        '订单事件' => ['Order', 'ShippingTemplate', 'ProductBatch'],
        '营销事件' => ['Integral', 'MemberCard', 'CapitalFlow', 'Coupon', 'Bargain', 'Pink', 'Lottery'],
        '用户事件' => ['User'],
    ];

    protected $eventDescriptions = [
        // HTTP事件
        'AppInit' => '应用初始化事件',
        'HttpRun' => 'HTTP请求运行事件',
        'HttpEnd' => 'HTTP请求结束回调事件',
        'LogLevel' => '日志级别事件',
        'LogWrite' => '日志写入事件',
        // 用户事件
        'UserLogin' => '用户登录事件',
        'UserLogout' => '用户退出登录事件',
        'UserRegister' => '用户注册后置事件',
        'UserLevel' => '用户升级事件',
        'UserVisit' => '用户访问事件',
        'UserIntegralChange' => '用户积分变动事件',
        'UserExpChange' => '用户经验变动事件',
        'UserBalanceChange' => '用户余额变动事件',
        'UserRecharge' => '用户充值成功事件',
        'UserRechargeRefund' => '用户充值退款事件',
        'UserRevenue' => '用户收益事件',
        'UserCancel' => '用户注销事件',
        // 管理员事件
        'AdminLogin' => '管理员登录事件',
        // 系统事件
        // 订单事件
        'OrderCreateAfter' => '订单创建后置事件',
        'OrderPromotionCompute' => '订单促销计价扩展点（价格链条位[20]，营销插件经该事件写回优惠金额与明细）',
        'OrderPaySuccess' => '订单支付成功后置事件',
        'OrderDelivery' => '订单发货后置事件',
        'OrderTake' => '订单收货后置事件',
        'OrderShipping' => '小程序发货管理',
        'OrderCancel' => '订单取消事件',
        'OrderComplete' => '订单完成事件',
        'OrderRefundCreateAfter' => '售后单生成后置事件',
        'OrderRefundCancelAfter' => '售后单取消后置事件',
        'OrderRefundSuccess' => '售后单退款成功事件',
        'OrderRefundRefuse' => '售后单退款拒绝事件',
        'OrderComment' => '订单评价事件',
        'OrderDelete' => '订单删除事件',
        'OrderPriceChange' => '订单价格变更事件',
        'OrderCenterDetailExtend' => '订单详情扩展点（extend 注入数据 + extend_sections 自声明渲染）',
        'OrderCenterBusinessStatusResolve' => '订单业务状态解析',
        'OrderPayLottery' => '支付后抽奖事件',
        // 购物车事件
        'UserCart' => '购物车事件',
        // 商品事件
        'Product' => '商品事件(创建/更新/删除/状态/库存/价格/收藏/访问/评价)',
        'ProductBatchLogistics' => '批量发货查询事件',
        // 支付事件
        'Notify' => '支付异步回调',
        // 外部推送
        'OutPush' => '对外推送事件',
        // 定时任务
        'CrontabBeforeRun' => '定时任务执行前事件',
        'CrontabAfterRun' => '定时任务执行后事件',
        'CrontabSuccess' => '定时任务成功事件',
        'CrontabFail' => '定时任务失败事件',
        // 营销事件
        'Bargain' => '砍价事件',
        'BargainComplete' => '砍价完成事件',
        'BargainStart' => '砍价开始事件',
        'BargainHelp' => '砍价助力事件',
        'BargainCancel' => '砍价取消事件',
        'BargainExpire' => '砍价过期事件',
        'OrderCommentLotteryCheck' => '评价后抽奖检查',
        // 渠道事件
        'MiniProgramApiCall' => '小程序API调用事件',
        'UserOpenidQuery' => '用户OpenID查询',
        'WechatMerchantPay' => '微信商家付款',
        'WechatSubscribeCheck' => '微信公众号订阅检查',
        // 分销事件
        'UserListExtend' => '用户列表扩展点',
        'UserDetailExtend' => '用户详情扩展点',
        'UserPromoterCancel' => '推广员取消事件',
        'OrderSpreadBrokerage' => '订单佣金结算',
        'OrderDivisionPercent' => '订单分账比例查询',
        'OrderDivisionInfoQuery' => '订单分账信息查询',
        'UserRegisterDistributionJob' => '用户注册分销任务',
        'BrokerageBindingStatusChange' => '佣金绑定状态变更',
        // 通知事件
        'UnpaidOrderNotice' => '未支付订单通知事件',
        'MemberExpireNotice' => '会员过期通知事件',
        // 抽奖事件
        'LotteryDraw' => '抽奖事件',
        'LotteryWin' => '中奖事件',
        'LotteryLose' => '未中奖事件',
        'LotteryReceive' => '领奖事件',
        'LotteryRevenue' => '抽奖收益事件',
        // 其他
        'OrderCenterListExtend' => '订单中心列表扩展',
    ];

    /**
     * 获取事件列表（按分类分组）
     * 通过反射从 Event 实例动态获取所有已注册事件，按前缀规则自动分类
     */
    public function getEventList()
    {
        $event = Event::instance();
        $listenerProperty = new \ReflectionProperty($event, 'listener');
        $listenerProperty->setAccessible(true);
        $listeners = $listenerProperty->getValue($event);

        // 从实际注册的监听器中提取所有事件名
        $allEventNames = array_keys($listeners);

        // 按分类规则分组
        $categorized = [];
        foreach ($allEventNames as $eventName) {
            $eventListeners = $listeners[$eventName] ?? [];
            $listenerList = [];
            foreach ($eventListeners as $listener) {
                if (is_string($listener)) {
                    $listenerList[] = [
                        'class' => $listener,
                        'short_class' => $this->getShortClassName($listener),
                        'source' => $this->getListenerSource($listener),
                    ];
                }
            }

            $category = $this->categorizeEvent($eventName);
            $categorized[$category][] = [
                'name' => $eventName,
                'description' => $this->eventDescriptions[$eventName] ?? $this->generateDescription($eventName),
                'listener_count' => count($listenerList),
                'listeners' => $listenerList,
            ];
        }

        // 按 rules 定义顺序输出分类（保证分类顺序稳定）
        $result = [];
        $seenCategories = [];
        foreach ($this->categoryRules as $category => $prefixes) {
            if (isset($categorized[$category])) {
                $result[] = [
                    'name' => $category,
                    'events' => $categorized[$category],
                ];
                $seenCategories[$category] = true;
            }
        }
        // 未匹配到规则的事件归入"其他事件"
        if (isset($categorized['其他事件'])) {
            $result[] = [
                'name' => '其他事件',
                'events' => $categorized['其他事件'],
            ];
        }

        return $result;
    }

    /**
     * 获取事件详情
     */
    public function getEventInfo(string $eventName)
    {
        $event = Event::instance();
        $listenerProperty = new \ReflectionProperty($event, 'listener');
        $listenerProperty->setAccessible(true);
        $listeners = $listenerProperty->getValue($event);

        $eventListeners = $listeners[$eventName] ?? [];
        $listenerList = [];
        foreach ($eventListeners as $listener) {
            if (is_string($listener)) {
                $listenerList[] = [
                    'class' => $listener,
                    'short_class' => $this->getShortClassName($listener),
                    'source' => $this->getListenerSource($listener),
                ];
            }
        }

        return [
            'name' => $eventName,
            'description' => $this->eventDescriptions[$eventName] ?? $this->generateDescription($eventName),
            'listener_count' => count($listenerList),
            'listeners' => $listenerList,
        ];
    }

    /**
     * 获取事件分类统计
     */
    public function getCategories()
    {
        $event = Event::instance();
        $listenerProperty = new \ReflectionProperty($event, 'listener');
        $listenerProperty->setAccessible(true);
        $listeners = $listenerProperty->getValue($event);

        $allEventNames = array_keys($listeners);

        $categoryCount = [];
        foreach ($allEventNames as $eventName) {
            $category = $this->categorizeEvent($eventName);
            if (!isset($categoryCount[$category])) {
                $categoryCount[$category] = 0;
            }
            $categoryCount[$category]++;
        }

        $categories = [];
        foreach ($this->categoryRules as $name => $prefixes) {
            if (isset($categoryCount[$name])) {
                $categories[] = [
                    'name' => $name,
                    'event_count' => $categoryCount[$name],
                ];
                unset($categoryCount[$name]);
            }
        }
        if (!empty($categoryCount)) {
            foreach ($categoryCount as $name => $count) {
                $categories[] = [
                    'name' => $name,
                    'event_count' => $count,
                ];
            }
        }

        return $categories;
    }

    /**
     * 根据事件名匹配分类
     */
    protected function categorizeEvent(string $eventName): string
    {
        foreach ($this->categoryRules as $category => $prefixes) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with($eventName, $prefix)) {
                    return $category;
                }
            }
        }
        return '其他事件';
    }

    /**
     * 从事件名自动生成中文描述（无自定义描述时的兜底）
     * 将驼峰事件名拆分为单词，逐个翻译为中文
     */
    protected function generateDescription(string $eventName): string
    {
        // 去掉 Listener 后缀
        $normalized = preg_replace('/Listener$/', '', $eventName);

        // 拆分为驼峰单词
        $words = preg_split('/(?<=[a-z])(?=[A-Z])/', $normalized);
        $words = array_values(array_filter($words, fn($v) => $v !== ''));

        // 逐个翻译
        $cnParts = [];
        foreach ($words as $word) {
            $cn = $this->wordToCn($word);
            if ($cn !== null) {
                $cnParts[] = $cn;
            }
        }

        if (!empty($cnParts)) {
            return implode('', $cnParts) . '事件';
        }

        return $normalized . '事件';
    }

    /**
     * 英文单词 → 中文术语
     */
    protected function wordToCn(string $word): ?string
    {
        $map = [
            'App' => '应用', 'Init' => '初始化',
            'Http' => 'HTTP', 'Run' => '运行', 'End' => '结束',
            'Log' => '日志', 'Level' => '级别', 'Write' => '写入',
            'Queue' => '队列', 'Start' => '启动',
            'Listener' => '监听',
            'User' => '用户', 'Login' => '登录', 'Register' => '注册',
            'Visit' => '访问', 'Info' => '信息', 'Change' => '变更',
            'Integral' => '积分', 'Exp' => '经验', 'Balance' => '余额',
            'Recharge' => '充值', 'Refund' => '退款',
            'Extract' => '提现', 'Apply' => '申请',
            'Revenue' => '收益', 'Brokerage' => '佣金',
            'Collect' => '收藏', 'Cancel' => '取消',
            'Wechat' => '微信', 'Auth' => '授权', 'Subscribe' => '关注',
            'Admin' => '管理员',
            'Invoice' => '发票', 'Service' => '客服', 'System' => '系统',
            'Order' => '订单', 'Create' => '创建', 'After' => '后',
            'Pay' => '支付', 'Success' => '成功',
            'Delivery' => '发货', 'Take' => '收货', 'Shipping' => '发货',
            'Complete' => '完成', 'Comment' => '评价',
            'Delete' => '删除', 'Price' => '价格',
            'Cart' => '购物车',
            'Product' => '商品', 'Notify' => '通知',
            'Out' => '外部', 'Push' => '推送',
            'Member' => '会员', 'Card' => '卡', 'Capital' => '资金', 'Flow' => '流水',
            'Coupon' => '优惠券',
            'Crontab' => '定时任务', 'Before' => '前', 'Fail' => '失败',
            'Mini' => '迷你', 'Program' => '程序', 'Api' => 'API', 'Call' => '调用',
            'Openid' => 'OpenID', 'Query' => '查询',
            'Merchant' => '商家', 'Check' => '检查',
            'Center' => '中心', 'Detail' => '详情', 'Extend' => '扩展',
            'Business' => '业务', 'Status' => '状态', 'Resolve' => '解析',
            'List' => '列表', 'Promoter' => '推广员',
            'Spread' => '推广', 'Division' => '分账', 'Percent' => '比例',
            'Agent' => '代理', 'Job' => '任务', 'Binding' => '绑定', 'Unbind' => '解绑',
            'Lottery' => '抽奖', 'Draw' => '抽奖', 'Win' => '中奖',
            'Lose' => '未中', 'Receive' => '领取',
            'Bargain' => '砍价', 'Help' => '助力', 'Expire' => '过期',
            'Pink' => '拼团', 'Join' => '参与',
            'Template' => '模板', 'Region' => '区域', 'Free' => '包邮',
            'No' => '不', 'Batch' => '批量',
            'Logistics' => '物流',
            'Unpaid' => '未支付', 'Notice' => '通知',
            'Custom' => '自定义', 'Event' => '事件',
            'Map' => '映射', 'Route' => '路由',
            'Score' => '积分', 'Store' => '门店',
            'Group' => '分组', 'Data' => '数据',
            'Api' => 'API', 'Controller' => '控制器',
            'Interface' => '接口',
        ];

        return $map[$word] ?? null;
    }

    protected function getShortClassName(string $className)
    {
        $parts = explode('\\', $className);
        return end($parts);
    }

    protected function getListenerSource(string $className)
    {
        if (str_starts_with($className, 'app\\listener\\')) {
            return 'app';
        } elseif (str_starts_with($className, 'modules\\')) {
            return 'modules';
        } else {
            return 'core';
        }
    }
}
