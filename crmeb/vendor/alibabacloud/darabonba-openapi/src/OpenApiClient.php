<?php

// This file is auto-generated, don't edit it. Thanks.

namespace Darabonba\OpenApi;

use AlibabaCloud\Credentials\Credential;
use AlibabaCloud\Credentials\Credential\Config;
use AlibabaCloud\OpenApiUtil\OpenApiUtilClient;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Exception\TeaUnableRetryError;
use AlibabaCloud\Tea\Request;
use AlibabaCloud\Tea\Tea;
use AlibabaCloud\Tea\Utils\Utils;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use AlibabaCloud\Tea\XML\XML;
use Darabonba\GatewaySpi\Models\AttributeMap;
use Darabonba\GatewaySpi\Models\InterceptorContext;
use Darabonba\GatewaySpi\Models\InterceptorContext\configuration;
use Darabonba\GatewaySpi\Models\InterceptorContext\response;
use Darabonba\OpenApi\Models\OpenApiRequest;
use Darabonba\OpenApi\Models\Params;
use Exception;

/**
 * This is for OpenApi SDK.
 */
class OpenApiClient
{
    protected $_endpoint;

    protected $_regionId;

    protected $_protocol;

    protected $_method;

    protected $_userAgent;

    protected $_endpointRule;

    protected $_endpointMap;

    protected $_suffix;

    protected $_readTimeout;

    protected $_connectTimeout;

    protected $_httpProxy;

    protected $_httpsProxy;

    protected $_socks5Proxy;

    protected $_socks5NetWork;

    protected $_noProxy;

    protected $_network;

    protected $_productId;

    protected $_maxIdleConns;

    protected $_endpointType;

    protected $_openPlatformEndpoint;

    protected $_credential;

    protected $_signatureVersion;

    protected $_signatureAlgorithm;

    protected $_headers;

    protected $_spi;

    protected $_globalParameters;

    /**
     * Init client with Config.
     *
     * @param config config contains the necessary information to create a client
     */
    public function __construct($config)
    {
        if (Utils::isUnset($config)) {
            throw new TeaError(['code' => 'ParameterMissing', 'message' => "'config' can not be unset"]);
        }
        if (!Utils::empty_($config->accessKeyId) && !Utils::empty_($config->accessKeySecret)) {
            if (!Utils::empty_($config->securityToken)) {
                $config->type = 'sts';
            } else {
                $config->type = 'access_key';
            }
            $credentialConfig = new Config([
                'accessKeyId' => $config->accessKeyId,
                'type' => $config->type,
                'accessKeySecret' => $config->accessKeySecret,
                'securityToken' => $config->securityToken,
            ]);
            $this->_credential = new Credential($credentialConfig);
        } elseif (!Utils::isUnset($config->credential)) {
            $this->_credential = $config->credential;
        }
        $this->_endpoint = $config->endpoint;
        $this->_endpointType = $config->endpointType;
        $this->_network = $config->network;
        $this->_suffix = $config->suffix;
        $this->_protocol = $config->protocol;
        $this->_method = $config->method;
        $this->_regionId = $config->regionId;
        $this->_userAgent = $config->userAgent;
        $this->_readTimeout = $config->readTimeout;
        $this->_connectTimeout = $config->connectTimeout;
        $this->_httpProxy = $config->httpProxy;
        $this->_httpsProxy = $config->httpsProxy;
        $this->_noProxy = $config->noProxy;
        $this->_socks5Proxy = $config->socks5Proxy;
        $this->_socks5NetWork = $config->socks5NetWork;
        $this->_maxIdleConns = $config->maxIdleConns;
        $this->_signatureVersion = $config->signatureVersion;
        $this->_signatureAlgorithm = $config->signatureAlgorithm;
        $this->_globalParameters = $config->globalParameters;
    }

