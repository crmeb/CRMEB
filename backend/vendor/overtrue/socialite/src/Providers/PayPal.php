<?php

namespace Overtrue\Socialite\Providers;

use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\Exceptions\AuthorizeFailedException;
use Overtrue\Socialite\User;

/**
 * Class PayPal
 *
 * @author zhiqiang
 *
 * @see https://developer.paypal.com/docs/log-in-with-paypal/
 */
class PayPal extends Base
{
    public const NAME = 'paypal';

    protected string $scopeSeparator = ' ';

    /**
     * @var string|null
     *                  code or id_token
     */
    protected ?string $responseType = Contracts\RFC6749_ABNF_CODE;

    protected string $flowEntry = 'static';

    protected string $authUrl = 'https://www.paypal.com/signin/authorize';

    protected string $tokenURL = 'https://api-m.paypal.com/v1/oauth2/token';

    protected string $userinfoURL = 'https://api.paypal.com/v1/identity/openidconnect/userinfo?schema=openid';

    protected array $scopes = [
        'openid', 'profile', 'email', 'address',
    ];

    protected bool $sandbox = true;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->sandbox = (bool) $this->config->get('sandbox', false);
        if ($this->sandbox) {
            $this->authUrl = 'https://www.sandbox.paypal.com/signin/authorize';
            $this->tokenURL = 'https://api-m.sandbox.paypal.com/v1/oauth2/token';
            $this->userinfoURL = 'https://api-m.sandbox.paypal.com/v1/identity/openidconnect/userinfo?schema=openid';
        }
    }

    /**
     * @return $this
     *
     * @see https://developer.paypal.com/docs/log-in-with-paypal/integrate/generate-button/
     */
    public function withResponseType(?string $responseType)
    {
        $this->responseType = $responseType;

        return $this;
    }

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->authUrl);
    }

    protected function getCodeFields(): array
    {
        $fields = \array_merge(
            [
                'flowEntry' => $this->flowEntry,
                Contracts\RFC6749_ABNF_CLIENT_ID => $this->getClientId(),
                Contracts\RFC6749_ABNF_RESPONSE_TYPE => $this->responseType,
                Contracts\RFC6749_ABNF_SCOPE => $this->formatScopes($this->scopes, $this->scopeSeparator),
                Contracts\RFC6749_ABNF_REDIRECT_URI => $this->redirectUrl,
            ],
            $this->parameters
        );

        if ($this->state) {
            $fields[Contracts\RFC6749_ABNF_STATE] = $this->state;
        }

        return $fields;
    }

    protected function getTokenUrl(): string
    {
        return $this->tokenURL;
    }

    /**
     * @throws GuzzleException
     * @throws AuthorizeFailedException
     *
     * @see https://developer.paypal.com/docs/log-in-with-paypal/integrate/#link-getaccesstoken
     */
    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->post(
            $this->getTokenUrl(),
            [
                'form_params' => [
                    Contracts\RFC6749_ABNF_GRANT_TYPE => Contracts\RFC6749_ABNF_CLIENT_CREDENTIALS,
                    Contracts\RFC6749_ABNF_CODE => $code,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic '.\base64_encode(\sprintf('%s:%s', $this->getClientId(), $this->getClientSecret())),
                ],
            ]
        );

        return $this->normalizeAccessTokenResponse((string) $response->getBody());
    }

    /**
     * @throws GuzzleException
     *
     * @see https://developer.paypal.com/docs/api/identity/v1/#userinfo_get
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get(
            $this->userinfoURL,
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Bearer '.$token,
                ],
            ]
        );

        return $this->fromJsonBody($response);
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        $user[Contracts\ABNF_ID] = $user['user_id'] ?? null;
        $user[Contracts\ABNF_NICKNAME] = $user['given_name'] ?? $user['family_name'] ?? $user['middle_name'] ?? null;
        $user[Contracts\ABNF_NAME] = $user['name'] ?? '';
        $user[Contracts\ABNF_EMAIL] = $user[Contracts\ABNF_EMAIL] ?? null;
        $user[Contracts\ABNF_AVATAR] = $user['picture'] ?? null;

        return new User($user);
    }
}
