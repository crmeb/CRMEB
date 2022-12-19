<?php
/*
 * Copyright 2018 Google LLC
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 *
 *     * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above
 * copyright notice, this list of conditions and the following disclaimer
 * in the documentation and/or other materials provided with the
 * distribution.
 *     * Neither the name of Google Inc. nor the names of its
 * contributors may be used to endorse or promote products derived from
 * this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace Google\ApiCore;

use Google\ApiCore\LongRunning\OperationsClient;
use Google\ApiCore\Middleware\CredentialsWrapperMiddleware;
use Google\ApiCore\Middleware\FixedHeaderMiddleware;
use Google\ApiCore\Middleware\OperationsMiddleware;
use Google\ApiCore\Middleware\OptionsFilterMiddleware;
use Google\ApiCore\Middleware\PagedMiddleware;
use Google\ApiCore\Middleware\RetryMiddleware;
use Google\ApiCore\Transport\GrpcFallbackTransport;
use Google\ApiCore\Transport\GrpcTransport;
use Google\ApiCore\Transport\RestTransport;
use Google\ApiCore\Transport\TransportInterface;
use Google\Auth\CredentialsLoader;
use Google\Auth\FetchAuthTokenInterface;
use Google\LongRunning\Operation;
use Google\Protobuf\Internal\Message;
use Grpc\Gcp\ApiConfig;
use Grpc\Gcp\Config;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Common functions used to work with various clients.
 *
 * @internal
 */
trait GapicClientTrait
{
    use ArrayTrait;
    use ValidationTrait {
        ValidationTrait::validate as traitValidate;
    }
    use GrpcSupportTrait;

    /** @var TransportInterface */
    private $transport;
    private $credentialsWrapper;

    private static $gapicVersionFromFile;
    /** @var RetrySettings[] $retrySettings */
    private $retrySettings;
    private $serviceName;
    private $agentHeader;
    private $descriptors;
    private $transportCallMethods = [
        Call::UNARY_CALL => 'startUnaryCall',
        Call::BIDI_STREAMING_CALL => 'startBidiStreamingCall',
        Call::CLIENT_STREAMING_CALL => 'startClientStreamingCall',
        Call::SERVER_STREAMING_CALL => 'startServerStreamingCall',
    ];

    /**
     * Initiates an orderly shutdown in which preexisting calls continue but new
     * calls are immediately cancelled.
     *
     * @experimental
     */
    public function close()
    {
        $this->transport->close();
    }

    /**
     * Get the transport for the client. This method is protected to support
     * use by customized clients.
     *
     * @access private
     * @return TransportInterface
     */
    protected function getTransport()
    {
        return $this->transport;
    }

    /**
     * Get the credentials for the client. This method is protected to support
     * use by customized clients.
     *
     * @access private
     * @return CredentialsWrapper
     */
    protected function getCredentialsWrapper()
    {
        return $this->credentialsWrapper;
    }

    private static function getGapicVersion(array $options)
    {
        if (isset($options['libVersion'])) {
            return $options['libVersion'];
        } else {
            if (!isset(self::$gapicVersionFromFile)) {
                self::$gapicVersionFromFile = AgentHeader::readGapicVersionFromFile(__CLASS__);
            }
            return self::$gapicVersionFromFile;
        }
    }

    private static function initGrpcGcpConfig(string $hostName, string $confPath)
    {
        $apiConfig = new ApiConfig();
        $apiConfig->mergeFromJsonString(file_get_contents($confPath));
        $config = new Config($hostName, $apiConfig);
        return $config;
    }

    /**
     * Get default options. This function should be "overridden" by clients using late static
     * binding to provide default options to the client.
     *
     * @return array
     * @access private
     */
    private static function getClientDefaults()
    {
        return [];
    }

