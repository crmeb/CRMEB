<?php

namespace Overtrue\Socialite\Providers;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Utils;
use JetBrains\PhpStorm\ArrayShape;
use Overtrue\Socialite\Config;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\Exceptions;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class Base implements Contracts\ProviderInterface
{
    public const NAME = null;

    protected ?string $state = null;

    protected Config $config;

    protected ?string $redirectUrl;

    protected array $parameters = [];

    protected array $scopes = [];

    protected string $scopeSeparator = ',';

    protected GuzzleClient $httpClient;

    protected array $guzzleOptions = [];

    protected int $encodingType = PHP_QUERY_RFC1738;

    protected string $expiresInKey = Contracts\RFC6749_ABNF_EXPIRES_IN;

    protected string $accessTokenKey = Contracts\RFC6749_ABNF_ACCESS_TOKEN;

    protected string $refreshTokenKey = Contracts\RFC6749_ABNF_REFRESH_TOKEN;

    public function __construct(array $config)
    {
        $this->config = new Config($config);

        // set scopes
        if ($this->config->has('scopes') && is_array($this->config->get('scopes'))) {
            $this->scopes = $this->getConfig()->get('scopes');
        } elseif ($this->config->has(Contracts\RFC6749_ABNF_SCOPE) && is_string($this->getConfig()->get(Contracts\RFC6749_ABNF_SCOPE))) {
            $this->scopes = [$this->getConfig()->get(Contracts\RFC6749_ABNF_SCOPE)];
        }

        // normalize Contracts\RFC6749_ABNF_CLIENT_ID
        if (! $this->config->has(Contracts\RFC6749_ABNF_CLIENT_ID)) {
            $id = $this->config->get(Contracts\ABNF_APP_ID);
            if ($id != null) {
                $this->config->set(Contracts\RFC6749_ABNF_CLIENT_ID, $id);
            }
        }

        // normalize Contracts\RFC6749_ABNF_CLIENT_SECRET
        if (! $this->config->has(Contracts\RFC6749_ABNF_CLIENT_SECRET)) {
            $secret = $this->config->get(Contracts\ABNF_APP_SECRET);
            if ($secret != null) {
                $this->config->set(Contracts\RFC6749_ABNF_CLIENT_SECRET, $secret);
            }
        }

        // normalize redirect uri aliases to RFC6749_ABNF_REDIRECT_URI
        if (! $this->config->has(Contracts\RFC6749_ABNF_REDIRECT_URI)) {
            $this->config->set(
                Contracts\RFC6749_ABNF_REDIRECT_URI,
                $this->config->get('redirect') ?? $this->config->get('redirect_url')
            );
        }

        // keep legacy alias available for compatibility
        if (! $this->config->has('redirect_url')) {
            $this->config->set('redirect_url', $this->config->get(Contracts\RFC6749_ABNF_REDIRECT_URI));
        }

        $this->redirectUrl = $this->config->get(Contracts\RFC6749_ABNF_REDIRECT_URI);
    }

    abstract protected function getAuthUrl(): string;

    abstract protected function getTokenUrl(): string;

    abstract protected function getUserByToken(string $token): array;

    abstract protected function mapUserToObject(array $user): Contracts\UserInterface;

    public function redirect(?string $redirectUrl = null): string
    {
        if (! empty($redirectUrl)) {
            $this->withRedirectUrl($redirectUrl);
        }

        return $this->getAuthUrl();
    }

    public function userFromCode(string $code): Contracts\UserInterface
    {
        $tokenResponse = $this->tokenFromCode($code);
        $user = $this->userFromToken($tokenResponse[$this->accessTokenKey]);

        return $user->setRefreshToken($tokenResponse[$this->refreshTokenKey] ?? null)
            ->setExpiresIn($tokenResponse[$this->expiresInKey] ?? null)
            ->setTokenResponse($tokenResponse);
    }

    public function userFromToken(string $token): Contracts\UserInterface
    {
        $user = $this->getUserByToken($token);

        return $this->mapUserToObject($user)->setProvider($this)->setRaw($user)->setAccessToken($token);
    }

    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->post(
            $this->getTokenUrl(),
            [
                'form_params' => $this->getTokenFields($code),
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]
        );

        return $this->normalizeAccessTokenResponse((string) $response->getBody());
    }

    /**
     * @throws Exceptions\MethodDoesNotSupportException
     */
    public function refreshToken(string $refreshToken): mixed
    {
        throw new Exceptions\MethodDoesNotSupportException('refreshToken does not support.');
    }

    public function withRedirectUrl(string $redirectUrl): Contracts\ProviderInterface
    {
        $this->redirectUrl = $redirectUrl;
        $this->config->set(Contracts\RFC6749_ABNF_REDIRECT_URI, $redirectUrl);
        $this->config->set('redirect_url', $redirectUrl);

        return $this;
    }

    public function withState(string $state): Contracts\ProviderInterface
    {
        $this->state = $state;

        return $this;
    }

    public function scopes(array $scopes): Contracts\ProviderInterface
    {
        $this->scopes = $scopes;

        return $this;
    }

    public function with(array $parameters): Contracts\ProviderInterface
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function withScopeSeparator(string $scopeSeparator): Contracts\ProviderInterface
    {
        $this->scopeSeparator = $scopeSeparator;

        return $this;
    }

    public function getClientId(): ?string
    {
        return $this->config->get(Contracts\RFC6749_ABNF_CLIENT_ID);
    }

    public function getClientSecret(): ?string
    {
        return $this->config->get(Contracts\RFC6749_ABNF_CLIENT_SECRET);
    }

    public function getHttpClient(): GuzzleClient
    {
        return $this->httpClient ?? new GuzzleClient($this->guzzleOptions);
    }

    public function setGuzzleOptions(array $config): Contracts\ProviderInterface
    {
        $this->guzzleOptions = $config;

        return $this;
    }

    public function getGuzzleOptions(): array
    {
        return $this->guzzleOptions;
    }

    protected function formatScopes(array $scopes, string $scopeSeparator): string
    {
        return \implode($scopeSeparator, $scopes);
    }

    #[ArrayShape([
        Contracts\RFC6749_ABNF_CLIENT_ID => 'null|string',
        Contracts\RFC6749_ABNF_CLIENT_SECRET => 'null|string',
        Contracts\RFC6749_ABNF_CODE => 'string',
        Contracts\RFC6749_ABNF_REDIRECT_URI => 'null|string',
    ])]
    protected function getTokenFields(string $code): array
    {
        return [
            Contracts\RFC6749_ABNF_CLIENT_ID => $this->getClientId(),
            Contracts\RFC6749_ABNF_CLIENT_SECRET => $this->getClientSecret(),
            Contracts\RFC6749_ABNF_CODE => $code,
            Contracts\RFC6749_ABNF_REDIRECT_URI => $this->redirectUrl,
        ];
    }

    protected function buildAuthUrlFromBase(string $url): string
    {
        $query = $this->getCodeFields() + ($this->state ? [Contracts\RFC6749_ABNF_STATE => $this->state] : []);

        return $url.'?'.\http_build_query($query, '', '&', $this->encodingType);
    }

    protected function getCodeFields(): array
    {
        $fields = \array_merge(
            [
                Contracts\RFC6749_ABNF_CLIENT_ID => $this->getClientId(),
                Contracts\RFC6749_ABNF_REDIRECT_URI => $this->redirectUrl,
                Contracts\RFC6749_ABNF_SCOPE => $this->formatScopes($this->scopes, $this->scopeSeparator),
                Contracts\RFC6749_ABNF_RESPONSE_TYPE => Contracts\RFC6749_ABNF_CODE,
            ],
            $this->parameters
        );

        if ($this->state) {
            $fields[Contracts\RFC6749_ABNF_STATE] = $this->state;
        }

        return $fields;
    }

    /**
     * @throws Exceptions\AuthorizeFailedException
     */
    protected function normalizeAccessTokenResponse(mixed $response): array
    {
        if ($response instanceof StreamInterface) {
            $response->tell() && $response->rewind();
            $response = (string) $response;
        }

        if (\is_string($response)) {
            $response = Utils::jsonDecode($response, true);
        }

        if (! \is_array($response)) {
            throw new Exceptions\AuthorizeFailedException('Invalid token response', [$response]);
        }

        if (empty($response[$this->accessTokenKey])) {
            throw new Exceptions\AuthorizeFailedException('Authorize Failed: '.Utils::jsonEncode($response, \JSON_UNESCAPED_UNICODE), $response);
        }

        return $response + [
            Contracts\RFC6749_ABNF_ACCESS_TOKEN => $response[$this->accessTokenKey],
            Contracts\RFC6749_ABNF_REFRESH_TOKEN => $response[$this->refreshTokenKey] ?? null,
            Contracts\RFC6749_ABNF_EXPIRES_IN => \intval($response[$this->expiresInKey] ?? 0),
        ];
    }

    protected function fromJsonBody(MessageInterface $response): array
    {
        $result = Utils::jsonDecode((string) $response->getBody(), true);

        \is_array($result) || throw new Exceptions\InvalidArgumentException('Decoded the given response payload failed.');

        return $result;
    }
}
