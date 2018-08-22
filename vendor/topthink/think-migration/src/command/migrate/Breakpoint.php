<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\migration\command\migrate;

use think\console\Input;
use think\console\input\Option as InputOption;
use think\console\Output;
use think\migration\command\Migrate;

class Breakpoint extends Migrate
{
    protected function configure()
    {
        $this->setName('migrate:breakpoint')
             ->setDescription('Manage breakpoints')
             ->addOption('--target', '-t', InputOption::VALUE_REQUIRED, 'The version number to set or clear a breakpoint against')
             ->addOption('--remove-all', '-r', InputOption::VALUE_NONE, 'Remove all breakpoints')
             ->setHelp(<<<EOT
                 The <info>breakpoint</info> command allows you to set or clear a breakpoint against a specific target to inhibit rollbacks beyond a certain target.
If no target is supplied then the most recent migration will be used.
You cannot specify un-migrated targets

<info>phinx breakpoint</info>
<info>phinx breakpoint -t 20110103081132</info>
<info>phinx breakpoint -r</info>
EOT
             );
    }

    protected function execute(Input $input, Output $output)
    {
        $version   = $input->getOption('target');
        $removeAll = $input->getOption('remove-all');

        if ($version && $removeAll) {
            throw new \InvalidArgumentException('Cannot toggle a breakpoint and remove all breakpoints at the same time.');
        }

        // Remove all breakpoints
        if ($removeAll) {
            $this->removeBreakpoints();
        } else {
            // Toggle the breakpoint.
            $this->toggleBreakpoint($version);
        }
    }

    protected function toggleBreakpoint($version)
    {
        $migrations = $this->getMigrations();
        $versions   = $this->getVersionLog();

        if (empty($versions) || empty($migrations)) {
            return;
        }

        if (null === $version) {
            $lastVersion = end($versions);
            $version     = $lastVersion['version'];
        }

        if (0 != $version && !isset($migrations[$version])) {
            $this->output->writeln(sprintf('<comment>warning</comment> %s is not a valid version', $version));
            return;
        }

        $this->getAdapter()->toggleBreakpoint($migrations[$version]);

        $versions = $this->getVersionLog();

        $this->output->writeln(' Breakpoint ' . ($versions[$version]['breakpoint'] ? 'set' : 'cleared') . ' for <info>' . $version . '</info>' . ' <comment>' . $migrations[$version]->getName() . '</comment>');
    }

    /**
     * Remove all breakpoints
     *
     * @return void
     */
    protected function removeBreakpoints()
    {
        $this->output->writeln(sprintf(' %d breakpoints cleared.', $this->getAdapter()->resetAllBreakpoints()));
    }
}