    private function buildClientOptions(array $options)
    {
        // Build $defaultOptions starting from top level
        // variables, then going into deeper nesting, so that
        // we will not encounter missing keys
        $defaultOptions = self::getClientDefaults();
        $defaultOptions += [
            'disableRetries' => false,
            'credentials' => null,
            'credentialsConfig' => [],
            'transport' => null,
            'transportConfig' => [],
            'gapicVersion' => self::getGapicVersion($options),
            'libName' => null,
            'libVersion' => null,
            'apiEndpoint' => null,
            'clientCertSource' => null,
        ];

        $supportedTransports = $this->supportedTransports();
        foreach ($supportedTransports as $transportName) {
            if (!array_key_exists($transportName, $defaultOptions['transportConfig'])) {
                $defaultOptions['transportConfig'][$transportName] = [];
            }
        }
        if (in_array('grpc', $supportedTransports)) {
            $defaultOptions['transportConfig']['grpc'] = [
                'stubOpts' => ['grpc.service_config_disable_resolution' => 1]
            ];
        }

        // Merge defaults into $options starting from top level
        // variables, then going into deeper nesting, so that
        // we will not encounter missing keys
        $options += $defaultOptions;
        $options['credentialsConfig'] += $defaultOptions['credentialsConfig'];
        $options['transportConfig'] += $defaultOptions['transportConfig'];  // @phpstan-ignore-line
        if (isset($options['transportConfig']['grpc'])) {
            $options['transportConfig']['grpc'] += $defaultOptions['transportConfig']['grpc'];
            $options['transportConfig']['grpc']['stubOpts'] += $defaultOptions['transportConfig']['grpc']['stubOpts'];
        }
        if (isset($options['transportConfig']['rest'])) {
            $options['transportConfig']['rest'] += $defaultOptions['transportConfig']['rest'];
        }

        $this->modifyClientOptions($options);

        // serviceAddress is now deprecated and acts as an alias for apiEndpoint
        if (isset($options['serviceAddress'])) {
            $options['apiEndpoint'] = $this->pluck('serviceAddress', $options, false);
        }

        // If an API endpoint is set, ensure the "audience" does not conflict
        // with the custom endpoint by setting "user defined" scopes.
        if ($options['apiEndpoint'] != $defaultOptions['apiEndpoint']
            && empty($options['credentialsConfig']['scopes'])
            && !empty($options['credentialsConfig']['defaultScopes'])
        ) {
            $options['credentialsConfig']['scopes'] = $options['credentialsConfig']['defaultScopes'];
        }

        if (extension_loaded('sysvshm')
                && isset($options['gcpApiConfigPath'])
                && file_exists($options['gcpApiConfigPath'])
                && isset($options['apiEndpoint'])) {
            $grpcGcpConfig = self::initGrpcGcpConfig(
                $options['apiEndpoint'],
                $options['gcpApiConfigPath']
            );

            if (array_key_exists('stubOpts', $options['transportConfig']['grpc'])) {
                $options['transportConfig']['grpc']['stubOpts'] += [
                    'grpc_call_invoker' => $grpcGcpConfig->callInvoker()
                ];
            } else {
                $options['transportConfig']['grpc'] += [
                    'stubOpts' => [
                        'grpc_call_invoker' => $grpcGcpConfig->callInvoker()
                    ]
                ];
            }
        }

        // mTLS: detect and load the default clientCertSource if the environment variable
        // "GOOGLE_API_USE_CLIENT_CERTIFICATE" is true, and the cert source is available
        if (empty($options['clientCertSource']) && CredentialsLoader::shouldLoadClientCertSource()) {
            if ($defaultCertSource = CredentialsLoader::getDefaultClientCertSource()) {
                $options['clientCertSource'] = function () use ($defaultCertSource) {
                    $cert = call_user_func($defaultCertSource);

                    // the key and the cert are returned in one string
                    return [$cert, $cert];
                };
            }
        }

        // mTLS: If no apiEndpoint has been supplied by the user, and either
        // GOOGLE_API_USE_MTLS_ENDPOINT tells us to, or mTLS is available, use the mTLS endpoint.
        if ($options['apiEndpoint'] === $defaultOptions['apiEndpoint']
            && $this->shouldUseMtlsEndpoint($options)
        ) {
            $options['apiEndpoint'] = self::determineMtlsEndpoint($options['apiEndpoint']);
        }

        return $options;
    }

    private function shouldUseMtlsEndpoint(array $options)
    {
        $mtlsEndpointEnvVar = getenv('GOOGLE_API_USE_MTLS_ENDPOINT');
        if ('always' === $mtlsEndpointEnvVar) {
            return true;
        }
        if ('never' === $mtlsEndpointEnvVar) {
            return false;
        }
        // For all other cases, assume "auto" and return true if clientCertSource exists
        return !empty($options['clientCertSource']);
    }

