<?php

namespace Overtrue\Socialite\Providers;

use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\Exceptions\BadRequestException;
use Overtrue\Socialite\Exceptions\InvalidArgumentException;
use Overtrue\Socialite\User;

class Coding extends Base
{
    public const NAME = 'coding';

    // example: https://{your-team}.coding.net
    protected string $teamUrl;

    protected array $scopes = ['user', 'user:email'];

    protected string $scopeSeparator = ',';

    public function __construct(array $config)
    {
        parent::__construct($config);

        // https://{your-team}.coding.net
        $teamUrl = $this->config->get('team_url');

        if (! $teamUrl) {
            throw new InvalidArgumentException('Missing required config [team_url]');
        }

        // validate team_url
        if (filter_var($teamUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Invalid team_url');
        }

        $this->teamUrl = rtrim($teamUrl, '/');
    }

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase("$this->teamUrl/oauth_authorize.html");
    }

    protected function getTokenUrl(): string
    {
        return "$this->teamUrl/api/oauth/access_token";
    }

    #[ArrayShape([
        Contracts\RFC6749_ABNF_CLIENT_ID => 'null|string',
        Contracts\RFC6749_ABNF_CLIENT_SECRET => 'null|string',
        Contracts\RFC6749_ABNF_CODE => 'string',
        Contracts\RFC6749_ABNF_GRANT_TYPE => 'null|string',
    ])]
    protected function getTokenFields(string $code): array
    {
        return [
            Contracts\RFC6749_ABNF_CLIENT_ID => $this->getClientId(),
            Contracts\RFC6749_ABNF_CLIENT_SECRET => $this->getClientSecret(),
            Contracts\RFC6749_ABNF_CODE => $code,
            Contracts\RFC6749_ABNF_GRANT_TYPE => Contracts\RFC6749_ABNF_AUTHORATION_CODE,
        ];
    }

    /**
     * @throws GuzzleException
     * @throws BadRequestException
     */
    protected function getUserByToken(string $token): array
    {
        $responseInstance = $this->getHttpClient()->get(
            "$this->teamUrl/api/me",
            [
                'query' => [
                    'access_token' => $token,
                ],
            ]
        );

        $response = $this->fromJsonBody($responseInstance);

        if (empty($response[Contracts\ABNF_ID])) {
            throw new BadRequestException((string) $responseInstance->getBody());
        }

        return $response;
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User([
            Contracts\ABNF_ID => $user[Contracts\ABNF_ID] ?? null,
            Contracts\ABNF_NICKNAME => $user[Contracts\ABNF_NAME] ?? null,
            Contracts\ABNF_NAME => $user[Contracts\ABNF_NAME] ?? null,
            Contracts\ABNF_EMAIL => $user[Contracts\ABNF_EMAIL] ?? null,
            Contracts\ABNF_AVATAR => $user[Contracts\ABNF_AVATAR] ?? null,
        ]);
    }
}
