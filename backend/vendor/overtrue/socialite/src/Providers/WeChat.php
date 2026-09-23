<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\Exceptions;
use Overtrue\Socialite\User;
use Psr\Http\Message\ResponseInterface;

/**
 * @see http://mp.weixin.qq.com/wiki/9/01f711493b5a02f24b04365ac5d8fd95.html [WeChat - 公众平台OAuth文档]
 * @see https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419316505&token=&lang=zh_CN
 *      [网站应用微信登录开发指南]
 */
class WeChat extends Base
{
    public const NAME = 'wechat';

    protected string $baseUrl = 'https://api.weixin.qq.com/sns';

    protected array $scopes = ['snsapi_login'];

    protected bool $withCountryCode = false;

    protected ?array $component = null;

    protected ?string $openid = null;

    public function __construct(array $config)
    {
        parent::__construct($config);

        if ($this->getConfig()->has('component')) {
            $this->prepareForComponent((array) $this->getConfig()->get('component'));
        }
    }

    public function withOpenid(string $openid): self
    {
        $this->openid = $openid;

        return $this;
    }

    public function withCountryCode(): self
    {
        $this->withCountryCode = true;

        return $this;
    }

    public function tokenFromCode(string $code): array
    {
        $response = $this->getTokenFromCode($code);

        return $this->normalizeAccessTokenResponse($response->getBody());
    }

    /**
     * @param  array<string,string>  $componentConfig  [Contracts\ABNF_ID => xxx, Contracts\ABNF_TOKEN => xxx]
     */
    public function withComponent(array $componentConfig): self
    {
        $this->prepareForComponent($componentConfig);

        return $this;
    }

    public function getComponent(): ?array
    {
        return $this->component;
    }

    protected function getAuthUrl(): string
    {
        $path = 'oauth2/authorize';

        if (\in_array('snsapi_login', $this->scopes)) {
            $path = 'qrconnect';
        }

        return $this->buildAuthUrlFromBase("https://open.weixin.qq.com/connect/{$path}");
    }

    protected function buildAuthUrlFromBase(string $url): string
    {
        $query = \http_build_query($this->getCodeFields(), '', '&', $this->encodingType);

        return $url.'?'.$query.'#wechat_redirect';
    }

    protected function getCodeFields(): array
    {
        if (! empty($this->component)) {
            $this->with(\array_merge($this->parameters, ['component_appid' => $this->component[Contracts\ABNF_ID]]));
        }

        return \array_merge([
            'appid' => $this->getClientId(),
            Contracts\RFC6749_ABNF_REDIRECT_URI => $this->redirectUrl,
            Contracts\RFC6749_ABNF_RESPONSE_TYPE => Contracts\RFC6749_ABNF_CODE,
            Contracts\RFC6749_ABNF_SCOPE => $this->formatScopes($this->scopes, $this->scopeSeparator),
            Contracts\RFC6749_ABNF_STATE => $this->state ?: \md5(\uniqid(Contracts\RFC6749_ABNF_STATE, true)),
            'connect_redirect' => 1,
        ], $this->parameters);
    }

    protected function getTokenUrl(): string
    {
        return \sprintf($this->baseUrl.'/oauth2%s/access_token', empty($this->component) ? '' : '/component');
    }

    public function userFromCode(string $code): Contracts\UserInterface
    {
        if (\in_array('snsapi_base', $this->scopes)) {
            return $this->getSnsapiBaseUserFromCode($code);
        }

        $token = $this->tokenFromCode($code);

        if (empty($token['openid'])) {
            throw new Exceptions\AuthorizeFailedException('Authorization failed: missing openid in token response', $token);
        }

        $this->withOpenid($token['openid']);

        $user = $this->userFromToken($token[$this->accessTokenKey]);

        return $user->setRefreshToken($token[Contracts\RFC6749_ABNF_REFRESH_TOKEN])
            ->setExpiresIn($token[Contracts\RFC6749_ABNF_EXPIRES_IN])
            ->setTokenResponse($token);
    }