    private static function determineMtlsEndpoint(string $apiEndpoint)
    {
        $parts = explode('.', $apiEndpoint);
        if (count($parts) < 3) {
            return $apiEndpoint; // invalid endpoint!
        }
        return sprintf('%s.mtls.%s', array_shift($parts), implode('.', $parts));
    }

    /**
     * Configures the GAPIC client based on an array of options.
     *
     * @param array $options {
     *     An array of required and optional arguments.
     *
     *     @type string $apiEndpoint
     *           The address of the API remote host, for example "example.googleapis.com. May also
     *           include the port, for example "example.googleapis.com:443"
     *     @type string $serviceAddress
     *           **Deprecated**. This option will be removed in the next major release. Please
     *           utilize the `$apiEndpoint` option instead.
     *     @type bool $disableRetries
     *           Determines whether or not retries defined by the client configuration should be
     *           disabled. Defaults to `false`.
     *     @type string|array $clientConfig
     *           Client method configuration, including retry settings. This option can be either a
     *           path to a JSON file, or a PHP array containing the decoded JSON data.
     *           By default this settings points to the default client config file, which is provided
     *           in the resources folder.
     *     @type string|array|FetchAuthTokenInterface|CredentialsWrapper $credentials
     *           The credentials to be used by the client to authorize API calls. This option
     *           accepts either a path to a credentials file, or a decoded credentials file as a
     *           PHP array.
     *           *Advanced usage*: In addition, this option can also accept a pre-constructed
     *           \Google\Auth\FetchAuthTokenInterface object or \Google\ApiCore\CredentialsWrapper
     *           object. Note that when one of these objects are provided, any settings in
     *           $authConfig will be ignored.
     *     @type array $credentialsConfig
     *           Options used to configure credentials, including auth token caching, for the client.
     *           For a full list of supporting configuration options, see
     *           \Google\ApiCore\CredentialsWrapper::build.
     *     @type string|TransportInterface $transport
     *           The transport used for executing network requests. May be either the string `rest`,
     *           `grpc`, or 'grpc-fallback'. Defaults to `grpc` if gRPC support is detected on the system.
     *           *Advanced usage*: Additionally, it is possible to pass in an already instantiated
     *           TransportInterface object. Note that when this objects is provided, any settings in
     *           $transportConfig, and any `$apiEndpoint` setting, will be ignored.
     *     @type array $transportConfig
     *           Configuration options that will be used to construct the transport. Options for
     *           each supported transport type should be passed in a key for that transport. For
     *           example:
     *           $transportConfig = [
     *               'grpc' => [...],
     *               'rest' => [...],
     *               'grpc-fallback' => [...],
     *           ];
     *           See the GrpcTransport::build and RestTransport::build
     *           methods for the supported options.
     *     @type string $versionFile
     *           The path to a file which contains the current version of the client.
     *     @type string $descriptorsConfigPath
     *           The path to a descriptor configuration file.
     *     @type string $serviceName
     *           The name of the service.
     *     @type string $libName
     *           The name of the client application.
     *     @type string $libVersion
     *           The version of the client application.
     *     @type string $gapicVersion
     *           The code generator version of the GAPIC library.
     *     @type callable $clientCertSource
     *           A callable which returns the client cert as a string.
     * }
     * @throws ValidationException
     */
    private function setClientOptions(array $options)
    {
        // serviceAddress is now deprecated and acts as an alias for apiEndpoint
        if (isset($options['serviceAddress'])) {
            $options['apiEndpoint'] = $this->pluck('serviceAddress', $options, false);
        }
        $this->validateNotNull($options, [
            'apiEndpoint',
            'serviceName',
            'descriptorsConfigPath',
            'clientConfig',
            'disableRetries',
            'credentialsConfig',
            'transportConfig',
        ]);
        $this->traitValidate($options, [
            'credentials',
            'transport',
            'gapicVersion',
            'libName',
            'libVersion',
        ]);

        $clientConfig = $options['clientConfig'];
        if (is_string($clientConfig)) {
            $clientConfig = json_decode(file_get_contents($clientConfig), true);
        }
        $this->serviceName = $options['serviceName'];
        $this->retrySettings = RetrySettings::load(
            $this->serviceName,
            $clientConfig,
            $options['disableRetries']
        );

        // Edge case: If the client has the gRPC extension installed, but is
        // a REST-only library, then the grpcVersion header should not be set.
        if ($this->transport instanceof GrpcTransport) {
            $options['grpcVersion'] = phpversion('grpc');
            unset($options['restVersion']);
        } elseif ($this->transport instanceof RestTransport
            || $this->transport instanceof GrpcFallbackTransport) {
            unset($options['grpcVersion']);
            $options['restVersion'] = Version::getApiCoreVersion();
        }

        $this->agentHeader = AgentHeader::buildAgentHeader(
            $this->pluckArray([
                'libName',
                'libVersion',
                'gapicVersion'
            ], $options)
        );
        self::validateFileExists($options['descriptorsConfigPath']);
        $descriptors = require($options['descriptorsConfigPath']);
        $this->descriptors = $descriptors['interfaces'][$this->serviceName];

        $this->credentialsWrapper = $this->createCredentialsWrapper(
            $options['credentials'],
            $options['credentialsConfig']
        );

        $transport = $options['transport'] ?: self::defaultTransport();
        $this->transport = $transport instanceof TransportInterface
            ? $transport
            : $this->createTransport(
                $options['apiEndpoint'],
                $transport,
                $options['transportConfig'],
                $options['clientCertSource']
            );
    }

