<?php

namespace think\migration;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Composer\Script\Event;

class UsePhinx
{
    public static function run(Event $event)
    {
        if (!$event->isDevMode()) {
            return;
        }

        $files = [
            'vendor/robmorgan/phinx/LICENSE'              => 'phinx/LICENSE',
            'vendor/robmorgan/phinx/README.md'            => 'phinx/README.md',
            'vendor/robmorgan/phinx/src/Phinx/Config/'    => 'phinx/Config/',
            'vendor/robmorgan/phinx/src/Phinx/Db/'        => 'phinx/Db/',
            'vendor/robmorgan/phinx/src/Phinx/Migration/' => 'phinx/Migration/',
            'vendor/robmorgan/phinx/src/Phinx/Seed/'      => 'phinx/Seed/',
            'vendor/robmorgan/phinx/src/Phinx/Util/'      => 'phinx/Util/',
        ];

        $io = $event->getIO();

        $fs = new Filesystem;

        //clear
        $fs->remove('phinx');

        foreach ($files as $from => $to) {
            // check pattern
            $pattern = null;
            if (strpos($from, '#') > 0) {
                [$from, $pattern] = explode('#', $from, 2);
            }

            // check the overwrite newer files disable flag (? in end of path)
            $overwriteNewerFiles = substr($to, -1) != '?';
            if (!$overwriteNewerFiles) {
                $to = substr($to, 0, -1);
            }

            // Check the renaming of file for direct moving (file-to-file)
            $isRenameFile = substr($to, -1) != '/' && !is_dir($from);

            if (file_exists($to) && !is_dir($to) && !$isRenameFile) {
                throw new \InvalidArgumentException('Destination directory is not a directory.');
            }

            try {
                if ($isRenameFile) {
                    $fs->mkdir(dirname($to));
                } else {
                    $fs->mkdir($to);
                }
            } catch (IOException $e) {
                throw new \InvalidArgumentException(sprintf('<error>Could not create directory %s.</error>', $to), $e->getCode(), $e);
            }

            if (false === file_exists($from)) {
                throw new \InvalidArgumentException(sprintf('<error>Source directory or file "%s" does not exist.</error>', $from));
            }

            if (is_dir($from)) {
                $finder = new Finder;
                $finder->files()->ignoreDotFiles(false)->in($from);

                if ($pattern) {
                    $finder->path("#{$pattern}#");
                }

                foreach ($finder as $file) {
                    $dest = sprintf('%s/%s', $to, $file->getRelativePathname());

                    try {
                        $fs->copy($file, $dest, $overwriteNewerFiles);

                        // replace namespace
                        $content  = file_get_contents($dest);
                        $replaces = [
                            'use Symfony\Component\Console\Input\InputInterface;'   => 'use think\console\Input as InputInterface;',
                            'use Symfony\Component\Console\Output\OutputInterface;' => 'use think\console\Output as OutputInterface;',
                            '\Symfony\Component\Console\Output\OutputInterface'     => '\think\console\Output',
                            '\Symfony\Component\Console\Input\InputInterface'       => '\think\console\Input',
                            'use Symfony\Component\Console\Output\NullOutput;'      => 'use think\migration\NullOutput;',
                        ];
                        $content  = str_replace(array_keys($replaces), array_values($replaces), $content);
                        file_put_contents($dest, $content);
                    } catch (IOException $e) {
                        throw new \InvalidArgumentException(sprintf('<error>Could not copy %s</error>', $file->getBaseName()), $e->getCode(), $e);
                    }
                }
            } else {
                try {
                    if ($isRenameFile) {
                        $fs->copy($from, $to, $overwriteNewerFiles);
                    } else {
                        $fs->copy($from, $to . '/' . basename($from), $overwriteNewerFiles);
                    }
                } catch (IOException $e) {
                    throw new \InvalidArgumentException(sprintf('<error>Could not copy %s</error>', $from), $e->getCode(), $e);
                }
            }

            $io->write(sprintf('Copied file(s) from <comment>%s</comment> to <comment>%s</comment>.', $from, $to));
        }

        //clear
        $fs->remove('vendor/robmorgan/phinx');
    }
}
