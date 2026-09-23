<?php

declare(strict_types=1);

namespace cweagans\Composer\Capability;

use Composer\Plugin\Capability\CommandProvider as CommandProviderInterface;
use cweagans\Composer\Command\DoctorCommand;
use cweagans\Composer\Command\RepatchCommand;
use cweagans\Composer\Command\RelockCommand;

class CommandProvider implements CommandProviderInterface
{
    public function getCommands(): array
    {
        return [
            new DoctorCommand(),
            new RepatchCommand(),
            new RelockCommand(),
        ];
    }
}
