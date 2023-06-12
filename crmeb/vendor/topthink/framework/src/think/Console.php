<?php
// +----------------------------------------------------------------------
// | TopThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangyajun <448901948@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think;

use Closure;
use InvalidArgumentException;
use LogicException;
use think\console\Command;
use think\console\command\Clear;
use think\console\command\Help;
use think\console\command\Help as HelpCommand;
use think\console\command\Lists;
use think\console\command\make\Command as MakeCommand;
use think\console\command\make\Controller;
use think\console\command\make\Event;
use think\console\command\make\Listener;
use think\console\command\make\Middleware;
use think\console\command\make\Model;
use think\console\command\make\Service;
use think\console\command\make\Subscribe;
use think\console\command\make\Validate;
use think\console\command\optimize\Route;
use think\console\command\optimize\Schema;
use think\console\command\RouteList;
use think\console\command\RunServer;
use think\console\command\ServiceDiscover;
use think\console\command\VendorPublish;
use think\console\command\Version;
use think\console\Input;
use think\console\input\Argument as InputArgument;
use think\console\input\Definition as InputDefinition;
use think\console\input\Option as InputOption;
use think\console\Output;
use think\console\output\driver\Buffer;

/**
 * 控制台应用管理类
 */
class Console
{

    protected $app;

    /** @var Command[] */
    protected $commands = [];

    protected $wantHelps = false;

    protected $catchExceptions = true;
    protected $autoExit        = true;
    protected $definition;
    protected $defaultCommand  = 'list';

    protected $defaultCommands = [
        'help'             => Help::class,
        'list'             => Lists::class,
        'clear'            => Clear::class,
        'make:command'     => MakeCommand::class,
        'make:controller'  => Controller::class,
        'make:model'       => Model::class,
        'make:middleware'  => Middleware::class,
        'make:validate'    => Validate::class,
        'make:event'       => Event::class,
        'make:listener'    => Listener::class,
        'make:service'     => Service::class,
        'make:subscribe'   => Subscribe::class,
        'optimize:route'   => Route::class,
        'optimize:schema'  => Schema::class,
        'run'              => RunServer::class,
        'version'          => Version::class,
        'route:list'       => RouteList::class,
        'service:discover' => ServiceDiscover::class,
        'vendor:publish'   => VendorPublish::class,
    ];

    /**
     * 启动器
     * @var array
     */
    protected static $startCallbacks = [];

    public function __construct(App $app)
    {
        $this->app = $app;

        $this->initialize();

        $this->definition = $this->getDefaultInputDefinition();

        //加载指令
        $this->loadCommands();

        $this->start();
    }

    /**
     * 初始化
     */
    protected function initialize()
    {
        if (!$this->app->initialized()) {
            $this->app->initialize();
        }
        $this->makeRequest();
    }

    /**
     * 构造request
     */
    protected function makeRequest()
    {
        $url = $this->app->config->get('app.url', 'http://localhost');

        $components = parse_url($url);

        $server = $_SERVER;

        if (isset($components['path'])) {
            $server = array_merge($server, [
                'SCRIPT_FILENAME' => $components['path'],
                'SCRIPT_NAME'     => $components['path'],
                'REQUEST_URI'     => $components['path'],
            ]);
        }

        if (isset($components['host'])) {
            $server['SERVER_NAME'] = $components['host'];
            $server['HTTP_HOST']   = $components['host'];
        }

        if (isset($components['scheme'])) {
            if ('https' === $components['scheme']) {
                $server['HTTPS']       = 'on';
                $server['SERVER_PORT'] = 443;
            } else {
                unset($server['HTTPS']);
                $server['SERVER_PORT'] = 80;
            }
        }

        if (isset($components['port'])) {
            $server['SERVER_PORT'] = $components['port'];
            $server['HTTP_HOST'] .= ':' . $components['port'];
        }

        /** @var Request $request */
        $request = $this->app->make('request');

        $request->withServer($server);
    }