    /**
     * @param mixed $credentials
     * @param array $credentialsConfig
     * @return CredentialsWrapper
     * @throws ValidationException
     */
    private function createCredentialsWrapper($credentials, array $credentialsConfig)
    {
        if (is_null($credentials)) {
            return CredentialsWrapper::build($credentialsConfig);
        } elseif (is_string($credentials) || is_array($credentials)) {
            return CredentialsWrapper::build(['keyFile' => $credentials] + $credentialsConfig);
        } elseif ($credentials instanceof FetchAuthTokenInterface) {
            $authHttpHandler = isset($credentialsConfig['authHttpHandler'])
                ? $credentialsConfig['authHttpHandler']
                : null;
            return new CredentialsWrapper($credentials, $authHttpHandler);
        } elseif ($credentials instanceof CredentialsWrapper) {
            return $credentials;
        } else {
            throw new ValidationException(
                'Unexpected value in $auth option, got: ' .
                print_r($credentials, true)
            );
        }
    }

    /**
     * @param string $apiEndpoint
     * @param string $transport
     * @param array $transportConfig
     * @param callable $clientCertSource
     * @return TransportInterface
     * @throws ValidationException
     */
    private function createTransport(
        string $apiEndpoint,
        $transport,
        array $transportConfig,
        callable $clientCertSource = null
    ) {
        if (!is_string($transport)) {
            throw new ValidationException(
                "'transport' must be a string, instead got:" .
                print_r($transport, true)
            );
        }
        $supportedTransports = self::supportedTransports();
        if (!in_array($transport, $supportedTransports)) {
            throw new ValidationException(sprintf(
                'Unexpected transport option "%s". Supported transports: %s',
                $transport,
                implode(', ', $supportedTransports)
            ));
        }
        $configForSpecifiedTransport = isset($transportConfig[$transport])
            ? $transportConfig[$transport]
            : [];
        $configForSpecifiedTransport['clientCertSource'] = $clientCertSource;
        switch ($transport) {
            case 'grpc':
                return GrpcTransport::build($apiEndpoint, $configForSpecifiedTransport);
            case 'grpc-fallback':
                return GrpcFallbackTransport::build($apiEndpoint, $configForSpecifiedTransport);
            case 'rest':
                if (!isset($configForSpecifiedTransport['restClientConfigPath'])) {
                    throw new ValidationException(
                        "The 'restClientConfigPath' config is required for 'rest' transport."
                    );
                }
                $restConfigPath = $configForSpecifiedTransport['restClientConfigPath'];
                return RestTransport::build($apiEndpoint, $restConfigPath, $configForSpecifiedTransport);
            default:
                throw new ValidationException(
                    "Unexpected 'transport' option: $transport. " .
                    "Supported values: ['grpc', 'rest', 'grpc-fallback']"
                );
        }
    }

