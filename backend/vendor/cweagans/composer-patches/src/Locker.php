<?php

namespace cweagans\Composer;

// Much of this file was taken directly from Composer's locker.

use Composer\Json\JsonFile;
use LogicException;
use Seld\JsonLint\ParsingException;

class Locker
{
    protected JsonFile $lockFile;

    protected bool $virtualFileWritten = false;

    protected ?array $lockDataCache = null;

    protected string $hash;

    public function __construct(JsonFile $lockFile)
    {
        $this->lockFile = $lockFile;
    }

    /**
     * Returns the sha256 hash of the sorted content of the patches.lock.json file.
     *
     * @param PatchCollection $patchCollection
     *   The resolved patches for the project.
     */
    public static function getCollectionHash(PatchCollection $patchCollection): string
    {
        $file = $patchCollection->jsonSerialize();
        ksort($file);
        return hash('sha256', JsonFile::encode($file, 0));
    }

    public function isLocked(): bool
    {
        if (!$this->virtualFileWritten && !$this->lockFile->exists()) {
            return false;
        }

        try {
            $data = $this->getLockData();
            return isset($data['patches']);
        } catch (LogicException $e) {
            return false;
        }
    }

    public function isFresh(PatchCollection $patchCollection): bool
    {
        try {
            $lock = $this->getLockData();
        } catch (LogicException $e) {
            return false;
        }

        if (empty($lock['patches']) || empty($lock['_hash'])) {
            return false;
        }

        $expectedHash = self::getCollectionHash($patchCollection);

        return $expectedHash === $lock['_hash'];
    }

    public function getLockData(): array
    {
        if (!is_null($this->lockDataCache)) {
            return $this->lockDataCache;
        }

        if (!$this->lockFile->exists()) {
            throw new LogicException('No lockfile found. Unable to read locked patches.');
        }

        return $this->lockDataCache = $this->lockFile->read();
    }

    public function setLockData(PatchCollection $patchCollection, bool $write = true): bool
    {
        $lock = $patchCollection->jsonSerialize();
        $lock['_hash'] = self::getCollectionHash($patchCollection);
        ksort($lock);

        try {
            $isLocked = $this->isLocked();
        } catch (ParsingException $e) {
            $isLocked = false;
        }
        if (!$isLocked || $lock !== $this->getLockData()) {
            if ($write) {
                $this->lockFile->write($lock);
                $this->lockDataCache = null;
                $this->virtualFileWritten = false;
            } else {
                $this->virtualFileWritten = true;
                $this->lockDataCache = JsonFile::parseJson(JsonFile::encode($lock));
            }

            return true;
        }

        return false;
    }
}
