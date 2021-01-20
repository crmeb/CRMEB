<?php

namespace AlibabaCloud\Tea;

use Adbar\Dot;
use ArrayAccess;
use Countable;
use GuzzleHttp\Psr7\Response as PsrResponse;
use GuzzleHttp\TransferStats;
use IteratorAggregate;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class Response.
 */
class Response extends PsrResponse implements ArrayAccess, IteratorAggregate, Countable
{
    public $headers = [];
    public $statusCode;
    public $statusMessage = '';

    /**
     * @var TransferStats
     */
    public static $info;

    /**
     * @var StreamInterface
     */
    public $body;
    /**
     * Instance of the Dot.
     *
     * @var Dot
     */
    protected $dot;

    /**
     * Response constructor.
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct(
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getBody(),
            $response->getProtocolVersion(),
            $response->getReasonPhrase()
        );
        $this->headers    = $response->getHeaders();
        $this->body       = $response->getBody();
        $this->statusCode = $response->getStatusCode();
        if ($this->body->isSeekable()) {
            $this->body->seek(0);
        }

        if (Helper::isJson((string) $this->getBody())) {
            $this->dot = new Dot($this->toArray());
        } else {
            $this->dot = new Dot();
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getBody();
    }

    /**
     * @param string $name
     *
     * @return null|mixed
     */
    public function __get($name)
    {
        $data = $this->dot->all();
        if (!isset($data[$name])) {
            return null;
        }

        return json_decode(json_encode($data))->{$name};
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $this->dot->set($name, $value);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return $this->dot->has($name);
    }

    /**
     * @param $offset
     */
    public function __unset($offset)
    {
        $this->dot->delete($offset);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return \GuzzleHttp\json_decode((string) $this->getBody(), true);
    }

    /**
     * @param array|int|string $keys
     * @param mixed            $value
     */
    public function add($keys, $value = null)
    {
        return $this->dot->add($keys, $value);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->dot->all();
    }

    /**
     * @param null|array|int|string $keys
     */
    public function clear($keys = null)
    {
        return $this->dot->clear($keys);
    }

    /**
     * @param array|int|string $keys
     */
    public function delete($keys)
    {
        return $this->dot->delete($keys);
    }

    /**
     * @param string     $delimiter
     * @param null|array $items
     * @param string     $prepend
     *
     * @return array
     */
    public function flatten($delimiter = '.', $items = null, $prepend = '')
    {
        return $this->dot->flatten($delimiter, $items, $prepend);
    }

    /**
     * @param null|int|string $key
     * @param mixed           $default
     *
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return $this->dot->get($key, $default);
    }

    /**
     * @param array|int|string $keys
     *
     * @return bool
     */
    public function has($keys)
    {
        return $this->dot->has($keys);
    }

    /**
     * @param null|array|int|string $keys
     *
     * @return bool
     */
    public function isEmpty($keys = null)
    {
        return $this->dot->isEmpty($keys);
    }

    /**
     * @param array|self|string $key
     * @param array|self        $value
     */
    public function merge($key, $value = [])
    {
        return $this->dot->merge($key, $value);
    }

    /**
     * @param array|self|string $key
     * @param array|self        $value
     */
    public function mergeRecursive($key, $value = [])
    {
        return $this->dot->mergeRecursive($key, $value);
    }

    /**
     * @param array|self|string $key
     * @param array|self        $value
     */
    public function mergeRecursiveDistinct($key, $value = [])
    {
        return $this->dot->mergeRecursiveDistinct($key, $value);
    }

    /**
     * @param null|int|string $key
     * @param mixed           $default
     *
     * @return mixed
     */
    public function pull($key = null, $default = null)
    {
        return $this->dot->pull($key, $default);
    }

    /**
     * @param null|int|string $key
     * @param mixed           $value
     *
     * @return mixed
     */
    public function push($key = null, $value = null)
    {
        return $this->dot->push($key, $value);
    }

    /**
     * Replace all values or values within the given key
     * with an array or Dot object.
     *
     * @param array|self|string $key
     * @param array|self        $value
     */
    public function replace($key, $value = [])
    {
        return $this->dot->replace($key, $value);
    }

    /**
     * Set a given key / value pair or pairs.
     *
     * @param array|int|string $keys
     * @param mixed            $value
     */
    public function set($keys, $value = null)
    {
        return $this->dot->set($keys, $value);
    }

    /**
     * Replace all items with a given array.
     *
     * @param mixed $items
     */
    public function setArray($items)
    {
        return $this->dot->setArray($items);
    }

    /**
     * Replace all items with a given array as a reference.
     */
    public function setReference(array &$items)
    {
        return $this->dot->setReference($items);
    }

    /**
     * Return the value of a given key or all the values as JSON.
     *
     * @param mixed $key
     * @param int   $options
     *
     * @return string
     */
    public function toJson($key = null, $options = 0)
    {
        return $this->dot->toJson($key, $options);
    }

    /**
     * Retrieve an external iterator.
     */
    public function getIterator()
    {
        return $this->dot->getIterator();
    }

    /**
     * Whether a offset exists.
     *
     * @param $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->dot->offsetExists($offset);
    }

    /**
     * Offset to retrieve.
     *
     * @param $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->dot->offsetGet($offset);
    }

    /**
     * Offset to set.
     *
     * @param $offset
     * @param $value
     */
    public function offsetSet($offset, $value)
    {
        $this->dot->offsetSet($offset, $value);
    }

    /**
     * Offset to unset.
     *
     * @param $offset
     */
    public function offsetUnset($offset)
    {
        $this->dot->offsetUnset($offset);
    }

    /**
     * Count elements of an object.
     *
     * @param null $key
     *
     * @return int
     */
    public function count($key = null)
    {
        return $this->dot->count($key);
    }
}
