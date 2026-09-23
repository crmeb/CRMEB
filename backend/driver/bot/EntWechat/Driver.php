<?php
declare(strict_types=1);

namespace driver\bot\EntWechat;

use crmeb\services\bot\BaseBot;

class Driver extends BaseBot
{
    public function sendText(string $content, array $options = []): bool
    {
        return $this->request([
            'msgtype' => 'text',
            'text' => ['content' => $content],
        ]);
    }

    public function sendMarkdown(string $title, string $content, array $options = []): bool
    {
        // 企业微信 markdown 不支持 title 字段，将 title 合并到 content
        $fullContent = "**{$title}**\n>{$content}";
        return $this->request([
            'msgtype' => 'markdown',
            'markdown' => ['content' => $fullContent],
        ]);
    }

    public function sendCard(array $card, array $options = []): bool
    {
        return $this->request([
            'msgtype' => 'news',
            'news' => ['articles' => [$card]],
        ]);
    }
}