    /**
     * Encapsulate the request and invoke the network.
     *
     * @param string         $action   api name
     * @param string         $version  product version
     * @param string         $protocol http or https
     * @param string         $method   e.g. GET
     * @param string         $authType authorization type e.g. AK
     * @param string         $bodyType response body type e.g. String
     * @param OpenApiRequest $request  object of OpenApiRequest
     * @param RuntimeOptions $runtime  which controls some details of call api, such as retry times
     *
     * @return array the response
     *
     * @throws TeaError
     * @throws Exception
     * @throws TeaUnableRetryError
     */
    public function doRPCRequest($action, $version, $protocol, $method, $authType, $bodyType, $request, $runtime)
    {
        $request->validate();
        $runtime->validate();
        $_runtime = [
            'timeouted' => 'retry',
            'readTimeout' => Utils::defaultNumber($runtime->readTimeout, $this->_readTimeout),
            'connectTimeout' => Utils::defaultNumber($runtime->connectTimeout, $this->_connectTimeout),
            'httpProxy' => Utils::defaultString($runtime->httpProxy, $this->_httpProxy),
            'httpsProxy' => Utils::defaultString($runtime->httpsProxy, $this->_httpsProxy),
            'noProxy' => Utils::defaultString($runtime->noProxy, $this->_noProxy),
            'socks5Proxy' => Utils::defaultString($runtime->socks5Proxy, $this->_socks5Proxy),
            'socks5NetWork' => Utils::defaultString($runtime->socks5NetWork, $this->_socks5NetWork),
            'maxIdleConns' => Utils::defaultNumber($runtime->maxIdleConns, $this->_maxIdleConns),
            'retry' => [
                'retryable' => $runtime->autoretry,
                'maxAttempts' => Utils::defaultNumber($runtime->maxAttempts, 3),
            ],
            'backoff' => [
                'policy' => Utils::defaultString($runtime->backoffPolicy, 'no'),
                'period' => Utils::defaultNumber($runtime->backoffPeriod, 1),
            ],
            'ignoreSSL' => $runtime->ignoreSSL,
        ];
        $_lastRequest = null;
        $_lastException = null;
        $_now = time();
        $_retryTimes = 0;
        while (Tea::allowRetry(@$_runtime['retry'], $_retryTimes, $_now)) {
            if ($_retryTimes > 0) {
                $_backoffTime = Tea::getBackoffTime(@$_runtime['backoff'], $_retryTimes);
                if ($_backoffTime > 0) {
                    Tea::sleep($_backoffTime);
                }
            }
            $_retryTimes = $_retryTimes + 1;
            try {
                $_request = new Request();
                $_request->protocol = Utils::defaultString($this->_protocol, $protocol);
                $_request->method = $method;
                $_request->pathname = '/';
                $_request->query = Tea::merge([
                    'Action' => $action,
                    'Format' => 'json',
                    'Version' => $version,
                    'Timestamp' => OpenApiUtilClient::getTimestamp(),
                    'SignatureNonce' => Utils::getNonce(),
                ], $request->query);
                $headers = $this->getRpcHeaders();
                if (Utils::isUnset($headers)) {
                    // endpoint is setted in product client
                    $_request->headers = [
                        'host' => $this->_endpoint,
                        'x-acs-version' => $version,
                        'x-acs-action' => $action,
                        'user-agent' => $this->getUserAgent(),
                    ];
                } else {
                    $_request->headers = Tea::merge([
                        'host' => $this->_endpoint,
                        'x-acs-version' => $version,
                        'x-acs-action' => $action,
                        'user-agent' => $this->getUserAgent(),
                    ], $headers);
                }
                if (!Utils::isUnset($request->body)) {
                    $m = Utils::assertAsMap($request->body);
                    $tmp = Utils::anyifyMapValue(OpenApiUtilClient::query($m));
                    $_request->body = Utils::toFormString($tmp);
                    $_request->headers['content-type'] = 'application/x-www-form-urlencoded';
                }
                if (!Utils::equalString($authType, 'Anonymous')) {
                    $accessKeyId = $this->getAccessKeyId();
                    $accessKeySecret = $this->getAccessKeySecret();
                    $securityToken = $this->getSecurityToken();
                    if (!Utils::empty_($securityToken)) {
                        $_request->query['SecurityToken'] = $securityToken;
                    }
                    $_request->query['SignatureMethod'] = 'HMAC-SHA1';
                    $_request->query['SignatureVersion'] = '1.0';
                    $_request->query['AccessKeyId'] = $accessKeyId;
                    $t = null;
                    if (!Utils::isUnset($request->body)) {
                        $t = Utils::assertAsMap($request->body);
                    }
                    $signedParam = Tea::merge($_request->query, OpenApiUtilClient::query($t));
                    $_request->query['Signature'] = OpenApiUtilClient::getRPCSignature($signedParam, $_request->method, $accessKeySecret);
                }
                $_lastRequest = $_request;
                $_response = Tea::send($_request, $_runtime);
                if (Utils::is4xx($_response->statusCode) || Utils::is5xx($_response->statusCode)) {
                    $_res = Utils::readAsJSON($_response->body);
                    $err = Utils::assertAsMap($_res);
                    $requestId = self::defaultAny(@$err['RequestId'], @$err['requestId']);
                    throw new TeaError(['code' => ''.(string) (self::defaultAny(@$err['Code'], @$err['code'])).'', 'message' => 'code: '.(string) ($_response->statusCode).', '.(string) (self::defaultAny(@$err['Message'], @$err['message'])).' request id: '.(string) ($requestId).'', 'data' => $err]);
                }
                if (Utils::equalString($bodyType, 'binary')) {
                    $resp = [
                        'body' => $_response->body,
                        'headers' => $_response->headers,
                    ];

                    return $resp;
                } elseif (Utils::equalString($bodyType, 'byte')) {
                    $byt = Utils::readAsBytes($_response->body);

                    return [
                        'body' => $byt,
                        'headers' => $_response->headers,
                    ];
                } elseif (Utils::equalString($bodyType, 'string')) {
                    $str = Utils::readAsString($_response->body);

                    return [
                        'body' => $str,
                        'headers' => $_response->headers,
                    ];
                } elseif (Utils::equalString($bodyType, 'json')) {
                    $obj = Utils::readAsJSON($_response->body);
                    $res = Utils::assertAsMap($obj);

                    return [
                        'body' => $res,
                        'headers' => $_response->headers,
                    ];
                } elseif (Utils::equalString($bodyType, 'array')) {
                    $arr = Utils::readAsJSON($_response->body);

                    return [
                        'body' => $arr,
                        'headers' => $_response->headers,
                    ];
                } else {
                    return [
                        'headers' => $_response->headers,
                    ];
                }
            } catch (Exception $e) {
                if (!($e instanceof TeaError)) {
                    $e = new TeaError([], $e->getMessage(), $e->getCode(), $e);
                }
                if (Tea::isRetryable($e)) {
                    $_lastException = $e;
                    continue;
                }
                throw $e;
            }
        }
        throw new TeaUnableRetryError($_lastRequest, $_lastException);
    }