    /**
     * 添加初始化器
     * @param Closure $callback
     */
    public static function starting(Closure $callback): void
    {
        static::$startCallbacks[] = $callback;
    }

    /**
     * 清空启动器
     */
    public static function flushStartCallbacks(): void
    {
        static::$startCallbacks = [];
    }

    /**
     * 设置执行用户
     * @param $user
     */
    public static function setUser(string $user): void
    {
        if (extension_loaded('posix')) {
            $user = posix_getpwnam($user);

            if (!empty($user)) {
                posix_setgid($user['gid']);
                posix_setuid($user['uid']);
            }
        }
    }

    /**
     * 启动
     */
    protected function start(): void
    {
        foreach (static::$startCallbacks as $callback) {
            $callback($this);
        }
    }

    /**
     * 加载指令
     * @access protected
     */
    protected function loadCommands(): void
    {
        $commands = $this->app->config->get('console.commands', []);
        $commands = array_merge($this->defaultCommands, $commands);

        $this->addCommands($commands);
    }

    /**
     * @access public
     * @param string $command
     * @param array $parameters
     * @param string $driver
     * @return Output|Buffer
     */
    public function call(string $command, array $parameters = [], string $driver = 'buffer')
    {
        array_unshift($parameters, $command);

        $input  = new Input($parameters);
        $output = new Output($driver);

        $this->setCatchExceptions(false);
        $this->find($command)->run($input, $output);

        return $output;
    }

    /**
     * 执行当前的指令
     * @access public
     * @return int
     * @throws \Exception
     * @api
     */
    public function run()
    {
        $input  = new Input();
        $output = new Output();

        $this->configureIO($input, $output);

        try {
            $exitCode = $this->doRun($input, $output);
        } catch (\Exception $e) {
            if (!$this->catchExceptions) {
                throw $e;
            }

            $output->renderException($e);

            $exitCode = $e->getCode();
            if (is_numeric($exitCode)) {
                $exitCode = (int) $exitCode;
                if (0 === $exitCode) {
                    $exitCode = 1;
                }
            } else {
                $exitCode = 1;
            }
        }

        if ($this->autoExit) {
            if ($exitCode > 255) {
                $exitCode = 255;
            }

            exit($exitCode);
        }

        return $exitCode;
    }

    /**
     * 执行指令
     * @access public
     * @param Input $input
     * @param Output $output
     * @return int
     */
    public function doRun(Input $input, Output $output)
    {
        if (true === $input->hasParameterOption(['--version', '-V'])) {
            $output->writeln($this->getLongVersion());

            return 0;
        }

        $name = $this->getCommandName($input);

        if (true === $input->hasParameterOption(['--help', '-h'])) {
            if (!$name) {
                $name  = 'help';
                $input = new Input(['help']);
            } else {
                $this->wantHelps = true;
            }
        }

        if (!$name) {
            $name  = $this->defaultCommand;
            $input = new Input([$this->defaultCommand]);
        }

        $command = $this->find($name);

        return $this->doRunCommand($command, $input, $output);
    }

    /**
     * 设置输入参数定义
     * @access public
     * @param InputDefinition $definition
     */
    public function setDefinition(InputDefinition $definition): void
    {
        $this->definition = $definition;
    }

    /**
     * 获取输入参数定义
     * @access public
     * @return InputDefinition The InputDefinition instance
     */
    public function getDefinition(): InputDefinition
    {
        return $this->definition;
    }

    /**
     * Gets the help message.
     * @access public
     * @return string A help message.
     */
    public function getHelp(): string
    {
        return $this->getLongVersion();
    }

    /**
     * 是否捕获异常
     * @access public
     * @param bool $boolean
     * @api
     */
    public function setCatchExceptions(bool $boolean): void
    {
        $this->catchExceptions = $boolean;
    }

    /**
     * 是否自动退出
     * @access public
     * @param bool $boolean
     * @api
     */
    public function setAutoExit(bool $boolean): void
    {
        $this->autoExit = $boolean;
    }

