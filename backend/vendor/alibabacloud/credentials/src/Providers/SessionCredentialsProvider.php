<?php

namespace AlibabaCloud\Credentials\Providers;

use AlibabaCloud\Credentials\Credential\RefreshResult;

abstract class SessionCredentialsProvider implements CredentialsProvider
{
    /**
     * @var array
     */
    protected static $credentialsCache = [];

    /**
     * Expiration time slot for temporary security credentials.
     *
     * @var int
     */
    protected $expirationSlot = 180;

    /**
     * @var string
     */
    protected $error = 'Result contains no credentials';

    /**
     * Get the credentials from the cache in the validity period.
     *
     * @return RefreshResult|null
     */
    protected function getCredentialsInCache()
    {
        if (isset(self::$credentialsCache[$this->key()])) {
            $result = self::$credentialsCache[$this->key()];
            return $result;
        }
        return null;
    }

    /**
     * Cache credentials.
     *
     * @param RefreshResult $credential
     */
    protected function cache(RefreshResult $credential)
    {
        self::$credentialsCache[$this->key()] = $credential;
    }

    /**
     * Get credential.
     *
     * @return Credentials
     */
    public function getCredentials()
    {
        if ($this->cacheIsStale() || $this->shouldInitiateCachePrefetch()) {
            $result = $this->refreshCache();
            $this->cache($result);
        }

        $result = $this->getCredentialsInCache();

        return $result->credentials();
    }

    /**
     * @return RefreshResult
     */
    protected function refreshCache()
    {
        try {
            return $this->handleFetchedSuccess($this->refreshCredentials());
        } catch (\Exception $e) {
            return $this->handleFetchedFailure($e);
        }
    }

    /**
     * @return RefreshResult
     * @throws \Exception
     */
    protected function handleFetchedFailure(\Exception $e)
    {
        $currentCachedValue = $this->getCredentialsInCache();
        if (is_null($currentCachedValue)) {
            throw $e;
        }

        if (time() < $currentCachedValue->staleTime()) {
            return $currentCachedValue;
        }

        throw $e;
    }
    /**
     * @return RefreshResult
     */
    protected function handleFetchedSuccess(RefreshResult $value)
    {
        $now = time();
        // 过期时间大于15分钟，不用管
        if ($now < $value->staleTime()) {
            return $value;
        }
        // 不足或等于15分钟，但未过期，下次会再次刷新
        if ($now < $value->staleTime() + 15 * 60) {
            $value->staleTime = $now;
            return $value;
        }
        // 已过期，看缓存，缓存若大于15分钟，返回缓存，若小于15分钟，则稍后重试
        if (is_null($this->getCredentialsInCache())) {
            throw new \Exception("The fetched credentials have expired and no cache is available.");
        } else if ($now < $this->getCredentialsInCache()->staleTime()) {
            return $this->getCredentialsInCache();
        } else {
            // 返回成功，延长有效期 1 分钟
            $expectation = mt_rand(50, 70);
            $value->staleTime = time() + $expectation;
            return $value;
        }
    }

    /**
     * @return bool
     */
    protected function cacheIsStale()
    {
        return is_null($this->getCredentialsInCache()) || time() >= $this->getCredentialsInCache()->staleTime();
    }

    /**
     * @return bool
     */
    protected function shouldInitiateCachePrefetch()
    {
        return is_null($this->getCredentialsInCache()) || time() >= $this->getCredentialsInCache()->prefetchTime();
    }

    /**
     * @return int
     */
    public function getStaleTime($expiration)
    {
        return $expiration <= 0 ?
            time() + (60 * 60) :
            $expiration - (15 * 60);
    }

    /**
     * @return RefreshResult
     */
    abstract function refreshCredentials();

    /**
     * Get the toString of the credentials provider as the key.
     *
     * @return string
     */
    abstract function key();
}