    /**
     * Encapsulate the request and invoke the network.
     *
     * @param string         $action   api name
     * @param string         $version  product version
     * @param string         $protocol http or https
     * @param string         $method   e.g. GET
     * @param string         $authType authorization type e.g. AK
     * @param string         $pathname pathname of every api
     * @param string         $bodyType response body type e.g. String
     * @param OpenApiRequest $request  object of OpenApiRequest
     * @param RuntimeOptions $runtime  which controls some details of call api, such as retry times
     *
     * @return array the response
     *
     * @throws TeaError
     * @throws Exception
     * @throws TeaUnableRetryError
     */
    public function doROARequest($action, $version, $protocol, $method, $authType, $pathname, $bodyType, $request, $runtime)
    {
        $request->validate();
        $runtime->validate();
        $_runtime = [
            'timeouted' => 'retry',
            'readTimeout' => Utils::defaultNumber($runtime->readTimeout, $this->_readTimeout),
            'connectTimeout' => Utils::defaultNumber($runtime->connectTimeout, $this->_connectTimeout),
            'httpProxy' => Utils::defaultString($runtime->httpProxy, $this->_httpProxy),
            'httpsProxy' => Utils::defaultString($runtime->httpsProxy, $this->_httpsProxy),
            'noProxy' => Utils::defaultString($runtime->noProxy, $this->_noProxy),
            'socks5Proxy' => Utils::defaultString($runtime->socks5Proxy, $this->_socks5Proxy),
            'socks5NetWork' => Utils::defaultString($runtime->socks5NetWork, $this->_socks5NetWork),
            'maxIdleConns' => Utils::defaultNumber($runtime->maxIdleConns, $this->_maxIdleConns),
            'retry' => [
                'retryable' => $runtime->autoretry,
                'maxAttempts' => Utils::defaultNumber($runtime->maxAttempts, 3),
            ],
            'backoff' => [
                'policy' => Utils::defaultString($runtime->backoffPolicy, 'no'),
                'period' => Utils::defaultNumber($runtime->backoffPeriod, 1),
            ],
            'ignoreSSL' => $runtime->ignoreSSL,
        ];
        $_lastRequest = null;
        $_lastException = null;
        $_now = time();
        $_retryTimes = 0;
        while (Tea::allowRetry(@$_runtime['retry'], $_retryTimes, $_now)) {
            if ($_retryTimes > 0) {
                $_backoffTime = Tea::getBackoffTime(@$_runtime['backoff'], $_retryTimes);
                if ($_backoffTime > 0) {
                    Tea::sleep($_backoffTime);
                }
            }
            $_retryTimes = $_retryTimes + 1;
            try {
                $_request = new Request();
                $_request->protocol = Utils::defaultString($this->_protocol, $protocol);
                $_request->method = $method;
                $_request->pathname = $pathname;
                $_request->headers = Tea::merge([
                    'date' => Utils::getDateUTCString(),
                    'host' => $this->_endpoint,
                    'accept' => 'application/json',
                    'x-acs-signature-nonce' => Utils::getNonce(),
                    'x-acs-signature-method' => 'HMAC-SHA1',
                    'x-acs-signature-version' => '1.0',
                    'x-acs-version' => $version,
                    'x-acs-action' => $action,
                    'user-agent' => Utils::getUserAgent($this->_userAgent),
                ], $request->headers);
                if (!Utils::isUnset($request->body)) {
                    $_request->body = Utils::toJSONString($request->body);
                    $_request->headers['content-type'] = 'application/json; charset=utf-8';
                }
                if (!Utils::isUnset($request->query)) {
                    $_request->query = $request->query;
                }
                if (!Utils::equalString($authType, 'Anonymous')) {
                    $accessKeyId = $this->getAccessKeyId();
                    $accessKeySecret = $this->getAccessKeySecret();
                    $securityToken = $this->getSecurityToken();
                    if (!Utils::empty_($securityToken)) {
                        $_request->headers['x-acs-accesskey-id'] = $accessKeyId;
                        $_request->headers['x-acs-security-token'] = $securityToken;
                    }
                    $stringToSign = OpenApiUtilClient::getStringToSign($_request);
                    $_request->headers['authorization'] = 'acs '.$accessKeyId.':'.OpenApiUtilClient::getROASignature($stringToSign, $accessKeySecret).'';
                }
                $_lastRequest = $_request;
                $_response = Tea::send($_request, $_runtime);
                if (Utils::equalNumber($_response->statusCode, 204)) {
                    return [
                        'headers' => $_response->headers,
                    ];
                }
                if (Utils::is4xx($_response->statusCode) || Utils::is5xx($_response->statusCode)) {
                    $_res = Utils::readAsJSON($_response->body);
                    $err = Utils::assertAsMap($_res);
                    $requestId = self::defaultAny(@$err['RequestId'], @$err['requestId']);
                    $requestId = self::defaultAny($requestId, @$err['requestid']);
                    throw new TeaError(['code' => ''.(string) (self::defaultAny(@$err['Code'], @$err['code'])).'', 'message' => 'code: '.(string) ($_response->statusCode).', '.(string) (self::defaultAny(@$err['Message'], @$err['message'])).' request id: '.(string) ($requestId).'', 'data' => $err]);
                }
                if (Utils::equalString($bodyType, 'binary')) {
                    $resp = [
                        'body' => $_response->body,
                        'headers' => $_response->headers,
                    ];

                    return $resp;
                } elseif (Utils::equalString($bodyType, 'byte')) {
                    $byt = Utils::readAsBytes($_response->body);

                    return [
                        'body' => $byt,
                        'headers' => $_response->headers,
                    ];
                } elseif (Utils::equalString($bodyType, 'string')) {
                    $str = Utils::readAsString($_response->body);

                    return [
                        'body' => $str,
                        'headers' => $_response->headers,
                    ];
                } elseif (Utils::equalString($bodyType, 'json')) {
                    $obj = Utils::readAsJSON($_response->body);
                    $res = Utils::assertAsMap($obj);

                    return [
                        'body' => $res,
                        'headers' => $_response->headers,
                    ];
                } elseif (Utils::equalString($bodyType, 'array')) {
                    $arr = Utils::readAsJSON($_response->body);

                    return [
                        'body' => $arr,
                        'headers' => $_response->headers,
                    ];
                } else {
                    return [
                        'headers' => $_response->headers,
                    ];
                }
            } catch (Exception $e) {
                if (!($e instanceof TeaError)) {
                    $e = new TeaError([], $e->getMessage(), $e->getCode(), $e);
                }
                if (Tea::isRetryable($e)) {
                    $_lastException = $e;
                    continue;
                }
                throw $e;
            }
        }
        throw new TeaUnableRetryError($_lastRequest, $_lastException);
    }

