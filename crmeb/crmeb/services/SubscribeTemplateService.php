<?php

namespace crmeb\services;

use app\admin\model\wechat\WechatUser;
use app\admin\model\wechat\StoreService as ServiceModel;
use app\models\routine\RoutineTemplate;
use crmeb\interfaces\ProviderInterface;
use think\facade\Db;

/**
 * 小程序模板消息
 * Class RoutineTemplate
 * @package app\routine\model\routine
 */
class SubscribeTemplateService implements ProviderInterface
{

    //订单发货提醒(送货)
    const ORDER_POSTAGE_SUCCESS = 1128;
    //提现成功通知
    const USER_EXTRACT = 1470;
    //确认收货通知
    const OREDER_TAKEVER = 1481;
    //订单取消
    const ORDER_CLONE = 1134;
    //订单发货提醒(快递)
    const ORDER_DELIVER_SUCCESS = 1458;
    //拼团成功
    const PINK_TRUE = 3098;
    //砍价成功
    const BARGAIN_SUCCESS = 2727;
    //核销成功通知
    const ORDER_WRITE_OFF = 3116;
    //新订单提醒
    const ORDER_NEW = 1476;
    //退款通知
    const ORDER_REFUND = 1451;
    //充值成功
    const RECHARGE_SUCCESS = 755;
    //订单支付成功
    const ORDER_PAY_SUCCESS = 1927;
    //申请退款通知 管理员提醒
    const ORDER_REFUND_STATUS = 1468;
    //积分到账提醒
    const INTEGRAL_ACCOUT = 335;
    //拼团状态通知
    const PINK_STATUS = 3353;

    public static function getConstants($code = '')
    {
        $oClass = new \ReflectionClass(__CLASS__);
        $stants = $oClass->getConstants();
        if ($code) return isset($stants[$code]) ? $stants[$code] : '';
        else return $stants;
    }

    public function register($config)
    {

    }

    /**
     * 根据模板编号获取模板ID
     * @param string $tempKey
     * @return mixed|string
     */
    public static function setTemplateId($tempKey = '')
    {
        if ($tempKey == '') return '';
        return RoutineTemplate::where('tempkey', $tempKey)->where('status', 1)->value('tempid');
    }


    /**
     * 发送订阅模板消息
     * @param string $tempCode 所需下发的模板编号
     * @param string $openId 接收者（用户）的 openid
     * @param array $dataKey 模板内容，不填则下发空模板
     * @param string $link 点击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,（示例index?foo=bar）。该字段不填则模板无跳转
     * @return bool|\EasyWeChat\Support\Collection|null
     */
    public static function sendTemplate(string $tempCode,string $openId,array $dataKey, string $link = '')
    {
        if (!$openId || !$tempCode) return false;
        return MiniProgramService::sendSubscribeTemlate($openId, trim(self::setTemplateId(self::getConstants($tempCode))), $dataKey, $link);
    }

    /**服务进度通知
     * @param array $data
     * @param null $url
     * @param string $defaultColor
     * @return bool
     */
    public static function sendAdminNoticeTemplate(array $data, $url = null, $defaultColor = '')
    {
        $adminIds = explode(',', trim(sys_config('site_store_admin_uids')));
        $kefuIds = ServiceModel::where('notify', 1)->column('uid', 'uid');
        if (empty($adminIds[0])) {
            $adminList = array_unique($kefuIds);
        } else {
            $adminList = array_unique(array_merge($adminIds, $kefuIds));
        }
        if (!is_array($adminList) || empty($adminList)) return false;
        foreach ($adminList as $uid) {
            try {
                $openid = WechatUser::uidToRoutineOpenid($uid);
            } catch (\Exception $e) {
                continue;
            }
//            self::sendTemplate($openid,self::ADMIN_NOTICE,$data,$url,$defaultColor);
        }
    }

    /**
     * 返回所有支持的行业列表
     * @return \EasyWeChat\Support\Collection
     */
    public static function getIndustry()
    {
        return MiniProgramService::noticeService()->getIndustry();
    }

    /**
     * 修改账号所属行业
     * 主行业    副行业    代码
     * IT科技    互联网/电子商务    1
     * IT科技    IT软件与服务    2
     * IT科技    IT硬件与设备    3
     * IT科技    电子技术    4
     * IT科技    通信与运营商    5
     * IT科技    网络游戏    6
     * 金融业    银行    7
     * 金融业    基金|理财|信托    8
     * 金融业    保险    9
     * 餐饮    餐饮    10
     * 酒店旅游    酒店    11
     * 酒店旅游    旅游    12
     * 运输与仓储    快递    13
     * 运输与仓储    物流    14
     * 运输与仓储    仓储    15
     * 教育    培训    16
     * 教育    院校    17
     * 政府与公共事业    学术科研    18
     * 政府与公共事业    交警    19
     * 政府与公共事业    博物馆    20
     * 政府与公共事业    公共事业|非盈利机构    21
     * 医药护理    医药医疗    22
     * 医药护理    护理美容    23
     * 医药护理    保健与卫生    24
     * 交通工具    汽车相关    25
     * 交通工具    摩托车相关    26
     * 交通工具    火车相关    27
     * 交通工具    飞机相关    28
     * 房地产    建筑    29
     * 房地产    物业    30
     * 消费品    消费品    31
     * 商业服务    法律    32
     * 商业服务    会展    33
     * 商业服务    中介服务    34
     * 商业服务    认证    35
     * 商业服务    审计    36
     * 文体娱乐    传媒    37
     * 文体娱乐    体育    38
     * 文体娱乐    娱乐休闲    39
     * 印刷    印刷    40
     * 其它    其它    41
     * @param $industryId1
     * @param $industryId2
     * @return \EasyWeChat\Support\Collection
     */
    public static function setIndustry($industryId1, $industryId2)
    {
        return MiniProgramService::noticeService()->setIndustry($industryId1, $industryId2);
    }

    /**
     * 获取所有模板列表
     * @return \EasyWeChat\Support\Collection
     */
    public static function getPrivateTemplates()
    {
        return MiniProgramService::noticeService()->getPrivateTemplates();
    }

    /**
     * 删除指定ID的模板
     * @param $templateId
     * @return \EasyWeChat\Support\Collection
     */
    public static function deletePrivateTemplate($templateId)
    {
        return MiniProgramService::noticeService()->deletePrivateTemplate($templateId);
    }


    /**
     * 添加模板并获取模板ID
     * @param $shortId
     * @return \EasyWeChat\Support\Collection
     */
    public static function addTemplate($shortId)
    {
        return MiniProgramService::noticeService()->addTemplate($shortId);
    }
}