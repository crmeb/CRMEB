<?php
declare(strict_types=1);

namespace driver\bot\Feishu;

use crmeb\services\bot\BaseBot;

class Driver extends BaseBot
{
    public function sendText(string $content, array $options = []): bool
    {
        return $this->request([
            'msg_type' => 'text',
            'content' => ['text' => $content],
        ]);
    }

    public function sendMarkdown(string $title, string $content, array $options = []): bool
    {
        $postData = [
            'title' => $title,
            'content' => [
                [
                    [
                        'tag' => 'text',
                        'text' => $content,
                    ],
                ],
            ],
        ];
        return $this->request([
            'msg_type' => 'post',
            'content' => ['post' => ['zh_cn' => $postData]],
        ]);
    }

    public function sendCard(array $card, array $options = []): bool
    {
        return $this->request([
            'msg_type' => 'interactive',
            'card' => $card,
        ]);
    }
}