    /**
     * Encapsulate the request and invoke the network with form body.
     *
     * @param string         $action   api name
     * @param string         $version  product version
     * @param string         $protocol http or https
     * @param string         $method   e.g. GET
     * @param string         $authType authorization type e.g. AK
     * @param string         $pathname pathname of every api
     * @param string         $bodyType response body type e.g. String
     * @param OpenApiRequest $request  object of OpenApiRequest
     * @param RuntimeOptions $runtime  which controls some details of call api, such as retry times
     *
     * @return array the response
     *
     * @throws TeaError
     * @throws Exception
     * @throws TeaUnableRetryError
     */
    public function doROARequestWithForm($action, $version, $protocol, $method, $authType, $pathname, $bodyType, $request, $runtime)
    {
        $request->validate();
        $runtime->validate();
        $_runtime = [
            'timeouted' => 'retry',
            'readTimeout' => Utils::defaultNumber($runtime->readTimeout, $this->_readTimeout),
            'connectTimeout' => Utils::defaultNumber($runtime->connectTimeout, $this->_connectTimeout),
            'httpProxy' => Utils::defaultString($runtime->httpProxy, $this->_httpProxy),
            'httpsProxy' => Utils::defaultString($runtime->httpsProxy, $this->_httpsProxy),
            'noProxy' => Utils::defaultString($runtime->noProxy, $this->_noProxy),
            'socks5Proxy' => Utils::defaultString($runtime->socks5Proxy, $this->_socks5Proxy),
            'socks5NetWork' => Utils::defaultString($runtime->socks5NetWork, $this->_socks5NetWork),
            'maxIdleConns' => Utils::defaultNumber($runtime->maxIdleConns, $this->_maxIdleConns),
            'retry' => [
                'retryable' => $runtime->autoretry,
                'maxAttempts' => Utils::defaultNumber($runtime->maxAttempts, 3),
            ],
            'backoff' => [
                'policy' => Utils::defaultString($runtime->backoffPolicy, 'no'),
                'period' => Utils::defaultNumber($runtime->backoffPeriod, 1),
            ],
            'ignoreSSL' => $runtime->ignoreSSL,
        ];
        $_lastRequest = null;
        $_lastException = null;
        $_now = time();
        $_retryTimes = 0;
        while (Tea::allowRetry(@$_runtime['retry'], $_retryTimes, $_now)) {
            if ($_retryTimes > 0) {
                $_backoffTime = Tea::getBackoffTime(@$_runtime['backoff'], $_retryTimes);
                if ($_backoffTime > 0) {
                    Tea::sleep($_backoffTime);
                }
            }
            $_retryTimes = $_retryTimes + 1;
            try {
                $_request = new Request();
                $_request->protocol = Utils::defaultString($this->_protocol, $protocol);
                $_request->method = $method;
                $_request->pathname = $pathname;
                $_request->headers = Tea::merge([
                    'date' => Utils::getDateUTCString(),
                    'host' => $this->_endpoint,
                    'accept' => 'application/json',
                    'x-acs-signature-nonce' => Utils::getNonce(),
                    'x-acs-signature-method' => 'HMAC-SHA1',
                    'x-acs-signature-version' => '1.0',
                    'x-acs-version' => $version,
                    'x-acs-action' => $action,
                    'user-agent' => Utils::getUserAgent($this->_userAgent),
                ], $request->headers);
                if (!Utils::isUnset($request->body)) {
                    $m = Utils::assertAsMap($request->body);
                    $_request->body = OpenApiUtilClient::toForm($m);
                    $_request->headers['content-type'] = 'application/x-www-form-urlencoded';
                }
                if (!Utils::isUnset($request->query)) {
                    $_request->query = $request->query;
                }
                if (!Utils::equalString($authType, 'Anonymous')) {
                    $accessKeyId = $this->getAccessKeyId();
                    $accessKeySecret = $this->getAccessKeySecret();
                    $securityToken = $this->getSecurityToken();
                    if (!Utils::empty_($securityToken)) {
                        $_request->headers['x-acs-accesskey-id'] = $accessKeyId;
                        $_request->headers['x-acs-security-token'] = $securityToken;
                    }
                    $stringToSign = OpenApiUtilClient::getStringToSign($_request);
                    $_request->headers['authorization'] = 'acs '.$accessKeyId.':'.OpenApiUtilClient::getROASignature($stringToSign, $accessKeySecret).'';
                }
                $_lastRequest = $_request;
                $_response = Tea::send($_request, $_runtime);
                if (Utils::equalNumber($_response->statusCode, 204)) {
                    return [
                        'headers' => $_response->headers,
                    ];
                }
                if (Utils::is4xx($_response->statusCode) || Utils::is5xx($_response->statusCode)) {
                    $_res = Utils::readAsJSON($_response->body);
                    $err = Utils::assertAsMap($_res);
                    throw new TeaError(['code' => ''.(string) (self::defaultAny(@$err['Code'], @$err['code'])).'', 'message' => 'code: '.(string) ($_response->statusCode).', '.(string) (self::defaultAny(@$err['Message'], @$err['message'])).' request id: '.(string) (self::defaultAny(@$err['RequestId'], @$err['requestId'])).'', 'data' => $err]);
                }
                if (Utils::equalString($bodyType, 'binary')) {
                    $resp = [
                        'body' => $_response->body,
                        'headers' => $_response->headers,
                    ];

                    return $resp;
                } elseif (Utils::equalString($bodyType, 'byte')) {
                    $byt = Utils::readAsBytes($_response->body);

                    return [
                        'body' => $byt,
                        'headers' => $_response->headers,
                    ];
                } elseif (Utils::equalString($bodyType, 'string')) {
                    $str = Utils::readAsString($_response->body);

                    return [
                        'body' => $str,
                        'headers' => $_response->headers,
                    ];
                } elseif (Utils::equalString($bodyType, 'json')) {
                    $obj = Utils::readAsJSON($_response->body);
                    $res = Utils::assertAsMap($obj);

                    return [
                        'body' => $res,
                        'headers' => $_response->headers,
                    ];
                } elseif (Utils::equalString($bodyType, 'array')) {
                    $arr = Utils::readAsJSON($_response->body);

                    return [
                        'body' => $arr,
                        'headers' => $_response->headers,
                    ];
                } else {
                    return [
                        'headers' => $_response->headers,
                    ];
                }
            } catch (Exception $e) {
                if (!($e instanceof TeaError)) {
                    $e = new TeaError([], $e->getMessage(), $e->getCode(), $e);
                }
                if (Tea::isRetryable($e)) {
                    $_lastException = $e;
                    continue;
                }
                throw $e;
            }
        }
        throw new TeaUnableRetryError($_lastRequest, $_lastException);
    }

