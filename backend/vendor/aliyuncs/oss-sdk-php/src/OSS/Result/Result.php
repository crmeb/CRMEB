<?php

namespace OSS\Result;

use OSS\Core\OssException;
use OSS\Http\ResponseCore;


/**
 * Class Result, The result class of The operation of the base class, different requests in dealing with the return of data have different logic,
 * The specific parsing logic postponed to subclass implementation
 *
 * @package OSS\Model
 */
abstract class Result
{
    /**
     * Result constructor.
     * @param $response ResponseCore
     * @throws OssException
     */
    public function __construct($response)
    {
        if ($response === null) {
            throw new OssException("raw response is null");
        }
        $this->rawResponse = $response;
        $this->parseResponse();
    }

    /**
     * Get requestId
     *
     * @return string
     */
    public function getRequestId()
    {
        if (isset($this->rawResponse) &&
            isset($this->rawResponse->header) &&
            isset($this->rawResponse->header['x-oss-request-id'])
        ) {
            return $this->rawResponse->header['x-oss-request-id'];
        } else {
            return '';
        }
    }

    /**
     * Get the returned data, different request returns the data format is different
     *
     * $return mixed
     */
    public function getData()
    {
        return $this->parsedData;
    }

    /**
     * Subclass implementation, different requests return data has different analytical logic, implemented by subclasses
     *
     * @return mixed
     */
    abstract protected function parseDataFromResponse();

    /**
     * Whether the operation is successful
     *
     * @return mixed
     */
    public function isOK()
    {
        return $this->isOk;
    }

    /**
     * @throws OssException
     */
    public function parseResponse()
    {
        $this->isOk = $this->isResponseOk();
        if ($this->isOk) {
            $this->parsedData = $this->parseDataFromResponse();
        } else {
            $httpStatus = strval($this->rawResponse->status);
            $requestId = strval($this->getRequestId());
            $code = $this->retrieveErrorCode($this->rawResponse->body);
            $message = $this->retrieveErrorMessage($this->rawResponse->body);
            $body = $this->rawResponse->body;

            $details = array(
                'status' => $httpStatus,
                'request-id' => $requestId,
                'code' => $code,
                'message' => $message,
                'body' => $body
            );
            throw new OssException($details);
        }
    }

    /**
     * Try to get the error message from body
     *
     * @param $body
     * @return string
     */
    private function retrieveErrorMessage($body)
    {
        if (empty($body) || false === strpos($body, '<?xml')) {
            return '';
        }
        $flag = false;
        try {
            $xml = simplexml_load_string($body);
            if (isset($xml->Message)) {
                return strval($xml->Message);
            }
            $flag = true;
        } catch (\Exception $e) {
            $flag = true;
        }
        if ($flag === true) {
            $start = strpos($body, '<Message>');
            if ($start === false) {
                return '';
            }
            $start += 9;
            $end = strpos($body, '</Message>', $start);
            if ($end === false) {
                return '';
            }
            return substr($body, $start, $end - $start);
        }

        return '';
    }

    /**
     * Try to get the error Code from body
     *
     * @param $body
     * @return string
     */
    private function retrieveErrorCode($body)
    {
        if (empty($body) || false === strpos($body, '<?xml')) {
            return '';
        }
        $flag = false;
        try {
            $xml = simplexml_load_string($body);
            if (isset($xml->Code)) {
                return strval($xml->Code);
            }
            $flag = true;
        } catch (\Exception $e) {
            $flag = true;
        }
        if ($flag === true) {
            $start = strpos($body, '<Code>');
            if ($start === false) {
                return '';
            }
            $start += 6;
            $end = strpos($body, '</Code>', $start);
            if ($end === false) {
                return '';
            }
            return substr($body, $start, $end - $start);
        }

        return '';
    }

    /**
     * Judging from the return http status code, [200-299] that is OK
     *
     * @return bool
     */
    protected function isResponseOk()
    {
        $status = $this->rawResponse->status;
        if ((int)(intval($status) / 100) == 2) {
            return true;
        }
        return false;
    }

    /**
     * Return the original return data
     *
     * @return ResponseCore
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     * Indicate whether the request is successful
     */
    protected $isOk = false;
    /**
     * Data parsed by subclasses
     */
    protected $parsedData = null;
    /**
     * Store the original Response returned by the auth function
     *
     * @var ResponseCore
     */
    protected $rawResponse;
}