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

use ArrayAccess;
use JsonSerializable;

/**
 * Class User.
 */
class User implements ArrayAccess, UserInterface, JsonSerializable
{
    use HasAttributes;

    /**
     * User constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return string
     */
    public function getId()
    {
        return $this->getAttribute('id');
    }

    /**
     * Get the username for the user.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->getAttribute('username', $this->getId());
    }

    /**
     * Get the nickname / username for the user.
     *
     * @return string
     */
    public function getNickname()
    {
        return $this->getAttribute('nickname');
    }

    /**
     * Get the full name of the user.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }

    /**
     * Get the e-mail address of the user.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getAttribute('email');
    }

    /**
     * Get the avatar / image URL for the user.
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->getAttribute('avatar');
    }

    /**
     * Set the token on the user.
     *
     * @param \Overtrue\Socialite\AccessTokenInterface $token
     *
     * @return $this
     */
    public function setToken(AccessTokenInterface $token)
    {
        $this->setAttribute('token', $token);

        return $this;
    }

    /**
     * @param string $provider
     *
     * @return $this
     */
    public function setProviderName($provider)
    {
        $this->setAttribute('provider', $provider);

        return $this;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->getAttribute('provider');
    }

    /**
     * Get the authorized token.
     *
     * @return \Overtrue\Socialite\AccessToken
     */
    public function getToken()
    {
        return $this->getAttribute('token');
    }

    /**
     * Alias of getToken().
     *
     * @return \Overtrue\Socialite\AccessToken
     */
    public function getAccessToken()
    {
        return $this->getToken();
    }

    /**
     * Get the original attributes.
     *
     * @return array
     */
    public function getOriginal()
    {
        return $this->getAttribute('original');
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return array_merge($this->attributes, ['token' => $this->token->getAttributes()]);
    }
}