    /**
     * Encapsulate the request and invoke the network.
     *
     * @param Params         $params
     * @param OpenApiRequest $request object of OpenApiRequest
     * @param RuntimeOptions $runtime which controls some details of call api, such as retry times
     *
     * @return array the response
     *
     * @throws TeaError
     * @throws Exception
     * @throws TeaUnableRetryError
     */
    public function doRequest($params, $request, $runtime)
    {
        $params->validate();
        $request->validate();
        $runtime->validate();
        $_runtime = [
            'timeouted' => 'retry',
            'readTimeout' => Utils::defaultNumber($runtime->readTimeout, $this->_readTimeout),
            'connectTimeout' => Utils::defaultNumber($runtime->connectTimeout, $this->_connectTimeout),
            'httpProxy' => Utils::defaultString($runtime->httpProxy, $this->_httpProxy),
            'httpsProxy' => Utils::defaultString($runtime->httpsProxy, $this->_httpsProxy),
            'noProxy' => Utils::defaultString($runtime->noProxy, $this->_noProxy),
            'socks5Proxy' => Utils::defaultString($runtime->socks5Proxy, $this->_socks5Proxy),
            'socks5NetWork' => Utils::defaultString($runtime->socks5NetWork, $this->_socks5NetWork),
            'maxIdleConns' => Utils::defaultNumber($runtime->maxIdleConns, $this->_maxIdleConns),
            'retry' => [
                'retryable' => $runtime->autoretry,
                'maxAttempts' => Utils::defaultNumber($runtime->maxAttempts, 3),
            ],
            'backoff' => [
                'policy' => Utils::defaultString($runtime->backoffPolicy, 'no'),
                'period' => Utils::defaultNumber($runtime->backoffPeriod, 1),
            ],
            'ignoreSSL' => $runtime->ignoreSSL,
        ];
        $_lastRequest = null;
        $_lastException = null;
        $_now = time();
        $_retryTimes = 0;
        while (Tea::allowRetry(@$_runtime['retry'], $_retryTimes, $_now)) {
            if ($_retryTimes > 0) {
                $_backoffTime = Tea::getBackoffTime(@$_runtime['backoff'], $_retryTimes);
                if ($_backoffTime > 0) {
                    Tea::sleep($_backoffTime);
                }
            }
            $_retryTimes = $_retryTimes + 1;
            try {
                $_request = new Request();
                $_request->protocol = Utils::defaultString($this->_protocol, $params->protocol);
                $_request->method = $params->method;
                $_request->pathname = $params->pathname;
                $globalQueries = [];
                $globalHeaders = [];
                if (!Utils::isUnset($this->_globalParameters)) {
                    $globalParams = $this->_globalParameters;
                    if (!Utils::isUnset($globalParams->queries)) {
                        $globalQueries = $globalParams->queries;
                    }
                    if (!Utils::isUnset($globalParams->headers)) {
                        $globalHeaders = $globalParams->headers;
                    }
                }
                $_request->query = Tea::merge($globalQueries, $request->query);
                // endpoint is setted in product client
                $_request->headers = Tea::merge([
                    'host' => $this->_endpoint,
                    'x-acs-version' => $params->version,
                    'x-acs-action' => $params->action,
                    'user-agent' => $this->getUserAgent(),
                    'x-acs-date' => OpenApiUtilClient::getTimestamp(),
                    'x-acs-signature-nonce' => Utils::getNonce(),
                    'accept' => 'application/json',
                ], $globalHeaders, $request->headers);
                if (Utils::equalString($params->style, 'RPC')) {
                    $headers = $this->getRpcHeaders();
                    if (!Utils::isUnset($headers)) {
                        $_request->headers = Tea::merge($_request->headers, $headers);
                    }
                }
                $signatureAlgorithm = Utils::defaultString($this->_signatureAlgorithm, 'ACS3-HMAC-SHA256');
                $hashedRequestPayload = OpenApiUtilClient::hexEncode(OpenApiUtilClient::hash(Utils::toBytes(''), $signatureAlgorithm));
                if (!Utils::isUnset($request->stream)) {
                    $tmp = Utils::readAsBytes($request->stream);
                    $hashedRequestPayload = OpenApiUtilClient::hexEncode(OpenApiUtilClient::hash($tmp, $signatureAlgorithm));
                    $_request->body = $tmp;
                    $_request->headers['content-type'] = 'application/octet-stream';
                } else {
                    if (!Utils::isUnset($request->body)) {
                        if (Utils::equalString($params->reqBodyType, 'json')) {
                            $jsonObj = Utils::toJSONString($request->body);
                            $hashedRequestPayload = OpenApiUtilClient::hexEncode(OpenApiUtilClient::hash(Utils::toBytes($jsonObj), $signatureAlgorithm));
                            $_request->body = $jsonObj;
                            $_request->headers['content-type'] = 'application/json; charset=utf-8';
                        } else {
                            $m = Utils::assertAsMap($request->body);
                            $formObj = OpenApiUtilClient::toForm($m);
                            $hashedRequestPayload = OpenApiUtilClient::hexEncode(OpenApiUtilClient::hash(Utils::toBytes($formObj), $signatureAlgorithm));
                            $_request->body = $formObj;
                            $_request->headers['content-type'] = 'application/x-www-form-urlencoded';
                        }
                    }
                }
                $_request->headers['x-acs-content-sha256'] = $hashedRequestPayload;
                if (!Utils::equalString($params->authType, 'Anonymous')) {
                    $authType = $this->getType();
                    if (Utils::equalString($authType, 'bearer')) {
                        $bearerToken = $this->getBearerToken();
                        $_request->headers['x-acs-bearer-token'] = $bearerToken;
                    } else {
                        $accessKeyId = $this->getAccessKeyId();
                        $accessKeySecret = $this->getAccessKeySecret();
                        $securityToken = $this->getSecurityToken();
                        if (!Utils::empty_($securityToken)) {
                            $_request->headers['x-acs-accesskey-id'] = $accessKeyId;
                            $_request->headers['x-acs-security-token'] = $securityToken;
                        }
                        $_request->headers['Authorization'] = OpenApiUtilClient::getAuthorization($_request, $signatureAlgorithm, $hashedRequestPayload, $accessKeyId, $accessKeySecret);
                    }
                }
                $_lastRequest = $_request;
                $_response = Tea::send($_request, $_runtime);
                if (Utils::is4xx($_response->statusCode) || Utils::is5xx($_response->statusCode)) {
                    $err = [];
                    // if (!Utils::isUnset(@$_response->headers['content-type']) && Utils::equalString(@$_response->headers['content-type'], 'text/xml;charset=utf-8')) {
                    //     $_str = Utils::readAsString($_response->body);
                    //     $respMap = XML::parseXml($_str, null);
                    //     $err = Utils::assertAsMap(@$respMap['Error']);
                    // } else {
                    //     $_res = Utils::readAsJSON($_response->body);
                    //     $err = Utils::assertAsMap($_res);
                    // }
                    $_res = Utils::readAsJSON($_response->body);
                    $err = Utils::assertAsMap($_res);
                    @$err['statusCode'] = $_response->statusCode;
                    throw new TeaError(['code' => ''.(string) (self::defaultAny(@$err['Code'], @$err['code'])).'', 'message' => 'code: '.(string) ($_response->statusCode).', '.(string) (self::defaultAny(@$err['Message'], @$err['message'])).' request id: '.(string) (self::defaultAny(@$err['RequestId'], @$err['requestId'])).'', 'data' => $err]);
                }
                if (Utils::equalString($params->bodyType, 'binary')) {
                    $resp = [
                        'body' => $_response->body,
                        'headers' => $_response->headers,
                        'statusCode' => $_response->statusCode,
                    ];

                    return $resp;
                } elseif (Utils::equalString($params->bodyType, 'byte')) {
                    $byt = Utils::readAsBytes($_response->body);

                    return [
                        'body' => $byt,
                        'headers' => $_response->headers,
                        'statusCode' => $_response->statusCode,
                    ];
                } elseif (Utils::equalString($params->bodyType, 'string')) {
                    $str = Utils::readAsString($_response->body);

                    return [
                        'body' => $str,
                        'headers' => $_response->headers,
                        'statusCode' => $_response->statusCode,
                    ];
                } elseif (Utils::equalString($params->bodyType, 'json')) {
                    $obj = Utils::readAsJSON($_response->body);
                    $res = Utils::assertAsMap($obj);

                    return [
                        'body' => $res,
                        'headers' => $_response->headers,
                        'statusCode' => $_response->statusCode,
                    ];
                } elseif (Utils::equalString($params->bodyType, 'array')) {
                    $arr = Utils::readAsJSON($_response->body);

                    return [
                        'body' => $arr,
                        'headers' => $_response->headers,
                        'statusCode' => $_response->statusCode,
                    ];
                } else {
                    return [
                        'headers' => $_response->headers,
                        'statusCode' => $_response->statusCode,
                    ];
                }
            } catch (Exception $e) {
                if (!($e instanceof TeaError)) {
                    $e = new TeaError([], $e->getMessage(), $e->getCode(), $e);
                }
                if (Tea::isRetryable($e)) {
                    $_lastException = $e;
                    continue;
                }
                throw $e;
            }
        }
        throw new TeaUnableRetryError($_lastRequest, $_lastException);
    }

