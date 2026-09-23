<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\User;

/**
 * @see https://open.douyin.com/platform/resource/docs/openapi/account-permission/toutiao-get-permission-code
 */
class TouTiao extends DouYin
{
    public const NAME = 'toutiao';

    protected string $baseUrl = 'https://open.snssdk.com';

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/oauth/authorize/');
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User([
            Contracts\ABNF_ID => $user[Contracts\ABNF_OPEN_ID] ?? null,
            Contracts\ABNF_NAME => $user[Contracts\ABNF_NICKNAME] ?? null,
            Contracts\ABNF_NICKNAME => $user[Contracts\ABNF_NICKNAME] ?? null,
            Contracts\ABNF_AVATAR => $user[Contracts\ABNF_AVATAR] ?? null,
        ]);
    }
}