    /**
     * @param array $options
     * @return OperationsClient
     */
    private function createOperationsClient(array $options)
    {
        $this->pluckArray([
            'serviceName',
            'clientConfig',
            'descriptorsConfigPath',
        ], $options);

        // User-supplied operations client
        if ($operationsClient = $this->pluck('operationsClient', $options, false)) {
            return $operationsClient;
        }

        // operationsClientClass option
        $operationsClientClass = $this->pluck('operationsClientClass', $options, false)
            ?: OperationsCLient::class;
        return new $operationsClientClass($options);
    }

    /**
     * @return string
     */
    private static function defaultTransport()
    {
        return self::getGrpcDependencyStatus()
            ? 'grpc'
            : 'rest';
    }

    private function validateCallConfig(string $methodName)
    {
        // Ensure a method descriptor exists for the target method.
        if (!isset($this->descriptors[$methodName])) {
            throw new ValidationException("Requested method '$methodName' does not exist in descriptor configuration.");
        }
        $methodDescriptors = $this->descriptors[$methodName];

        // Ensure required descriptor configuration exists.
        if (!isset($methodDescriptors['callType'])) {
            throw new ValidationException("Requested method '$methodName' does not have a callType " .
                "in descriptor configuration.");
        }
        $callType = $methodDescriptors['callType'];

        // Validate various callType specific configurations.
        if ($callType == Call::LONGRUNNING_CALL) {
            if (!isset($methodDescriptors['longRunning'])) {
                throw new ValidationException("Requested method '$methodName' does not have a longRunning config " .
                    "in descriptor configuration.");
            }
            // @TODO: check if the client implements `OperationsClientInterface` instead
            if (!method_exists($this, 'getOperationsClient')) {
                throw new ValidationException("Client missing required getOperationsClient " .
                    "for longrunning call '$methodName'");
            }
        } elseif ($callType == Call::PAGINATED_CALL) {
            if (!isset($methodDescriptors['pageStreaming'])) {
                throw new ValidationException("Requested method '$methodName' with callType PAGINATED_CALL does not " .
                    "have a pageStreaming in descriptor configuration.");
            }
        }
        
        // LRO are either Standard LRO response type or custom, which are handled by
        // startOperationCall, so no need to validate responseType for those callType.
        if ($callType != Call::LONGRUNNING_CALL) {
            if (!isset($methodDescriptors['responseType'])) {
                throw new ValidationException("Requested method '$methodName' does not have a responseType " .
                    "in descriptor configuration.");
            }
        }

        return $methodDescriptors;
    }

    /**
     * @param string $methodName
     * @param Message $request
     * @param array $optionalArgs {
     *     Call Options
     *
     *     @type array $headers                     [optional] key-value array containing headers
     *     @type int $timeoutMillis                 [optional] the timeout in milliseconds for the call
     *     @type array $transportOptions            [optional] transport-specific call options
     *     @type RetrySettings|array $retrySettings [optional] A retry settings override for the call.
     * }
     *
     * @experimental
     *
     * @return PromiseInterface
     */
    private function startAsyncCall(
        string $methodName,
        Message $request,
        array $optionalArgs = []
    ) {
        // Convert method name to the UpperCamelCase of RPC names from lowerCamelCase of GAPIC method names
        // in order to find the method in the descriptor config.
        $methodName = ucfirst($methodName);
        $methodDescriptors = $this->validateCallConfig($methodName);
        
        $callType = $methodDescriptors['callType'];
        
        switch ($callType) {
            case Call::PAGINATED_CALL:
                return $this->getPagedListResponseAsync(
                    $methodName,
                    $optionalArgs,
                    $methodDescriptors['responseType'],
                    $request,
                    $methodDescriptors['interfaceOverride'] ?? $this->serviceName
                );
            case Call::SERVER_STREAMING_CALL:
            case Call::CLIENT_STREAMING_CALL:
            case Call::BIDI_STREAMING_CALL:
                throw new ValidationException("Call type '$callType' of requested method " .
                    "'$methodName' is not supported for async execution.");
        }

        return $this->startApiCall($methodName, $request, $optionalArgs);
    }

