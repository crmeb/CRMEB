<?php
declare(strict_types=1);

namespace crmeb\services\email;

use crmeb\basic\BaseStorage;

abstract class BaseEmail extends BaseStorage
{
    abstract public function send(string $to, string $subject, string $content, array $options = []): bool;
}
