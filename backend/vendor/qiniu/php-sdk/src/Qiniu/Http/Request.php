<?php
namespace Qiniu\Http;

final class Request
{
    /**
     * @var string
     */
    public $url;

    /**
     * @var array<string, string>
     */
    public $headers;

    /**
     * @var mixed|null
     */
    public $body;

    /**
     * @var string
     */
    public $method;

    /**
     * @var RequestOptions
     */
    public $opt;

    public function __construct($method, $url, array $headers = array(), $body = null, $opt = null)
    {
        $this->method = strtoupper($method);
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
        if ($opt === null) {
            $opt = new RequestOptions();
        }
        $this->opt = $opt;
    }
}
