<?php
declare(strict_types=1);

namespace crmeb\services\bot;

interface BotInterface
{
    public function setWebhook(string $webhook);
    public function sendText(string $content, array $options = []): bool;
    public function sendMarkdown(string $title, string $content, array $options = []): bool;
    public function sendCard(array $card, array $options = []): bool;
}