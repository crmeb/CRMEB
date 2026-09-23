<?php
declare(strict_types=1);

namespace crmeb\services\bot;

use crmeb\basic\BaseManager;
use think\facade\Config;
use think\Container;
use think\helper\Str;

/**
 * 机器人管理器
 * 
 * 支持企业微信、钉钉、飞书群机器人消息发送。
 * 通过静态方法 Bot::send() 快速调用。
 * 
 * @method \extend\bot\EntWechat\Driver entWechat()
 * @method \extend\bot\DingTalk\Driver dingTalk()
 * @method \extend\bot\Feishu\Driver feiShu()
 */
class Bot extends BaseManager
{
    protected $namespace = '\\driver\\bot\\';

    protected $webhook = '';

    public function setWebhook(string $webhook): self
    {
        $this->webhook = $webhook;
        return $this;
    }

    protected function getDefaultDriver()
    {
        return Config::get('bot.default', 'dingTalk');
    }

    protected function invokeClass($class)
    {
        if (!class_exists($class)) {
            throw new \RuntimeException('class not exists: ' . $class);
        }
        $this->getConfigFile();
        $handle = Container::getInstance()->invokeClass($class);
        if ($this->webhook) {
            $handle->setWebhook($this->webhook);
        }
        return $handle;
    }

    protected function resolveClass(string $type): string
    {
        $studly = Str::studly($type);
        $class = $this->namespace . $studly . '\\Driver';
        if (class_exists($class)) {
            return $class;
        }
        $class = $this->namespace . $studly;
        if (class_exists($class)) {
            return $class;
        }
        throw new \InvalidArgumentException("Bot driver [{$type}] not supported.");
    }

    public static function sendText(string $driver, string $webhook, string $content): bool
    {
        $instance = new static($driver);
        $instance->setWebhook($webhook);
        return $instance->sendText($content);
    }

    public static function sendMarkdown(string $driver, string $webhook, string $title, string $content): bool
    {
        $instance = new static($driver);
        $instance->setWebhook($webhook);
        return $instance->sendMarkdown($title, $content);
    }

    public static function sendCard(string $driver, string $webhook, array $card): bool
    {
        $instance = new static($driver);
        $instance->setWebhook($webhook);
        return $instance->sendCard($card);
    }

    public static function send(string $driver, string $webhook, string $content, string $type = 'text', string $title = ''): bool
    {
        $instance = new static($driver);
        $instance->setWebhook($webhook);
        
        return match ($type) {
            'markdown' => $instance->sendMarkdown($title ?: '通知', $content),
            'card' => $instance->sendCard(['content' => $content]),
            default => $instance->sendText($content),
        };
    }
}
