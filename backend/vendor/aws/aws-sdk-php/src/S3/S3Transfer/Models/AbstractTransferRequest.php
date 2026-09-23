<?php

namespace Aws\S3\S3Transfer\Models;

use Aws\S3\S3Transfer\Progress\AbstractTransferListener;
use InvalidArgumentException;

abstract class AbstractTransferRequest
{
    public static array $configKeys = [
        'track_progress' => 'bool',
    ];

    /** @var array  */
    protected array $listeners;

    /** @var AbstractTransferListener|null  */
    protected ?AbstractTransferListener $progressTracker;

    /** @var array */
    protected array $singleObjectListeners;

    /** @var array */
    protected array $config;

    /**
     * @param array $listeners
     * @param AbstractTransferListener|null $progressTracker
     * @param array $config
     */
    public function __construct(
        array $listeners,
        ?AbstractTransferListener $progressTracker,
        array $config,
        array $singleObjectListeners = []
    ) {
        $this->listeners = $listeners;
        $this->progressTracker = $progressTracker;
        $this->singleObjectListeners = $singleObjectListeners;
        $this->config = $config;
    }

    /**
     * Get current listeners.
     *
     * @return array
     */
    public function getListeners(): array
    {
        return $this->listeners;
    }

    /**
     * Get the progress tracker.
     *
     * @return AbstractTransferListener|null
     */
    public function getProgressTracker(): ?AbstractTransferListener
    {
        return $this->progressTracker;
    }

    /**
     * Get listeners that should receive single-object events.
     *
     * @return array
     */
    public function getSingleObjectListeners(): array
    {
        return $this->singleObjectListeners;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $defaultConfig
     *
     * @return void
     */
    public function updateConfigWithDefaults(array $defaultConfig): void
    {
        foreach (static::$configKeys as $key => $_) {
            if (isset($defaultConfig[$key]) && empty($this->config[$key])) {
                $this->config[$key] = $defaultConfig[$key];
            }
        }
    }

    /**
     * For validating config. By default, it provides an empty
     * implementation.
     * @return void
     */
    public function validateConfig(): void {
        foreach (static::$configKeys as $key => $type) {
            if (isset($this->config[$key])
                && !call_user_func('is_' . $type, $this->config[$key])) {
                throw new InvalidArgumentException(
                    "The provided config `$key` must be $type."
                );
            }
        }
    }
}
