<?php

/*
 * This file is part of the overtrue/socialite.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\Socialite;

/**
 * Trait HasAttributes.
 */
trait HasAttributes
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * Return the attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Return the extra attribute.
     *
     * @param string $name
     * @param string $default
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    /**
     * Set extra attributes.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Map the given array onto the user's properties.
     *
     * @param array $attributes
     *
     * @return $this
     */
    public function merge(array $attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($property)
    {
        return $this->getAttribute($property);
    }

    /**
     * Return array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getAttributes();
    }

    /**
     * Return JSON.
     *
     * @return string
     */
    public function toJSON()
    {
        return json_encode($this->getAttributes(), JSON_UNESCAPED_UNICODE);
    }
}
