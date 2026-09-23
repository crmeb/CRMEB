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

use Throwable;

class FileLockResolver implements SequenceResolver
{
    /**
     * We should always use exclusive lock to avoid the problem of concurrent access.
     */
    public const FlockLockOperation = LOCK_EX;

    public const FileOpenMode = 'r+';

    /**
     * For each lock file, we save 6,000 items, It can contain data generated within 10 minutes,
     * we believe is sufficient for the snowflake algorithm.
     *
     * 10m = 600s = 6000 ms
     */
    public static int $maxItems = 6000;

    public static int $shardCount = 32;

    /**
     * @throws SnowflakeException
     */
    public function __construct(protected string $lockFileDir)
    {
        $this->lockFileDir = $this->preparePath($lockFileDir);
    }

    /**
     * @throws SnowflakeException
     */
    public function sequence(int $currentTime): int
    {
        $filePath = $this->createShardLockFile($this->getShardLockIndex($currentTime));

        return $this->getSequence($filePath, $currentTime);
    }

    /**
     * Get next sequence. move lock/unlock in the same method to avoid lock file not release, this
     * will be more friendly to test.
     *
     * @throws SnowflakeException
     */
    protected function getSequence(string $filePath, int $currentTime): int
    {
        $f = null;

        if (! file_exists($filePath)) {
            throw new SnowflakeException(sprintf('the lock file %s not exists', $filePath));
        }

        try {
            $f = @fopen($filePath, static::FileOpenMode);

            if (! $f) {
                throw new SnowflakeException(sprintf('can not open this file %s', $filePath));
            }

            // we always use exclusive lock to avoid the problem of concurrent access.
            // so we don't need to check the return value of flock.
            flock($f, static::FlockLockOperation);
        } catch (Throwable $e) {
            $this->unlock($f);

            throw new SnowflakeException(sprintf('can not open/lock this file %s', $filePath), $e->getCode(), $e);
        }

        // We may get this error if the file contains invalid json, when you get this error,
        // may you can try to delete the invalid lock file directly.
        if (is_null($contents = $this->getContents($f))) {
            $this->unlock($f);

            throw new SnowflakeException(sprintf('file %s is not a valid lock file.', $filePath));
        }

        $this->updateContents($contents = $this->incrementSequenceWithSpecifyTime(
            $this->cleanOldSequences($contents),
            $currentTime
        ), $f);

        $this->unlock($f);

        return $contents[$currentTime];
    }

    /**
     * Unlock and close file.
     *
     * @param  resource|false|null  $f
     */
    protected function unlock($f): void
    {
        if (is_resource($f)) {
            flock($f, LOCK_UN);
            fclose($f);
        }
    }

    /**
     * @param  array<int, int>  $contents
     * @param  resource  $f
     */
    public function updateContents(array $contents, $f): bool
    {
        return ftruncate($f, 0) && rewind($f)
            && (fwrite($f, serialize($contents)) !== false);
    }

    /**
     * Increment sequence with specify time. if current time is not set in the lock file
     * set it to 1, otherwise increment it.
     *
     * @param  array<int, int>  $contents
     * @return array<int, int>
     */
    public function incrementSequenceWithSpecifyTime(array $contents, int $currentTime): array
    {
        $contents[$currentTime] = isset($contents[$currentTime]) ? $contents[$currentTime] + 1 : 1;

        return $contents;
    }

    /**
     * Clean the old content, we only save the data generated within 10 minutes.
     *
     * @param  array<int, int>  $contents
     * @return array<int, int>
     */
    public function cleanOldSequences(array $contents): array
    {
        ksort($contents); // sort by timestamp

        if (count($contents) > static::$maxItems) {
            $contents = array_slice($contents, -static::$maxItems, null, true);
        }

        return $contents;
    }

    /**
     * Remove all lock files, we only delete the file that name is match the pattern.
     */
    public function cleanAllLocksFile(): void
    {
        $files = glob($this->lockFileDir.'/*');

        if (! $files) {
            return;
        }

        foreach ($files as $file) {
            if (is_file($file) && preg_match('/snowflake-(\d+)\.lock$/', $file)) {
                unlink($file);
            }
        }
    }

    /**
     * Get resource contents, If the contents are invalid json, return null.
     *
     * @param  resource  $f
     * @return array<int, int>|null
     */
    public function getContents($f): ?array
    {
        $content = '';

        while (! feof($f)) {
            $content .= fread($f, 1024);
        }

        $content = trim($content);

        if (empty($content)) {
            return [];
        }

        try {
            if (is_array($data = @unserialize($content))) {
                return $data;
            }
        } catch (Throwable) {
        }

        return null;
    }

    /**
     * @see https://en.wikipedia.org/wiki/Fowler%E2%80%93Noll%E2%80%93Vo_hash_function
     */
    public function fnv(string $str): float
    {
        $hash = 2166136261;

        $length = strlen($str);
        for ($i = 0; $i < $length; $i++) {
            $hash ^= ord($str[$i]);
            $hash *= 0x01000193;
            $hash &= 0xFFFFFFFF;
        }

        return $hash;
    }

    /**
     * Shard lock file index.
     */
    public function getShardLockIndex(int $currentTime): int
    {
        return $this->fnv((string) $currentTime) % self::$shardCount;
    }

    /**
     * Check path is exists and writable.
     *
     * @throws SnowflakeException
     */
    protected function preparePath(string $lockFileDir): string
    {
        if (! is_dir($lockFileDir)) {
            throw new SnowflakeException("{$lockFileDir} is not a directory.");
        }

        if (! is_writable($lockFileDir)) {
            throw new SnowflakeException("{$lockFileDir} is not writable.");
        }

        return $lockFileDir;
    }

    /**
     * Generate shard lock file.
     *
     * @throws SnowflakeException
     */
    protected function createShardLockFile(int $index): string
    {
        $path = $this->filePath($index);

        if (file_exists($path)) {
            return $path;
        }

        $f = fopen($path, 'a');
        if (! $f) {
            throw new SnowflakeException(sprintf('can not create lock file %s', $path));
        }

        $this->unlock($f);

        return $path;
    }

    /**
     * Format lock file path with shard index.
     */
    protected function filePath(int $index): string
    {
        return sprintf('%s%ssnowflake-%s.lock', rtrim($this->lockFileDir, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR, $index);
    }
}