    /**
     * @param string $methodName
     * @param Message $request
     * @param array $optionalArgs {
     *     Call Options
     *
     *     @type array $headers [optional] key-value array containing headers
     *     @type int $timeoutMillis [optional] the timeout in milliseconds for the call
     *     @type array $transportOptions [optional] transport-specific call options
     *     @type RetrySettings|array $retrySettings [optional] A retry settings
     *           override for the call.
     * }
     *
     * @experimental
     *
     * @return PromiseInterface|PagedListResponse|BidiStream|ClientStream|ServerStream
     */
    private function startApiCall(
        string $methodName,
        Message $request = null,
        array $optionalArgs = []
    ) {
        $methodDescriptors =$this->validateCallConfig($methodName);
        $callType = $methodDescriptors['callType'];
        
        // Prepare request-based headers, merge with user-provided headers,
        // which take precedence.
        $headerParams = $methodDescriptors['headerParams'] ?? [];
        $requestHeaders = $this->buildRequestParamsHeader($headerParams, $request);
        $optionalArgs['headers'] = array_merge($requestHeaders, $optionalArgs['headers'] ?? []);

        // Default the interface name, if not set, to the client's protobuf service name.
        $interfaceName = $methodDescriptors['interfaceOverride'] ?? $this->serviceName;

        // Handle call based on call type configured in the method descriptor config.
        if ($callType == Call::LONGRUNNING_CALL) {
            return $this->startOperationsCall(
                $methodName,
                $optionalArgs,
                $request,
                $this->getOperationsClient(),
                $interfaceName,
                // Custom operations will define their own operation response type, whereas standard
                // LRO defaults to the same type.
                $methodDescriptors['responseType'] ?? null
            );
        }

        // Fully-qualified name of the response message PHP class.
        $decodeType = $methodDescriptors['responseType'];

        if ($callType == Call::PAGINATED_CALL) {
            return $this->getPagedListResponse($methodName, $optionalArgs, $decodeType, $request, $interfaceName);
        }

        // Unary, and all Streaming types handled by startCall.
        return $this->startCall($methodName, $decodeType, $optionalArgs, $request, $callType, $interfaceName);
    }

    /**
     * @param string $methodName
     * @param string $decodeType
     * @param array $optionalArgs {
     *     Call Options
     *
     *     @type array $headers [optional] key-value array containing headers
     *     @type int $timeoutMillis [optional] the timeout in milliseconds for the call
     *     @type array $transportOptions [optional] transport-specific call options
     *     @type RetrySettings|array $retrySettings [optional] A retry settings
     *           override for the call.
     * }
     * @param Message $request
     * @param int $callType
     * @param string $interfaceName
     *
     * @return PromiseInterface|BidiStream|ClientStream|ServerStream
     */
    private function startCall(
        string $methodName,
        string $decodeType,
        array $optionalArgs = [],
        Message $request = null,
        int $callType = Call::UNARY_CALL,
        string $interfaceName = null
    ) {
        $callStack = $this->createCallStack(
            $this->configureCallConstructionOptions($methodName, $optionalArgs)
        );

        $descriptor = isset($this->descriptors[$methodName]['grpcStreaming'])
            ? $this->descriptors[$methodName]['grpcStreaming']
            : null;

        $call = new Call(
            $this->buildMethod($interfaceName, $methodName),
            $decodeType,
            $request,
            $descriptor,
            $callType
        );
        switch ($callType) {
            case Call::UNARY_CALL:
                $this->modifyUnaryCallable($callStack);
                break;
            case Call::BIDI_STREAMING_CALL:
            case Call::CLIENT_STREAMING_CALL:
            case Call::SERVER_STREAMING_CALL:
                $this->modifyStreamingCallable($callStack);
                break;
        }

        return $callStack($call, $optionalArgs + array_filter([
            'audience' => self::getDefaultAudience()
        ]));
    }

