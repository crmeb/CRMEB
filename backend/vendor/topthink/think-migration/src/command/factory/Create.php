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

namespace think\migration\command\factory;

use InvalidArgumentException;
use Phinx\Util\Util;
use RuntimeException;
use think\console\Command;
use think\console\input\Argument as InputArgument;

class Create extends Command
{
    protected function configure()
    {
        $this->setName('factory:create')
            ->setDescription('Create a new model factory')
            ->addArgument('name', InputArgument::REQUIRED, 'What is the name of the model?');
    }

    public function handle()
    {
        $path = $this->getPath();

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        if (!is_dir($path)) {
            throw new InvalidArgumentException(sprintf('Factory directory "%s" does not exist', $path));
        }

        if (!is_writable($path)) {
            throw new InvalidArgumentException(sprintf('Factory directory "%s" is not writable', $path));
        }

        $path      = realpath($path);
        $className = $this->input->getArgument('name');

        if (!Util::isValidPhinxClassName($className)) {
            throw new InvalidArgumentException(sprintf('The migration class name "%s" is invalid. Please use CamelCase format.', $className));
        }

        $filePath = $path . DIRECTORY_SEPARATOR . $className . '.php';

        if (is_file($filePath)) {
            throw new InvalidArgumentException(sprintf('The file "%s" already exists', $filePath));
        }

        // Load the alternative template if it is defined.
        $contents = file_get_contents($this->getTemplate());

        // inject the class names appropriate to this migration
        $contents = strtr($contents, [
            '"ModelClass"' => "\\app\\model\\" . $className . '::class',
        ]);

        if (false === file_put_contents($filePath, $contents)) {
            throw new RuntimeException(sprintf('The file "%s" could not be written to', $path));
        }

        $this->output->writeln('<info>created</info> .' . str_replace(getcwd(), '', $filePath));
    }

    protected function getTemplate()
    {
        return __DIR__ . '/../stubs/factory.stub';
    }

    protected function getPath()
    {
        return $this->app->getRootPath() . 'database' . DIRECTORY_SEPARATOR . 'factories';
    }
}
