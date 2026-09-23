<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\ArrayShape;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\Exceptions;
use Overtrue\Socialite\User;
use Psr\Http\Message\StreamInterface;

/**
 * @see https://www.tapd.cn/help/show#1120003271001000708
 */
class Tapd extends Base
{
    public const NAME = 'tapd';

    protected string $baseUrl = 'https://api.tapd.cn';

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/quickstart/testauth');
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/tokens/request_token';
    }

    protected function getRefreshTokenUrl(): string
    {
        return $this->baseUrl.'/tokens/refresh_token';
    }

    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic '.\base64_encode(\sprintf('%s:%s', $this->getClientId(), $this->getClientSecret())),
            ],
            'form_params' => $this->getTokenFields($code),
        ]);

        return $this->normalizeAccessTokenResponse($response->getBody());
    }

    #[ArrayShape([
        Contracts\RFC6749_ABNF_GRANT_TYPE => 'string',
        Contracts\RFC6749_ABNF_REDIRECT_URI => 'null|string',
        Contracts\RFC6749_ABNF_CODE => 'string',
    ])]
    protected function getTokenFields(string $code): array
    {
        return [
            Contracts\RFC6749_ABNF_GRANT_TYPE => Contracts\RFC6749_ABNF_AUTHORATION_CODE,
            Contracts\RFC6749_ABNF_REDIRECT_URI => $this->redirectUrl,
            Contracts\RFC6749_ABNF_CODE => $code,
        ];
    }

    #[ArrayShape([
        Contracts\RFC6749_ABNF_GRANT_TYPE => 'string',
        Contracts\RFC6749_ABNF_REDIRECT_URI => 'null|string',
        Contracts\RFC6749_ABNF_REFRESH_TOKEN => 'string',
    ])]
    protected function getRefreshTokenFields(string $refreshToken): array
    {
        return [
            Contracts\RFC6749_ABNF_GRANT_TYPE => Contracts\RFC6749_ABNF_REFRESH_TOKEN,
            Contracts\RFC6749_ABNF_REDIRECT_URI => $this->redirectUrl,
            Contracts\RFC6749_ABNF_REFRESH_TOKEN => $refreshToken,
        ];
    }

    public function tokenFromRefreshToken(string $refreshToken): array
    {
        $response = $this->getHttpClient()->post($this->getRefreshTokenUrl(), [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic '.\base64_encode(\sprintf('%s:%s', $this->getClientId(), $this->getClientSecret())),
            ],
            'form_params' => $this->getRefreshTokenFields($refreshToken),
        ]);

        return $this->normalizeAccessTokenResponse((string) $response->getBody());
    }

    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get($this->baseUrl.'/users/info', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return $this->fromJsonBody($response);
    }

    /**
     * @throws Exceptions\BadRequestException
     */
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        if (! isset($user['status']) && $user['status'] != 1) {
            throw new Exceptions\BadRequestException('用户信息获取失败');
        }

        return new User([
            Contracts\ABNF_ID => $user['data'][Contracts\ABNF_ID] ?? null,
            Contracts\ABNF_NICKNAME => $user['data']['nick'] ?? null,
            Contracts\ABNF_NAME => $user['data'][Contracts\ABNF_NAME] ?? null,
            Contracts\ABNF_EMAIL => $user['data'][Contracts\ABNF_EMAIL] ?? null,
            Contracts\ABNF_AVATAR => $user['data'][Contracts\ABNF_AVATAR] ?? null,
        ]);
    }

    /**
     * @throws Exceptions\AuthorizeFailedException
     */
    protected function normalizeAccessTokenResponse(mixed $response): array
    {
        if ($response instanceof StreamInterface) {
            $response->rewind();
            $response = (string) $response;
        }

        if (\is_string($response)) {
            $response = \json_decode($response, true) ?? [];
        }

        if (! \is_array($response)) {
            throw new Exceptions\AuthorizeFailedException('Invalid token response', [$response]);
        }

        if (empty($response['data'][$this->accessTokenKey] ?? null)) {
            throw new Exceptions\AuthorizeFailedException('Authorize Failed: '.\json_encode($response, JSON_UNESCAPED_UNICODE), $response);
        }

        return $response + [
            Contracts\RFC6749_ABNF_ACCESS_TOKEN => $response['data'][$this->accessTokenKey],
            Contracts\RFC6749_ABNF_REFRESH_TOKEN => $response['data'][$this->refreshTokenKey] ?? null,
            Contracts\RFC6749_ABNF_EXPIRES_IN => \intval($response['data'][$this->expiresInKey] ?? 0),
        ];
    }
}
