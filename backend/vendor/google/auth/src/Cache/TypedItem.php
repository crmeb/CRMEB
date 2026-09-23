<?php
/*
 * Copyright 2022 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Auth\Cache;

use Psr\Cache\CacheItemInterface;

/**
 * A cache item.
 *
 * This class will be used by MemoryCacheItemPool and SysVCacheItemPool
 * on PHP 8.0 and above. It is compatible with psr/cache 3.0 (PSR-6).
 * @see Item for compatiblity with previous versions of PHP.
 */
final class TypedItem implements CacheItemInterface
{
    /**
     * @var mixed
     */
    private mixed $value;

    /**
     * @var \DateTimeInterface|null
     */
    private ?\DateTimeInterface $expiration;

    /**
     * @var bool
     */
    private bool $isHit = false;

    /**
     * @param string $key
     */
    public function __construct(
        private string $key
    ) {
        $this->key = $key;
        $this->expiration = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function get(): mixed
    {
        return $this->isHit() ? $this->value : null;
    }

    /**
     * {@inheritdoc}
     */
    public function isHit(): bool
    {
        if (!$this->isHit) {
            return false;
        }

        if ($this->expiration === null) {
            return true;
        }

        return $this->currentTime()->getTimestamp() < $this->expiration->getTimestamp();
    }

    /**
     * {@inheritdoc}
     */
    public function set(mixed $value): static
    {
        $this->isHit = true;
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt($expiration): static
    {
        if ($this->isValidExpiration($expiration)) {
            $this->expiration = $expiration;

            return $this;
        }

        $error = sprintf(
            'Argument 1 passed to %s::expiresAt() must implement interface DateTimeInterface, %s given',
            get_class($this),
            gettype($expiration)
        );

        throw new \TypeError($error);
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter($time): static
    {
        if (is_int($time)) {
            $this->expiration = $this->currentTime()->add(new \DateInterval("PT{$time}S"));
        } elseif ($time instanceof \DateInterval) {
            $this->expiration = $this->currentTime()->add($time);
        } elseif ($time === null) {
            $this->expiration = $time;
        } else {
            $message = 'Argument 1 passed to %s::expiresAfter() must be an ' .
                       'instance of DateInterval or of the type integer, %s given';
            $error = sprintf($message, get_class($this), gettype($time));

            throw new \TypeError($error);
        }

        return $this;
    }

    /**
     * Determines if an expiration is valid based on the rules defined by PSR6.
     *
     * @param mixed $expiration
     * @return bool
     */
    private function isValidExpiration($expiration)
    {
        if ($expiration === null) {
            return true;
        }

        // We test for two types here due to the fact the DateTimeInterface
        // was not introduced until PHP 5.5. Checking for the DateTime type as
        // well allows us to support 5.4.
        if ($expiration instanceof \DateTimeInterface) {
            return true;
        }

        return false;
    }

    /**
     * @return \DateTime
     */
    protected function currentTime()
    {
        return new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
