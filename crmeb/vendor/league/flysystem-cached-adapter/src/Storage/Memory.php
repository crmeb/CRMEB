<?php

namespace League\Flysystem\Cached\Storage;

class Memory extends AbstractCache
{
    /**
     * {@inheritdoc}
     */
    public function save()
    {
        // There is nothing to save
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        // There is nothing to load
    }
}
