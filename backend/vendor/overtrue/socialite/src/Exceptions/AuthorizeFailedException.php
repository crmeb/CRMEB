<?php

namespace Overtrue\Socialite\Exceptions;

use JetBrains\PhpStorm\Pure;

class AuthorizeFailedException extends Exception
{
    public array $body;

    #[Pure]
    public function __construct(string $message, mixed $body)
    {
        parent::__construct($message, -1);

        $this->body = (array) $body;
    }
}
