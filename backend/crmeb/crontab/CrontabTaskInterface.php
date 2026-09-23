<?php
declare(strict_types=1);

namespace crmeb\crontab;

interface CrontabTaskInterface
{
    public function getMark(): string;

    public function getName(): string;

    public function getDescription(): string;

    public function handle(): void;
}
