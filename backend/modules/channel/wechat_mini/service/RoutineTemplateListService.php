<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace modules\channel\wechat_mini\service;

use crmeb\exceptions\NoticeSendException;
use crmeb\services\notice\AbstractNoticeChannel;
use crmeb\services\notice\NoticeMessage;
use app\services\user\UserServices;
use app\services\user\UserWechatAuthServices;
use modules\channel\wechat_mini\service\RoutineSubscribeServices;
use think\facade\Log;


/**
 * 小程序订阅消息服务
 */
class RoutineTemplateListService extends AbstractNoticeChannel
{

    public static function channelKey(): string
    {
        return 'is_routine';
    }

    public static function channelName(): string
    {
        return '小程序订阅消息';
    }

    /**
     * 本渠道额外配置字段：模板编号/模板ID/跳转链接/模板字段列表
     */
    public static function getExtraFields(): array
    {
        return [
            'tempkey'      => '',
            'tempid'       => '',
            'routine_link' => '',
            'key_list'     => [],
        ];
    }

    /**
     * 发送小程序订阅消息（通知管线入口）
     *
     * 业务变量经后台配置的 key_list（[小程序模板变量名 => 业务变量名]）
     * 映射为小程序订阅消息所需字段（如 thing2、character_string1）；
     * 变量中可携带 link 指定跳转路径。使用前需在后台配置模板ID（extra.tempid）。
     *
     * @param NoticeMessage $message 待投递消息
     * @throws NoticeSendException 用户无 openid、模板ID未配置或网关拒绝
     */
    public function send(NoticeMessage $message): void
    {
        $variables = $message->variables;
        $link = isset($variables['link']) ? (string)$variables['link'] : null;
        unset($variables['link']);

        // 变量映射：配置了 key_list 时，把扁平业务数据映射成小程序模板所需的变量名；
        // 未配置时直接使用业务变量（兼容调用方已按小程序变量名准备数据的场景）
        $keyList = $message->extra['key_list'] ?? [];
        if (!empty($keyList) && is_array($keyList)) {
            $mapped = [];
            foreach ($keyList as $wechatVar => $bizVar) {
                if (isset($variables[$bizVar])) {
                    $mapped[$wechatVar] = $variables[$bizVar];
                }
            }
            $variables = $mapped;
        }

        $this->doSendTemplate((int)$message->uid, $variables, (string)($message->extra['tempid'] ?? ''), $link);
    }

    /**
     * 根据UID获取openid
     * @param int $uid
     * @return mixed
     */
    public function getOpenidByUid(int $uid)
    {
        $isDel = app()->make(UserServices::class)->value(['uid' => $uid], 'is_del');
        if ($isDel) {
            $openid = '';
        } else {
            $openid = app()->make(UserWechatAuthServices::class)->uidToOpenid($uid, 'routine');
        }
        return $openid;
    }

    /**
     * 同步发送订阅消息（不经过队列）
     *
     * @param int $uid 接收用户
     * @param array $data 小程序订阅消息字段数据（已按 key_list 映射）
     * @param string $tempid 订阅消息模板ID
     * @param string|null $link 跳转路径
     * @param string|null $color 文字颜色
     * @throws NoticeSendException 用户无 openid、模板ID未配置或网关拒绝
     */
    public function doSendTemplate(int $uid, array $data, string $tempid, ?string $link = null, ?string $color = null): void
    {
        $openid = $this->getOpenidByUid($uid);
        if (!$openid) {
            // 未订阅/未授权属常见业务状态，警告留痕后按失败处理
            Log::warning('小程序订阅消息发送跳过：用户无小程序 openid（未订阅/未授权） ' . json_encode([
                'uid' => $uid,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            throw new NoticeSendException('用户无小程序 openid（未订阅/未授权）');
        }
        if ($tempid === '') {
            throw new NoticeSendException('小程序订阅消息模板ID未配置');
        }

        $template = new RoutineSubscribeServices();
        $template->to($openid);
        if ($color) {
            $template->color($color);
        }
        if ($link) {
            $template->url($link);
        }
        $result = $template->send($tempid, $data);
        if (!$result) {
            Log::info('小程序订阅消息业务发送结果 ' . json_encode([
                'uid' => $uid,
                'template_id' => $tempid,
                'response' => $template->getLastResponse(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            throw new NoticeSendException('小程序订阅消息发送失败：' . (string)($template->getError() ?: '网关拒绝'));
        }
    }

}
