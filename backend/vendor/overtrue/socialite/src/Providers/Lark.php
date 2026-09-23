<?php

namespace Overtrue\Socialite\Providers;

/**
 * @see https://open.larksuite.com/document/ukTMukTMukTM/uITNz4iM1MjLyUzM
 */
class Lark extends FeiShu
{
    public const NAME = 'lark';

    protected string $baseUrl = 'https://open.larksuite.com/open-apis';
}