    /**
     * @param array $callConstructionOptions {
     *     Call Construction Options
     *
     *     @type RetrySettings $retrySettings [optional] A retry settings override
     *           For the call.
     * }
     *
     * @return callable
     */
    private function createCallStack(array $callConstructionOptions)
    {
        $quotaProject = $this->credentialsWrapper->getQuotaProject();
        $fixedHeaders = $this->agentHeader;
        if ($quotaProject) {
            $fixedHeaders += [
                'X-Goog-User-Project' => [$quotaProject]
            ];
        }
        $callStack = function (Call $call, array $options) {
            $startCallMethod = $this->transportCallMethods[$call->getCallType()];
            return $this->transport->$startCallMethod($call, $options);
        };
        $callStack = new CredentialsWrapperMiddleware($callStack, $this->credentialsWrapper);
        $callStack = new FixedHeaderMiddleware($callStack, $fixedHeaders, true);
        $callStack = new RetryMiddleware($callStack, $callConstructionOptions['retrySettings']);
        $callStack = new OptionsFilterMiddleware($callStack, [
            'headers',
            'timeoutMillis',
            'transportOptions',
            'metadataCallback',
            'audience',
        ]);

        return $callStack;
    }

    /**
     * @param string $methodName
     * @param array $optionalArgs {
     *     Optional arguments
     *
     *     @type RetrySettings|array $retrySettings [optional] A retry settings
     *           override for the call.
     * }
     *
     * @return array
     */
    private function configureCallConstructionOptions(string $methodName, array $optionalArgs)
    {
        $retrySettings = $this->retrySettings[$methodName];
        // Allow for retry settings to be changed at call time
        if (isset($optionalArgs['retrySettings'])) {
            if ($optionalArgs['retrySettings'] instanceof RetrySettings) {
                $retrySettings = $optionalArgs['retrySettings'];
            } else {
                $retrySettings = $retrySettings->with(
                    $optionalArgs['retrySettings']
                );
            }
        }
        return [
            'retrySettings' => $retrySettings,
        ];
    }

    /**
     * @param string $methodName
     * @param array $optionalArgs {
     *     Call Options
     *
     *     @type array $headers [optional] key-value array containing headers
     *     @type int $timeoutMillis [optional] the timeout in milliseconds for the call
     *     @type array $transportOptions [optional] transport-specific call options
     * }
     * @param Message $request
     * @param OperationsClient|object $client
     * @param string $interfaceName
     * @param string $operationClass If provided, will be used instead of the default
     *                               operation response class of {@see \Google\LongRunning\Operation}.
     *
     * @return PromiseInterface
     */
    private function startOperationsCall(
        string $methodName,
        array $optionalArgs,
        Message $request,
        $client,
        string $interfaceName = null,
        string $operationClass = null
    ) {
        $callStack = $this->createCallStack(
            $this->configureCallConstructionOptions($methodName, $optionalArgs)
        );

        $descriptor = $this->descriptors[$methodName]['longRunning'];

        // Call the methods supplied in "additionalArgumentMethods" on the request Message object
        // to build the "additionalOperationArguments" option for the operation response.
        if (isset($descriptor['additionalArgumentMethods'])) {
            $additionalArgs = [];
            foreach ($descriptor['additionalArgumentMethods'] as $additionalArgsMethodName) {
                $additionalArgs[] = $request->$additionalArgsMethodName();
            }
            $descriptor['additionalOperationArguments'] = $additionalArgs;
            unset($descriptor['additionalArgumentMethods']);
        }

        $callStack = new OperationsMiddleware($callStack, $client, $descriptor);

        $call = new Call(
            $this->buildMethod($interfaceName, $methodName),
            $operationClass ?: Operation::class,
            $request,
            [],
            Call::UNARY_CALL
        );

        $this->modifyUnaryCallable($callStack);
        return $callStack($call, $optionalArgs + array_filter([
            'audience' => self::getDefaultAudience()
        ]));
    }

    /**
     * @param string $methodName
     * @param array $optionalArgs
     * @param string $decodeType
     * @param Message $request
     * @param string $interfaceName
     *
     * @return PagedListResponse
     */
    private function getPagedListResponse(
        string $methodName,
        array $optionalArgs,
        string $decodeType,
        Message $request,
        string $interfaceName = null
    ) {
        return $this->getPagedListResponseAsync(
            $methodName,
            $optionalArgs,
            $decodeType,
            $request,
            $interfaceName
        )->wait();
    }

