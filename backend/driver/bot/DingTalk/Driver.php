<?php
declare(strict_types=1);

namespace driver\bot\DingTalk;

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
        return $this->request([
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => $title,
                'text' => $content,
            ],
        ]);
    }

    public function sendCard(array $card, array $options = []): bool
    {
        return $this->request([
            'msgtype' => 'actionCard',
            'actionCard' => $card,
        ]);
    }
}