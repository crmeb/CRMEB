<?php
declare(strict_types=1);

namespace crmeb\crontab;

class CrontabTaskRegistry
{
    protected array $handlers = [];

    protected array $instances = [];

    public function register(string $mark, string $handlerClass): void
    {
        $this->handlers[$mark] = $handlerClass;
    }

    public function get(string $mark): CrontabTaskInterface
    {
        if (!isset($this->instances[$mark])) {
            if (!isset($this->handlers[$mark])) {
                throw new \RuntimeException("定时任务 {$mark} 未注册");
            }
            $class = $this->handlers[$mark];
            $this->instances[$mark] = new $class();
        }
        return $this->instances[$mark];
    }

    public function has(string $mark): bool
    {
        return isset($this->handlers[$mark]);
    }

    public function all(): array
    {
        return array_keys($this->handlers);
    }

    public function allInfo(): array
    {
        $list = [];
        foreach ($this->handlers as $mark => $class) {
            $handler = $this->get($mark);
            $list[$mark] = [
                'name' => $handler->getName(),
                'description' => $handler->getDescription(),
            ];
        }
        return $list;
    }

    public function allFullInfo(): array
    {
        $list = [];
        foreach ($this->handlers as $mark => $class) {
            $handler = $this->get($mark);
            $list[] = [
                'mark' => $handler->getMark(),
                'name' => $handler->getName(),
                'description' => $handler->getDescription(),
                'class' => $class,
            ];
        }
        return $list;
    }
}