    /**
     * Encapsulate the request and invoke the network.
     *
     * @param Params         $params
     * @param OpenApiRequest $request object of OpenApiRequest
     * @param RuntimeOptions $runtime which controls some details of call api, such as retry times
     *
     * @return array the response
     *
     * @throws TeaError
     * @throws Exception
     * @throws TeaUnableRetryError
     */
    public function execute($params, $request, $runtime)
    {
        $params->validate();
        $request->validate();
        $runtime->validate();
        $_runtime = [
            'timeouted' => 'retry',
            'readTimeout' => Utils::defaultNumber($runtime->readTimeout, $this->_readTimeout),
            'connectTimeout' => Utils::defaultNumber($runtime->connectTimeout, $this->_connectTimeout),
            'httpProxy' => Utils::defaultString($runtime->httpProxy, $this->_httpProxy),
            'httpsProxy' => Utils::defaultString($runtime->httpsProxy, $this->_httpsProxy),
            'noProxy' => Utils::defaultString($runtime->noProxy, $this->_noProxy),
            'socks5Proxy' => Utils::defaultString($runtime->socks5Proxy, $this->_socks5Proxy),
            'socks5NetWork' => Utils::defaultString($runtime->socks5NetWork, $this->_socks5NetWork),
            'maxIdleConns' => Utils::defaultNumber($runtime->maxIdleConns, $this->_maxIdleConns),
            'retry' => [
                'retryable' => $runtime->autoretry,
                'maxAttempts' => Utils::defaultNumber($runtime->maxAttempts, 3),
            ],
            'backoff' => [
                'policy' => Utils::defaultString($runtime->backoffPolicy, 'no'),
                'period' => Utils::defaultNumber($runtime->backoffPeriod, 1),
            ],
            'ignoreSSL' => $runtime->ignoreSSL,
        ];
        $_lastRequest = null;
        $_lastException = null;
        $_now = time();
        $_retryTimes = 0;
        while (Tea::allowRetry(@$_runtime['retry'], $_retryTimes, $_now)) {
            if ($_retryTimes > 0) {
                $_backoffTime = Tea::getBackoffTime(@$_runtime['backoff'], $_retryTimes);
                if ($_backoffTime > 0) {
                    Tea::sleep($_backoffTime);
                }
            }
            $_retryTimes = $_retryTimes + 1;
            try {
                $_request = new Request();
                // spi = new Gateway();//Gateway implements SPI，这一步在产品 SDK 中实例化
                $headers = $this->getRpcHeaders();
                $globalQueries = [];
                $globalHeaders = [];
                if (!Utils::isUnset($this->_globalParameters)) {
                    $globalParams = $this->_globalParameters;
                    if (!Utils::isUnset($globalParams->queries)) {
                        $globalQueries = $globalParams->queries;
                    }
                    if (!Utils::isUnset($globalParams->headers)) {
                        $globalHeaders = $globalParams->headers;
                    }
                }
                $requestContext = new \Darabonba\GatewaySpi\Models\InterceptorContext\request([
                    'headers' => Tea::merge($globalHeaders, $request->headers, $headers),
                    'query' => Tea::merge($globalQueries, $request->query),
                    'body' => $request->body,
                    'stream' => $request->stream,
                    'hostMap' => $request->hostMap,
                    'pathname' => $params->pathname,
                    'productId' => $this->_productId,
                    'action' => $params->action,
                    'version' => $params->version,
                    'protocol' => Utils::defaultString($this->_protocol, $params->protocol),
                    'method' => Utils::defaultString($this->_method, $params->method),
                    'authType' => $params->authType,
                    'bodyType' => $params->bodyType,
                    'reqBodyType' => $params->reqBodyType,
                    'style' => $params->style,
                    'credential' => $this->_credential,
                    'signatureVersion' => $this->_signatureVersion,
                    'signatureAlgorithm' => $this->_signatureAlgorithm,
                    'userAgent' => $this->getUserAgent(),
                ]);
                $configurationContext = new configuration([
                    'regionId' => $this->_regionId,
                    'endpoint' => Utils::defaultString($request->endpointOverride, $this->_endpoint),
                    'endpointRule' => $this->_endpointRule,
                    'endpointMap' => $this->_endpointMap,
                    'endpointType' => $this->_endpointType,
                    'network' => $this->_network,
                    'suffix' => $this->_suffix,
                ]);
                $interceptorContext = new InterceptorContext([
                    'request' => $requestContext,
                    'configuration' => $configurationContext,
                ]);
                $attributeMap = new AttributeMap([]);
                // 1. spi.modifyConfiguration(context: SPI.InterceptorContext, attributeMap: SPI.AttributeMap);
                $this->_spi->modifyConfiguration($interceptorContext, $attributeMap);
                // 2. spi.modifyRequest(context: SPI.InterceptorContext, attributeMap: SPI.AttributeMap);
                $this->_spi->modifyRequest($interceptorContext, $attributeMap);
                $_request->protocol = $interceptorContext->request->protocol;
                $_request->method = $interceptorContext->request->method;
                $_request->pathname = $interceptorContext->request->pathname;
                $_request->query = $interceptorContext->request->query;
                $_request->body = $interceptorContext->request->stream;
                $_request->headers = $interceptorContext->request->headers;
                $_lastRequest = $_request;
                $_response = Tea::send($_request, $_runtime);
                $responseContext = new response([
                    'statusCode' => $_response->statusCode,
                    'headers' => $_response->headers,
                    'body' => $_response->body,
                ]);
                $interceptorContext->response = $responseContext;
                // 3. spi.modifyResponse(context: SPI.InterceptorContext, attributeMap: SPI.AttributeMap);
                $this->_spi->modifyResponse($interceptorContext, $attributeMap);

                return [
                    'headers' => $interceptorContext->response->headers,
                    'statusCode' => $interceptorContext->response->statusCode,
                    'body' => $interceptorContext->response->deserializedBody,
                ];
            } catch (Exception $e) {
                if (!($e instanceof TeaError)) {
                    $e = new TeaError([], $e->getMessage(), $e->getCode(), $e);
                }
                if (Tea::isRetryable($e)) {
                    $_lastException = $e;
                    continue;
                }
                throw $e;
            }
        }
        throw new TeaUnableRetryError($_lastRequest, $_lastException);
    }

