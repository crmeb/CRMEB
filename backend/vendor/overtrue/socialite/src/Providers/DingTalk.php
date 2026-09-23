<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\Exceptions;
use Overtrue\Socialite\User;

/**
 * “第三方个人应用”获取用户信息
 *
 * @see https://ding-doc.dingtalk.com/doc#/serverapi3/mrugr3
 *
 * 暂不支持“第三方企业应用”获取用户信息
 * @see https://ding-doc.dingtalk.com/doc#/serverapi3/hv357q
 */
class DingTalk extends Base
{
    public const NAME = 'dingtalk';

    protected string $getUserByCode = 'https://oapi.dingtalk.com/sns/getuserinfo_bycode';

    protected array $scopes = ['snsapi_login'];

    protected string $scopeSeparator = ' ';

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://oapi.dingtalk.com/connect/qrconnect');
    }

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    protected function getTokenUrl(): string
    {
        throw new Exceptions\InvalidArgumentException('not supported to get access token.');
    }

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    protected function getUserByToken(string $token): array
    {
        throw new Exceptions\InvalidArgumentException('Unable to use token get User.');
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User([
            Contracts\ABNF_NAME => $user['nick'] ?? null,
            Contracts\ABNF_NICKNAME => $user['nick'] ?? null,
            Contracts\ABNF_ID => $user[Contracts\ABNF_OPEN_ID] ?? null,
            Contracts\ABNF_EMAIL => null,
            Contracts\ABNF_AVATAR => null,
        ]);
    }

    protected function getCodeFields(): array
    {
        return array_merge(
            [
                'appid' => $this->getClientId(),
                Contracts\RFC6749_ABNF_GRANT_TYPE => Contracts\RFC6749_ABNF_AUTHORATION_CODE,
                Contracts\RFC6749_ABNF_CODE => $this->formatScopes($this->scopes, $this->scopeSeparator),
                Contracts\RFC6749_ABNF_REDIRECT_URI => $this->redirectUrl,
            ],
            $this->parameters
        );
    }

    public function getClientId(): ?string
    {
        return $this->getConfig()->get(Contracts\ABNF_APP_ID)
            ?? $this->getConfig()->get('appid')
            ?? $this->getConfig()->get('appId')
            ?? $this->getConfig()->get(Contracts\RFC6749_ABNF_CLIENT_ID);
    }

    public function getClientSecret(): ?string
    {
        return $this->getConfig()->get(Contracts\ABNF_APP_SECRET)
            ?? $this->getConfig()->get('appSecret')
            ?? $this->getConfig()->get(Contracts\RFC6749_ABNF_CLIENT_SECRET);
    }

    protected function createSignature(int $time): string
    {
        return \base64_encode(\hash_hmac('sha256', (string) $time, (string) $this->getClientSecret(), true));
    }

    /**
     * @see https://ding-doc.dingtalk.com/doc#/personnal/tmudue
     *
     * @throws Exceptions\BadRequestException
     */
    public function userFromCode(string $code): Contracts\UserInterface
    {
        $time = (int) \microtime(true) * 1000;

        $responseInstance = $this->getHttpClient()->post($this->getUserByCode, [
            'query' => [
                'accessKey' => $this->getClientId(),
                'timestamp' => $time,
                'signature' => $this->createSignature($time),
            ],
            'json' => ['tmp_auth_code' => $code],
        ]);
        $response = $this->fromJsonBody($responseInstance);

        if (0 != ($response['errcode'] ?? 1)) {
            throw new Exceptions\BadRequestException((string) $responseInstance->getBody());
        }

        if (empty($response['user_info'])) {
            throw new Exceptions\AuthorizeFailedException('Authorization failed: missing user_info in response', $response);
        }

        if (empty($response['user_info'][Contracts\ABNF_OPEN_ID])) {
            throw new Exceptions\AuthorizeFailedException('Authorization failed: missing open_id in user_info response', $response);
        }

        return new User([
            Contracts\ABNF_NAME => $response['user_info']['nick'] ?? null,
            Contracts\ABNF_NICKNAME => $response['user_info']['nick'] ?? null,
            Contracts\ABNF_ID => $response['user_info'][Contracts\ABNF_OPEN_ID],
        ]);
    }
}
