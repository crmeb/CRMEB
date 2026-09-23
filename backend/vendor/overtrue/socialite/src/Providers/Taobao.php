<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\User;

/**
 * @see https://open.taobao.com/doc.htm?docId=102635&docType=1&source=search [Taobao - OAuth 2.0 授权登录]
 */
class Taobao extends Base
{
    public const NAME = 'taobao';

    protected string $baseUrl = 'https://oauth.taobao.com';

    protected string $gatewayUrl = 'https://eco.taobao.com/router/rest';

    protected string $view = 'web';

    protected array $scopes = ['user_info'];

    public function withView(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/authorize');
    }

    #[ArrayShape([
        Contracts\RFC6749_ABNF_CLIENT_ID => 'null|string',
        Contracts\RFC6749_ABNF_REDIRECT_URI => 'null|string',
        'view' => 'string',
        Contracts\RFC6749_ABNF_RESPONSE_TYPE => 'string',
    ])]
    public function getCodeFields(): array
    {
        return [
            Contracts\RFC6749_ABNF_CLIENT_ID => $this->getClientId(),
            Contracts\RFC6749_ABNF_REDIRECT_URI => $this->redirectUrl,
            'view' => $this->view,
            Contracts\RFC6749_ABNF_RESPONSE_TYPE => Contracts\RFC6749_ABNF_CODE,
        ];
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/token';
    }

    #[ArrayShape([
        Contracts\RFC6749_ABNF_CLIENT_ID => 'null|string',
        Contracts\RFC6749_ABNF_CLIENT_SECRET => 'null|string',
        Contracts\RFC6749_ABNF_CODE => 'string',
        Contracts\RFC6749_ABNF_REDIRECT_URI => 'null|string',
        Contracts\RFC6749_ABNF_GRANT_TYPE => 'string',
        'view' => 'string',
    ])]
    protected function getTokenFields(string $code): array
    {
        return parent::getTokenFields($code) + [
            Contracts\RFC6749_ABNF_GRANT_TYPE => Contracts\RFC6749_ABNF_AUTHORATION_CODE,
            'view' => $this->view,
        ];
    }

    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'query' => $this->getTokenFields($code),
        ]);

        return $this->normalizeAccessTokenResponse($response->getBody());
    }

    protected function getUserByToken(string $token, ?array $query = []): array
    {
        $response = $this->getHttpClient()->post($this->getUserInfoUrl($this->gatewayUrl, $token));

        return $this->fromJsonBody($response);
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User([
            Contracts\ABNF_ID => $user[Contracts\ABNF_OPEN_ID] ?? null,
            Contracts\ABNF_NICKNAME => $user['nick'] ?? null,
            Contracts\ABNF_NAME => $user['nick'] ?? null,
            Contracts\ABNF_AVATAR => $user[Contracts\ABNF_AVATAR] ?? null,
            Contracts\ABNF_EMAIL => $user[Contracts\ABNF_EMAIL] ?? null,
        ]);
    }

    protected function generateSign(array $params): string
    {
        \ksort($params);

        $stringToBeSigned = $this->getConfig()->get(Contracts\RFC6749_ABNF_CLIENT_SECRET);

        foreach ($params as $k => $v) {
            if (! \is_array($v) && ! \str_starts_with($v, '@')) {
                $stringToBeSigned .= "$k$v";
            }
        }

        $stringToBeSigned .= $this->getConfig()->get(Contracts\RFC6749_ABNF_CLIENT_SECRET);

        return \strtoupper(\md5($stringToBeSigned));
    }

    protected function getPublicFields(string $token, array $apiFields = []): array
    {
        $fields = [
            'app_key' => $this->getClientId(),
            'sign_method' => 'md5',
            'session' => $token,
            'timestamp' => (new \DateTime('now', new \DateTimeZone('Asia/Shanghai')))->format('Y-m-d H:i:s'),
            'v' => '2.0',
            'format' => 'json',
        ];

        $fields = \array_merge($apiFields, $fields);
        $fields['sign'] = $this->generateSign($fields);

        return $fields;
    }

    protected function getUserInfoUrl(string $url, string $token): string
    {
        $apiFields = ['method' => 'taobao.miniapp.userInfo.get'];

        $query = \http_build_query($this->getPublicFields($token, $apiFields), '', '&', $this->encodingType);

        return $url.'?'.$query;
    }
}