    /**
     * @param string $methodName
     * @param array $optionalArgs
     * @param string $decodeType
     * @param Message $request
     * @param string $interfaceName
     *
     * @return PromiseInterface
     */
    private function getPagedListResponseAsync(
        string $methodName,
        array $optionalArgs,
        string $decodeType,
        Message $request,
        string $interfaceName = null
    ) {
        $callStack = $this->createCallStack(
            $this->configureCallConstructionOptions($methodName, $optionalArgs)
        );
        $descriptor = new PageStreamingDescriptor(
            $this->descriptors[$methodName]['pageStreaming']
        );
        $callStack = new PagedMiddleware($callStack, $descriptor);

        $call = new Call(
            $this->buildMethod($interfaceName, $methodName),
            $decodeType,
            $request,
            [],
            Call::UNARY_CALL
        );

        $this->modifyUnaryCallable($callStack);
        return $callStack($call, $optionalArgs + array_filter([
            'audience' => self::getDefaultAudience()
        ]));
    }

    /**
     * @param string $interfaceName
     * @param string $methodName
     *
     * @return string
     */
    private function buildMethod(string $interfaceName = null, string $methodName = null)
    {
        return sprintf(
            '%s/%s',
            $interfaceName ?: $this->serviceName,
            $methodName
        );
    }

    /**
     * @param array $headerParams
     * @param Message|null $request
     *
     * @return array
     */
    private function buildRequestParamsHeader(array $headerParams, Message $request = null)
    {
        $headers = [];
        
        // No request message means no request-based headers.
        if (!$request) {
            return $headers;
        }
        
        foreach ($headerParams as $headerParam) {
            $msg = $request;
            $value = null;
            foreach ($headerParam['fieldAccessors'] as $accessor) {
                $value = $msg->$accessor();
                
                // In case the field in question is nested in another message,
                // skip the header param when the nested message field is unset.
                $msg = $value;
                if (is_null($msg)) {
                    break;
                }
            }

            $keyName = $headerParam['keyName'];
            
            // If there are value pattern matchers configured and the target
            // field was set, evaluate the matchers in the order that they were
            // annotated in with last one matching wins.
            $original = $value;
            $matchers = isset($headerParam['matchers']) && !is_null($value) ?
                $headerParam['matchers'] :
                [];
            foreach ($matchers as $matcher) {
                $matches = [];
                if (preg_match($matcher, $original, $matches)) {
                    $value = $matches[$keyName];
                }
            }

            // If there are no matches or the target field was unset, skip this
            // header param.
            if (!$value) {
                continue;
            }

            $headers[$keyName] = $value;
        }

        $requestParams = new RequestParamsHeaderDescriptor($headers);

        return $requestParams->getHeader();
    }

    /**
     * The SERVICE_ADDRESS constant is set by GAPIC clients
     */
    private static function getDefaultAudience()
    {
        if (!defined('self::SERVICE_ADDRESS')) {
            return null;
        }
        return 'https://' . self::SERVICE_ADDRESS . '/'; // @phpstan-ignore-line
    }

    /**
     * This defaults to all three transports, which One-Platform supports.
     * Discovery clients should define this function and only return ['rest'].
     */
    private static function supportedTransports()
    {
        return ['grpc', 'grpc-fallback', 'rest'];
    }

    // Gapic Client Extension Points
    // The methods below provide extension points that can be used to customize client
    // functionality. These extension points are currently considered
    // private and may change at any time.

    /**
     * Modify options passed to the client before calling setClientOptions.
     *
     * @param array $options
     * @access private
     */
    protected function modifyClientOptions(array &$options)
    {
        // Do nothing - this method exists to allow option modification by partial veneers.
    }

    /**
     * Modify the unary callable.
     *
     * @param callable $callable
     * @access private
     */
    protected function modifyUnaryCallable(callable &$callable)
    {
        // Do nothing - this method exists to allow callable modification by partial veneers.
    }

    /**
     * Modify the streaming callable.
     *
     * @param callable $callable
     * @access private
     */
    protected function modifyStreamingCallable(callable &$callable)
    {
        // Do nothing - this method exists to allow callable modification by partial veneers.
    }
}