    /**
     * @param Params         $params
     * @param OpenApiRequest $request
     * @param RuntimeOptions $runtime
     *
     * @return array
     *
     * @throws TeaError
     */
    public function callApi($params, $request, $runtime)
    {
        if (Utils::isUnset($params)) {
            throw new TeaError(['code' => 'ParameterMissing', 'message' => "'params' can not be unset"]);
        }
        if (Utils::isUnset($this->_signatureAlgorithm) || !Utils::equalString($this->_signatureAlgorithm, 'v2')) {
            return $this->doRequest($params, $request, $runtime);
        } elseif (Utils::equalString($params->style, 'ROA') && Utils::equalString($params->reqBodyType, 'json')) {
            return $this->doROARequest($params->action, $params->version, $params->protocol, $params->method, $params->authType, $params->pathname, $params->bodyType, $request, $runtime);
        } elseif (Utils::equalString($params->style, 'ROA')) {
            return $this->doROARequestWithForm($params->action, $params->version, $params->protocol, $params->method, $params->authType, $params->pathname, $params->bodyType, $request, $runtime);
        } else {
            return $this->doRPCRequest($params->action, $params->version, $params->protocol, $params->method, $params->authType, $params->bodyType, $request, $runtime);
        }
    }

    /**
     * Get user agent.
     *
     * @return string user agent
     */
    public function getUserAgent()
    {
        $userAgent = Utils::getUserAgent($this->_userAgent);

        return $userAgent;
    }