    /**
     * 获取完整的版本号
     * @access public
     * @return string
     */
    public function getLongVersion(): string
    {
        if ($this->app->version()) {
            return sprintf('version <comment>%s</comment>', $this->app->version());
        }

        return '<info>Console Tool</info>';
    }

    /**
     * 添加指令集
     * @access public
     * @param array $commands
     */
    public function addCommands(array $commands): void
    {
        foreach ($commands as $key => $command) {
            if (is_subclass_of($command, Command::class)) {
                // 注册指令
                $this->addCommand($command, is_numeric($key) ? '' : $key);
            }
        }
    }

    /**
     * 添加一个指令
     * @access public
     * @param string|Command $command 指令对象或者指令类名
     * @param string $name 指令名 留空则自动获取
     * @return Command|void
     */
    public function addCommand($command, string $name = '')
    {
        if ($name) {
            $this->commands[$name] = $command;
            return;
        }

        if (is_string($command)) {
            $command = $this->app->invokeClass($command);
        }

        $command->setConsole($this);

        if (!$command->isEnabled()) {
            $command->setConsole(null);
            return;
        }

        $command->setApp($this->app);

        if (null === $command->getDefinition()) {
            throw new LogicException(sprintf('Command class "%s" is not correctly initialized. You probably forgot to call the parent constructor.', get_class($command)));
        }

        $this->commands[$command->getName()] = $command;

        foreach ($command->getAliases() as $alias) {
            $this->commands[$alias] = $command;
        }

        return $command;
    }

    /**
     * 获取指令
     * @access public
     * @param string $name 指令名称
     * @return Command
     * @throws InvalidArgumentException
     */
    public function getCommand(string $name): Command
    {
        if (!isset($this->commands[$name])) {
            throw new InvalidArgumentException(sprintf('The command "%s" does not exist.', $name));
        }

        $command = $this->commands[$name];

        if (is_string($command)) {
            $command = $this->app->invokeClass($command);
            /** @var Command $command */
            $command->setConsole($this);
            $command->setApp($this->app);
        }

        if ($this->wantHelps) {
            $this->wantHelps = false;

            /** @var HelpCommand $helpCommand */
            $helpCommand = $this->getCommand('help');
            $helpCommand->setCommand($command);

            return $helpCommand;
        }

        return $command;
    }

    /**
     * 某个指令是否存在
     * @access public
     * @param string $name 指令名称
     * @return bool
     */
    public function hasCommand(string $name): bool
    {
        return isset($this->commands[$name]);
    }

    /**
     * 获取所有的命名空间
     * @access public
     * @return array
     */
    public function getNamespaces(): array
    {
        $namespaces = [];
        foreach ($this->commands as $key => $command) {
            if (is_string($command)) {
                $namespaces = array_merge($namespaces, $this->extractAllNamespaces($key));
            } else {
                $namespaces = array_merge($namespaces, $this->extractAllNamespaces($command->getName()));

                foreach ($command->getAliases() as $alias) {
                    $namespaces = array_merge($namespaces, $this->extractAllNamespaces($alias));
                }
            }
        }

        return array_values(array_unique(array_filter($namespaces)));
    }

    /**
     * 查找注册命名空间中的名称或缩写。
     * @access public
     * @param string $namespace
     * @return string
     * @throws InvalidArgumentException
     */
    public function findNamespace(string $namespace): string
    {
        $allNamespaces = $this->getNamespaces();
        $expr          = preg_replace_callback('{([^:]+|)}', function ($matches) {
            return preg_quote($matches[1]) . '[^:]*';
        }, $namespace);
        $namespaces    = preg_grep('{^' . $expr . '}', $allNamespaces);

        if (empty($namespaces)) {
            $message = sprintf('There are no commands defined in the "%s" namespace.', $namespace);

            if ($alternatives = $this->findAlternatives($namespace, $allNamespaces)) {
                if (1 == count($alternatives)) {
                    $message .= "\n\nDid you mean this?\n    ";
                } else {
                    $message .= "\n\nDid you mean one of these?\n    ";
                }

                $message .= implode("\n    ", $alternatives);
            }

            throw new InvalidArgumentException($message);
        }

        $exact = in_array($namespace, $namespaces, true);
        if (count($namespaces) > 1 && !$exact) {
            throw new InvalidArgumentException(sprintf('The namespace "%s" is ambiguous (%s).', $namespace, $this->getAbbreviationSuggestions(array_values($namespaces))));
        }

        return $exact ? $namespace : reset($namespaces);
    }