    protected function getSnsapiBaseUserFromCode(string $code): Contracts\UserInterface
    {
        $token = $this->fromJsonBody($this->getTokenFromCode($code));

        if (empty($token['openid'])) {
            throw new Exceptions\AuthorizeFailedException('Authorization failed: missing openid in token response', $token);
        }

        if (empty($token[$this->accessTokenKey])) {
            throw new Exceptions\AuthorizeFailedException('Authorization failed: missing access_token in token response', $token);
        }

        $user = [
            'openid' => $token['openid'],
        ];
        if (isset($token['unionid'])) {
            $user['unionid'] = $token['unionid'];
        }

        return $this->mapUserToObject($token)->setProvider($this)->setRaw($user)->setAccessToken($token[$this->accessTokenKey]);
    }

    protected function getUserByToken(string $token): array
    {
        $language = $this->withCountryCode ? null : (isset($this->parameters['lang']) ? $this->parameters['lang'] : 'zh_CN');

        $response = $this->getHttpClient()->get($this->baseUrl.'/userinfo', [
            'query' => \array_filter([
                Contracts\RFC6749_ABNF_ACCESS_TOKEN => $token,
                'openid' => $this->openid,
                'lang' => $language,
            ]),
        ]);

        $response = $this->fromJsonBody($response);

        if (! empty($response['errcode'])) {
            throw new Exceptions\AuthorizeFailedException((string) ($response['errmsg'] ?? ''), $response);
        }

        return $response;
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User([
            Contracts\ABNF_ID => $user['openid'] ?? null,
            Contracts\ABNF_NAME => $user[Contracts\ABNF_NICKNAME] ?? null,
            Contracts\ABNF_NICKNAME => $user[Contracts\ABNF_NICKNAME] ?? null,
            Contracts\ABNF_AVATAR => $user['headimgurl'] ?? null,
            Contracts\ABNF_EMAIL => null,
        ]);
    }

    protected function getTokenFields(string $code): array
    {
        return empty($this->component) ? [
            'appid' => $this->getClientId(),
            'secret' => $this->getClientSecret(),
            Contracts\RFC6749_ABNF_CODE => $code,
            Contracts\RFC6749_ABNF_GRANT_TYPE => Contracts\RFC6749_ABNF_AUTHORATION_CODE,
        ] : [
            'appid' => $this->getClientId(),
            'component_appid' => $this->component[Contracts\ABNF_ID],
            'component_access_token' => $this->component[Contracts\ABNF_TOKEN],
            Contracts\RFC6749_ABNF_CODE => $code,
            Contracts\RFC6749_ABNF_GRANT_TYPE => Contracts\RFC6749_ABNF_AUTHORATION_CODE,
        ];
    }

    protected function getTokenFromCode(string $code): ResponseInterface
    {
        return $this->getHttpClient()->get($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            'query' => $this->getTokenFields($code),
        ]);
    }

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    protected function prepareForComponent(array $component): void
    {
        $config = [];
        foreach ($component as $key => $value) {
            if (\is_callable($value)) {
                $value = \call_user_func($value, $this);
            }

            switch ($key) {
                case Contracts\ABNF_ID:
                case Contracts\ABNF_APP_ID:
                case 'component_app_id':
                    $config[Contracts\ABNF_ID] = $value;
                    break;
                case Contracts\ABNF_TOKEN:
                case Contracts\RFC6749_ABNF_ACCESS_TOKEN:
                case 'app_token':
                case 'component_access_token':
                    $config[Contracts\ABNF_TOKEN] = $value;
                    break;
            }
        }

        if (\count($config) !== 2) {
            throw new Exceptions\InvalidArgumentException('Please check your config arguments were available.');
        }

        if (\count($this->scopes) === 1 && \in_array('snsapi_login', $this->scopes)) {
            $this->scopes = ['snsapi_base'];
        }

        $this->component = $config;
    }
}
