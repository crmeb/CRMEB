<?php
namespace crmeb\repositories;

/**
 * 微信公众号操作类
 * Class MessageRepositories
 * @package crmeb\repositories
 */
class MessageRepositories
{
    /**
     * 位置 事件
     * @param $message
     * @return string
     */
   public static function wechatEventLocation($message)
   {
       return 'location';
   }

    /**
     * 跳转URL  事件
     * @param $message
     * @return string
     */
   public static function wechatEventView($message)
   {
       return 'view';
   }

    /**
     * 图片 消息
     * @param $message
     * @return string
     */
   public static function wechatMessageImage($message)
   {
       return 'image';
   }

    /**
     * 语音 消息
     * @param $message
     * @return string
     */
   public static function wechatMessageVoice($message)
   {
       return 'voice';
   }

    /**
     * 视频 消息
     * @param $message
     * @return string
     */
   public static function wechatMessageVideo($message)
   {
       return 'video';
   }

    /**
     * 位置  消息
     */
   public static function wechatMessageLocation($message)
   {
       return 'location';
   }

    /**
     * 链接   消息
     * @param $message
     * @return string
     */
   public static function wechatMessageLink($message)
   {
       return 'link';
   }

    /**
     * 其它消息  消息
     */
   public static function wechatMessageOther($message)
   {
       return 'other';
   }
}