    /**
     * 查找指令
     * @access public
     * @param string $name 名称或者别名
     * @return Command
     * @throws InvalidArgumentException
     */
    public function find(string $name): Command
    {
        $allCommands = array_keys($this->commands);

        $expr = preg_replace_callback('{([^:]+|)}', function ($matches) {
            return preg_quote($matches[1]) . '[^:]*';
        }, $name);

        $commands = preg_grep('{^' . $expr . '}', $allCommands);

        if (empty($commands) || count(preg_grep('{^' . $expr . '$}', $commands)) < 1) {
            if (false !== $pos = strrpos($name, ':')) {
                $this->findNamespace(substr($name, 0, $pos));
            }

            $message = sprintf('Command "%s" is not defined.', $name);

            if ($alternatives = $this->findAlternatives($name, $allCommands)) {
                if (1 == count($alternatives)) {
                    $message .= "\n\nDid you mean this?\n    ";
                } else {
                    $message .= "\n\nDid you mean one of these?\n    ";
                }
                $message .= implode("\n    ", $alternatives);
            }

            throw new InvalidArgumentException($message);
        }

        $exact = in_array($name, $commands, true);
        if (count($commands) > 1 && !$exact) {
            $suggestions = $this->getAbbreviationSuggestions(array_values($commands));

            throw new InvalidArgumentException(sprintf('Command "%s" is ambiguous (%s).', $name, $suggestions));
        }

        return $this->getCommand($exact ? $name : reset($commands));
    }

    /**
     * 获取所有的指令
     * @access public
     * @param string $namespace 命名空间
     * @return Command[]
     * @api
     */
    public function all(string $namespace = null): array
    {
        if (null === $namespace) {
            return $this->commands;
        }

        $commands = [];
        foreach ($this->commands as $name => $command) {
            if ($this->extractNamespace($name, substr_count($namespace, ':') + 1) === $namespace) {
                $commands[$name] = $command;
            }
        }

        return $commands;
    }

    /**
     * 配置基于用户的参数和选项的输入和输出实例。
     * @access protected
     * @param Input $input 输入实例
     * @param Output $output 输出实例
     */
    protected function configureIO(Input $input, Output $output): void
    {
        if (true === $input->hasParameterOption(['--ansi'])) {
            $output->setDecorated(true);
        } elseif (true === $input->hasParameterOption(['--no-ansi'])) {
            $output->setDecorated(false);
        }

        if (true === $input->hasParameterOption(['--no-interaction', '-n'])) {
            $input->setInteractive(false);
        }

        if (true === $input->hasParameterOption(['--quiet', '-q'])) {
            $output->setVerbosity(Output::VERBOSITY_QUIET);
        } elseif ($input->hasParameterOption('-vvv') || $input->hasParameterOption('--verbose=3') || $input->getParameterOption('--verbose') === 3) {
            $output->setVerbosity(Output::VERBOSITY_DEBUG);
        } elseif ($input->hasParameterOption('-vv') || $input->hasParameterOption('--verbose=2') || $input->getParameterOption('--verbose') === 2) {
            $output->setVerbosity(Output::VERBOSITY_VERY_VERBOSE);
        } elseif ($input->hasParameterOption('-v') || $input->hasParameterOption('--verbose=1') || $input->hasParameterOption('--verbose') || $input->getParameterOption('--verbose')) {
            $output->setVerbosity(Output::VERBOSITY_VERBOSE);
        }
    }

