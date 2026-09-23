<?php
declare(strict_types=1);

namespace crmeb\services\bot;

use crmeb\utils\Http;

abstract class BaseBot implements BotInterface
{
    protected string $webhook = '';

    public function setWebhook(string $webhook): void
    {
        $this->webhook = $webhook;
    }

    protected function request(array $data): bool
    {
        if (empty($this->webhook)) return false;
        try {
            $response = Http::postRequest($this->webhook, json_encode($data));
            $result = json_decode($response, true);
            return ($result['errcode'] ?? -1) === 0 || ($result['code'] ?? -1) === 0;
        } catch (\Throwable $e) {
            return false;
        }
    }
}