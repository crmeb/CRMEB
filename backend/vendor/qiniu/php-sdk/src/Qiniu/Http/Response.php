<?php

namespace Qiniu\Http;

/**
 * HTTP response Object
 */
final class Response
{
    public $statusCode;
    /**
     * deprecated because of field names case-sensitive.
     * use $normalizedHeaders instead which field names are case-insensitive.
     * but be careful not to use $normalizedHeaders with `array_*` functions,
     * such as `array_key_exists`, `array_keys`, `array_values`.
     *
     * use `isset` instead of `array_key_exists`,
     * and should never use `array_key_exists` at http header.
     *
     * use `foreach` instead of `array_keys`, `array_values`.
     *
     * @deprecated
     */
    public $headers;
    public $normalizedHeaders;
    public $body;
    public $error;
    private $jsonData;
    public $duration;

    /** @var array Mapping of status codes to reason phrases */
    private static $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Reserved for WebDAV advanced collections expired proposal',
        426 => 'Upgrade required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    );

    /**
     * @param int $code 状态码
     * @param double $duration 请求时长
     * @param array $headers 响应头部
     * @param string $body 响应内容
     * @param string $error 错误描述
     */
    public function __construct($code, $duration, array $headers = array(), $body = null, $error = null)
    {
        $this->statusCode = $code;
        $this->duration = $duration;
        $this->headers = array();
        $this->body = $body;
        $this->error = $error;
        $this->jsonData = null;

        if ($error !== null) {
            return;
        }

        foreach ($headers as $k => $vs) {
            if (is_array($vs)) {
                $this->headers[$k] = $vs[count($vs) - 1];
            } else {
                $this->headers[$k] = $vs;
            }
        }
        $this->normalizedHeaders = new Header($headers);

        if ($body === null) {
            if ($code >= 400) {
                $this->error = self::$statusTexts[$code];
            }
            return;
        }
        if (self::isJson($this->normalizedHeaders)) {
            try {
                $jsonData = self::bodyJson($body);
                if ($code >= 400) {
                    $this->error = $body;
                    if ($jsonData['error'] !== null) {
                        $this->error = $jsonData['error'];
                    }
                }
                $this->jsonData = $jsonData;
            } catch (\InvalidArgumentException $e) {
                $this->error = $body;
                if ($code >= 200 && $code < 300) {
                    $this->error = $e->getMessage();
                }
            }
        } elseif ($code >= 400) {
            $this->error = $body;
        }
        return;
    }

    public function json()
    {
        return $this->jsonData;
    }

    public function headers($normalized = false)
    {
        if ($normalized) {
            return $this->normalizedHeaders;
        }
        return $this->headers;
    }

    public function body()
    {
        return $this->body;
    }

    private static function bodyJson($body)
    {
        return \Qiniu\json_decode((string) $body, true, 512);
    }

    public function xVia()
    {
        $via = $this->normalizedHeaders['X-Via'];
        if ($via === null) {
            $via = $this->normalizedHeaders['X-Px'];
        }
        if ($via === null) {
            $via = $this->normalizedHeaders['Fw-Via'];
        }
        return $via;
    }

    public function xLog()
    {
        return $this->normalizedHeaders['X-Log'];
    }

    public function xReqId()
    {
        return $this->normalizedHeaders['X-Reqid'];
    }

    public function ok()
    {
        return $this->statusCode >= 200 && $this->statusCode < 300 && $this->error === null;
    }

    public function needRetry()
    {
        if ($this->statusCode > 0 && $this->statusCode < 500) {
            return false;
        }

        // https://developer.qiniu.com/fusion/kb/1352/the-http-request-return-a-status-code
        if (in_array($this->statusCode, array(
            501, 509, 573, 579, 608, 612, 614, 616, 618, 630, 631, 632, 640, 701
        ))) {
            return false;
        }

        return true;
    }

    private static function isJson($headers)
    {
        return isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'application/json') === 0;
    }
}