    /**
     * Get accesskey id by using credential.
     *
     * @return string accesskey id
     */
    public function getAccessKeyId()
    {
        if (Utils::isUnset($this->_credential)) {
            return '';
        }
        $accessKeyId = $this->_credential->getAccessKeyId();

        return $accessKeyId;
    }

    /**
     * Get accesskey secret by using credential.
     *
     * @return string accesskey secret
     */
    public function getAccessKeySecret()
    {
        if (Utils::isUnset($this->_credential)) {
            return '';
        }
        $secret = $this->_credential->getAccessKeySecret();

        return $secret;
    }

    /**
     * Get security token by using credential.
     *
     * @return string security token
     */
    public function getSecurityToken()
    {
        if (Utils::isUnset($this->_credential)) {
            return '';
        }
        $token = $this->_credential->getSecurityToken();

        return $token;
    }

    /**
     * Get bearer token by credential.
     *
     * @return string bearer token
     */
    public function getBearerToken()
    {
        if (Utils::isUnset($this->_credential)) {
            return '';
        }
        $token = $this->_credential->getBearerToken();

        return $token;
    }

    /**
     * Get credential type by credential.
     *
     * @return string credential type e.g. access_key
     */
    public function getType()
    {
        if (Utils::isUnset($this->_credential)) {
            return '';
        }
        $authType = $this->_credential->getType();

        return $authType;
    }

    /**
     * If inputValue is not null, return it or return defaultValue.
     *
     * @param mixed $inputValue   users input value
     * @param mixed $defaultValue default value
     *
     * @return any the final result
     */
    public static function defaultAny($inputValue, $defaultValue)
    {
        if (Utils::isUnset($inputValue)) {
            return $defaultValue;
        }

        return $inputValue;
    }

    /**
     * If the endpointRule and config.endpoint are empty, throw error.
     *
     * @param \Darabonba\OpenApi\Models\Config $config config contains the necessary information to create a client
     *
     * @return void
     *
     * @throws TeaError
     */
    public function checkConfig($config)
    {
        if (Utils::empty_($this->_endpointRule) && Utils::empty_($config->endpoint)) {
            throw new TeaError(['code' => 'ParameterMissing', 'message' => "'config.endpoint' can not be empty"]);
        }
    }

    /**
     * set RPC header for debug.
     *
     * @param string[] $headers headers for debug, this header can be used only once
     *
     * @return void
     */
    public function setRpcHeaders($headers)
    {
        $this->_headers = $headers;
    }

    /**
     * get RPC header for debug.
     *
     * @return array
     */
    public function getRpcHeaders()
    {
        $headers = $this->_headers;
        $this->_headers = null;

        return $headers;
    }
}
