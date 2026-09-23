<?php

namespace Overtrue\Socialite\Contracts;

/** @see https://datatracker.ietf.org/doc/html/rfc6749#appendix-A.1 */
const RFC6749_ABNF_CLIENT_ID = 'client_id';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#appendix-A.2 */
const RFC6749_ABNF_CLIENT_SECRET = 'client_secret';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#appendix-A.3 */
const RFC6749_ABNF_RESPONSE_TYPE = 'response_type';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#appendix-A.4 */
const RFC6749_ABNF_SCOPE = 'scope';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#appendix-A.5 */
const RFC6749_ABNF_STATE = 'state';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#appendix-A.6 */
const RFC6749_ABNF_REDIRECT_URI = 'redirect_uri';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#appendix-A.7 */
const RFC6749_ABNF_ERROR = 'error';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#appendix-A.8 */
const RFC6749_ABNF_ERROR_DESCRIPTION = 'error_description';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#appendix-A.9 */
const RFC6749_ABNF_ERROR_URI = 'error_uri';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#appendix-A.10 */
const RFC6749_ABNF_GRANT_TYPE = 'grant_type';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#appendix-A.11 */
const RFC6749_ABNF_CODE = 'code';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#appendix-A.12 */
const RFC6749_ABNF_ACCESS_TOKEN = 'access_token';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#appendix-A.13 */
const RFC6749_ABNF_TOKEN_TYPE = 'token_type';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#appendix-A.14 */
const RFC6749_ABNF_EXPIRES_IN = 'expires_in';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#appendix-A.15 */
const RFC6749_ABNF_USERNAME = 'username';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#appendix-A.16 */
const RFC6749_ABNF_PASSWORD = 'password';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#appendix-A.17 */
const RFC6749_ABNF_REFRESH_TOKEN = 'refresh_token';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.3 */
const RFC6749_ABNF_AUTHORATION_CODE = 'authorization_code';
/** @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.4.2 */
const RFC6749_ABNF_CLIENT_CREDENTIALS = 'client_credentials';

interface ProviderInterface
{
    public function redirect(?string $redirectUrl = null): string;

    public function userFromCode(string $code): UserInterface;

    public function userFromToken(string $token): UserInterface;

    public function withRedirectUrl(string $redirectUrl): self;

    public function withState(string $state): self;

    /**
     * @param  string[]  $scopes
     */
    public function scopes(array $scopes): self;

    public function with(array $parameters): self;

    public function withScopeSeparator(string $scopeSeparator): self;

    public function getClientId(): ?string;

    public function getClientSecret(): ?string;
}