    /**
     * 执行指令
     * @access protected
     * @param Command $command 指令实例
     * @param Input $input 输入实例
     * @param Output $output 输出实例
     * @return int
     * @throws \Exception
     */
    protected function doRunCommand(Command $command, Input $input, Output $output)
    {
        return $command->run($input, $output);
    }

    /**
     * 获取指令的基础名称
     * @access protected
     * @param Input $input
     * @return string
     */
    protected function getCommandName(Input $input): string
    {
        return $input->getFirstArgument() ?: '';
    }

    /**
     * 获取默认输入定义
     * @access protected
     * @return InputDefinition
     */
    protected function getDefaultInputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this console version'),
            new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Do not output any message'),
            new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
            new InputOption('--ansi', '', InputOption::VALUE_NONE, 'Force ANSI output'),
            new InputOption('--no-ansi', '', InputOption::VALUE_NONE, 'Disable ANSI output'),
            new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Do not ask any interactive question'),
        ]);
    }

    /**
     * 获取可能的建议
     * @access private
     * @param array $abbrevs
     * @return string
     */
    private function getAbbreviationSuggestions(array $abbrevs): string
    {
        return sprintf('%s, %s%s', $abbrevs[0], $abbrevs[1], count($abbrevs) > 2 ? sprintf(' and %d more', count($abbrevs) - 2) : '');
    }

    /**
     * 返回命名空间部分
     * @access public
     * @param string $name 指令
     * @param int $limit 部分的命名空间的最大数量
     * @return string
     */
    public function extractNamespace(string $name, int $limit = 0): string
    {
        $parts = explode(':', $name);
        array_pop($parts);

        return implode(':', 0 === $limit ? $parts : array_slice($parts, 0, $limit));
    }

    /**
     * 查找可替代的建议
     * @access private
     * @param string $name
     * @param array|\Traversable $collection
     * @return array
     */
    private function findAlternatives(string $name, $collection): array
    {
        $threshold    = 1e3;
        $alternatives = [];

        $collectionParts = [];
        foreach ($collection as $item) {
            $collectionParts[$item] = explode(':', $item);
        }

        foreach (explode(':', $name) as $i => $subname) {
            foreach ($collectionParts as $collectionName => $parts) {
                $exists = isset($alternatives[$collectionName]);
                if (!isset($parts[$i]) && $exists) {
                    $alternatives[$collectionName] += $threshold;
                    continue;
                } elseif (!isset($parts[$i])) {
                    continue;
                }

                $lev = levenshtein($subname, $parts[$i]);
                if ($lev <= strlen($subname) / 3 || '' !== $subname && false !== strpos($parts[$i], $subname)) {
                    $alternatives[$collectionName] = $exists ? $alternatives[$collectionName] + $lev : $lev;
                } elseif ($exists) {
                    $alternatives[$collectionName] += $threshold;
                }
            }
        }

        foreach ($collection as $item) {
            $lev = levenshtein($name, $item);
            if ($lev <= strlen($name) / 3 || false !== strpos($item, $name)) {
                $alternatives[$item] = isset($alternatives[$item]) ? $alternatives[$item] - $lev : $lev;
            }
        }

        $alternatives = array_filter($alternatives, function ($lev) use ($threshold) {
            return $lev < 2 * $threshold;
        });
        asort($alternatives);

        return array_keys($alternatives);
    }

    /**
     * 返回所有的命名空间
     * @access private
     * @param string $name
     * @return array
     */
    private function extractAllNamespaces(string $name): array
    {
        $parts      = explode(':', $name, -1);
        $namespaces = [];

        foreach ($parts as $part) {
            if (count($namespaces)) {
                $namespaces[] = end($namespaces) . ':' . $part;
            } else {
                $namespaces[] = $part;
            }
        }

        return $namespaces;
    }

}
