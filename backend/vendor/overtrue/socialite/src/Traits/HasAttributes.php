<?php

namespace Overtrue\Socialite\Traits;

use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Exceptions;

trait HasAttributes
{
    protected array $attributes = [];

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    public function setAttribute(string $name, mixed $value): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function merge(array $attributes): self
    {
        $this->attributes = \array_merge($this->attributes, $attributes);

        return $this;
    }

    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->attributes);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->getAttribute($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setAttribute($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }

    public function __get(string $property): mixed
    {
        return $this->getAttribute($property);
    }

    #[Pure]
    public function toArray(): array
    {
        return $this->getAttributes();
    }

    public function toJSON(): string
    {
        $result = \json_encode($this->getAttributes(), JSON_UNESCAPED_UNICODE);

        $result === false && throw new Exceptions\Exception('Cannot Processing this instance as JSON stringify.');

        return $result;
    }
}
