<?php
declare(strict_types=1);

namespace crmeb\services\email;

interface EmailInterface
{
    public function send(string $to, string $subject, string $content, array $options = []): bool;
}
