<?php
namespace Aws\Token;

use Aws\Identity\BearerTokenIdentity;

/**
 * Basic implementation of the AWS Token interface that allows callers to
 * pass in an AWS token in the constructor.
 */
class Token extends BearerTokenIdentity implements TokenInterface, \Serializable
{
    protected $token;
    protected $expires;
    protected ?TokenSource $source;

    /**
     * Constructs a new basic token object, with the specified AWS
     * token
     *
     * @param string $token   Security token to use
     * @param int    $expires UNIX timestamp for when the token expires
     */
    public function __construct(
        $token,
        $expires = null,
        ?TokenSource $source = null
    ){
        $this->token = $token;
        $this->expires = $expires;
        $this->source = $source;
    }

    /**
     * Sets the state of a token object
     *
     * @param array $state   array containing 'token' and 'expires'
     */
    public static function __set_state(array $state)
    {
        return new self(
            $state['token'],
            $state['expires']
        );
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return int
     */
    public function getExpiration()
    {
        return $this->expires;
    }

    /**
     * @return string|null
     */
    public function getSource(): ?string
    {
        return $this->source?->value;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires !== null && time() >= $this->expires;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'token'   => $this->token,
            'expires' => $this->expires,
            'source'  => $this->source?->value
        ];
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return json_encode($this->__serialize());
    }

    /**
     * Sets the state of the object from serialized json data
     */
    public function unserialize($serialized)
    {
        $data = json_decode($serialized, true);

        $this->__unserialize($data);
    }

    /**
     * @return array
     */
    public function __serialize()
    {
        return $this->toArray();
    }

    /**
     *  Sets the state of this object from an array
     */
    public function __unserialize($data)
    {
        $this->token = $data['token'];
        $this->expires = $data['expires'];
        $this->source = isset($data['source'])
            ? TokenSource::from($data['source'])
            : null;
    }
}
