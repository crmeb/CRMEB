<?php

/**
 * @file
 * Dispatch events when patches are applied.
 */

namespace cweagans\Composer\Event;

use Composer\Composer;
use Composer\EventDispatcher\Event;
use Composer\IO\IOInterface;
use cweagans\Composer\Patch;
use Exception;

class PatchEvent extends Event
{
    /**
     * @var Patch $patch
     */
    protected Patch $patch;

    /**
     * @var Composer $composer
     */
    protected Composer $composer;

    /**
     * @var IOInterface $io
     */
    protected IOInterface $io;

    /**
     * @var ?Exception $error
     */
    protected ?Exception $error;

    /**
     * Constructs a PatchEvent object.
     *
     * @param string $eventName
     * @param Patch $patch
     */
    public function __construct(
        string $eventName,
        Patch $patch,
        Composer $composer,
        IOInterface $io,
        ?Exception $error = null
    ) {
        parent::__construct($eventName);
        $this->patch = $patch;
        $this->composer = $composer;
        $this->io = $io;
        $this->error = $error;
    }

    /**
     * Returns the Patch object.
     *
     * @return Patch
     */
    public function getPatch(): Patch
    {
        return $this->patch;
    }

    /**
     * Returns the Composer object.
     *
     * @return Composer
     */
    public function getComposer(): Composer
    {
        return $this->composer;
    }

    /**
     * Returns the IOInterface.
     *
     * @return IOInterface
     */
    public function getIO(): IOInterface
    {
        return $this->io;
    }

    /**
     * Returns the exception about to be thrown when a patch cannot be applied.
     *
     * @return Exception
     */
    public function getError(): ?Exception
    {
        return $this->error;
    }
}
