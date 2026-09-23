<?php

namespace Overtrue\Socialite;

use ArrayAccess;
use JsonSerializable;

class Config implements ArrayAccess, JsonSerializable
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $config = $this->config;

        if (isset($config[$key])) {
            return $config[$key];
        }

        foreach (\explode('.', $key) as $segment) {
            if (! \is_array($config) || ! \array_key_exists($segment, $config)) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    public function set(string $key, mixed $value): array
    {
        $keys = \explode('.', $key);
        $config = &$this->config;

        while (\count($keys) > 1) {
            $key = \array_shift($keys);
            if (! isset($config[$key]) || ! \is_array($config[$key])) {
                $config[$key] = [];
            }
            $config = &$config[$key];
        }

        $config[\array_shift($keys)] = $value;

        return $config;
    }

    public function has(string $key): bool
    {
        return (bool) $this->get($key);
    }

    public function offsetExists(mixed $offset): bool
    {
        \is_string($offset) || throw new Exceptions\InvalidArgumentException('The $offset must be type of string here.');

        return \array_key_exists($offset, $this->config);
    }

    public function offsetGet(mixed $offset): mixed
    {
        \is_string($offset) || throw new Exceptions\InvalidArgumentException('The $offset must be type of string here.');

        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        \is_string($offset) || throw new Exceptions\InvalidArgumentException('The $offset must be type of string here.');

        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        \is_string($offset) || throw new Exceptions\InvalidArgumentException('The $offset must be type of string here.');

        $this->set($offset, null);
    }

    public function jsonSerialize(): array
    {
        return $this->config;
    }

    public function __toString(): string
    {
        return \json_encode($this, \JSON_UNESCAPED_UNICODE) ?: '';
    }
}
