<?php

declare(strict_types=1);

/*
 * This file is part of the godruoyi/php-snowflake.
 *
 * (c) Godruoyi <g@godruoyi.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Godruoyi\Snowflake;

class Sonyflake extends Snowflake
{
    public const MAX_TIMESTAMP_LENGTH = 39;

    public const MAX_MACHINEID_LENGTH = 16;

    public const MAX_SEQUENCE_LENGTH = 8;

    public const MAX_SEQUENCE_SIZE = (-1 ^ (-1 << self::MAX_SEQUENCE_LENGTH));

    /**
     * Build Sonyflake Instance.
     *
     * @param  int  $machineId  machine ID 0 ~ 65535 (2^16)-1
     */
    public function __construct(protected int $machineId = 0)
    {
        $maxMachineID = -1 ^ (-1 << self::MAX_MACHINEID_LENGTH);

        if ($this->machineId < 0 || $this->machineId > $maxMachineID) {
            throw new \InvalidArgumentException("Invalid machine ID, must be between 0 ~ {$maxMachineID}.");
        }
    }

    /**
     * Get Sonyflake id.
     *
     * @throws SnowflakeException
     */
    public function id(): string
    {
        $elapsedTime = $this->elapsedTime();

        while (($sequence = $this->callResolver($elapsedTime)) > (-1 ^ (-1 << self::MAX_SEQUENCE_LENGTH))) {
            $nextMillisecond = $this->elapsedTime();
            while ($nextMillisecond === $elapsedTime) {
                usleep(1);
                $nextMillisecond = $this->elapsedTime();
            }
            $elapsedTime = $nextMillisecond;
        }

        $this->ensureEffectiveRuntime($elapsedTime);

        return (string) ($elapsedTime << (self::MAX_MACHINEID_LENGTH + self::MAX_SEQUENCE_LENGTH)
            | ($this->machineId << self::MAX_SEQUENCE_LENGTH)
            | ($sequence));
    }

    /**
     * Set start time (millisecond).
     *
     * @throws SnowflakeException
     */
    public function setStartTimeStamp(int $millisecond): self
    {
        $elapsedTime = floor(($this->getCurrentMillisecond() - $millisecond) / 10) | 0;
        if ($elapsedTime < 0) {
            throw new SnowflakeException('The start time cannot be greater than the current time');
        }

        $this->ensureEffectiveRuntime($elapsedTime);

        $this->startTime = $millisecond;

        return $this;
    }

    /**
     * Parse snowflake id.
     *
     * @return array<string, float|int|string>
     */
    public function parseId(string $id, bool $transform = false): array
    {
        $id = decbin((int) $id);
        $length = self::MAX_SEQUENCE_LENGTH + self::MAX_MACHINEID_LENGTH;

        $data = [
            'sequence' => substr($id, -1 * self::MAX_SEQUENCE_LENGTH),
            'machineid' => substr($id, -1 * $length, self::MAX_MACHINEID_LENGTH),
            'timestamp' => substr($id, 0, strlen($id) - $length),
        ];

        return $transform ? array_map(static function ($value) {
            return bindec($value);
        }, $data) : $data;
    }

    /**
     * Get current timestamp.
     */
    public function getDefaultSequenceResolver(): SequenceResolver
    {
        if ($this->defaultSequenceResolver) {
            return $this->defaultSequenceResolver;
        }

        $resolver = new RandomSequenceResolver();
        $resolver->setMaxSequence(self::MAX_SEQUENCE_SIZE);

        return $this->defaultSequenceResolver = $resolver;
    }

    /**
     * The Elapsed Time, unit: 10millisecond.
     */
    private function elapsedTime(): int
    {
        return floor(($this->getCurrentMillisecond() - $this->getStartTimeStamp()) / 10) | 0;
    }

    /**
     * Make sure it's an effective runtime
     *
     * @throws SnowflakeException
     */
    private function ensureEffectiveRuntime(int $elapsedTime): void
    {
        $maxRunTime = -1 ^ (-1 << self::MAX_TIMESTAMP_LENGTH);
        if ($elapsedTime > $maxRunTime) {
            throw new SnowflakeException('Exceeding the maximum life cycle of the algorithm');
        }
    }
}
