<?php

namespace Overtrue\Socialite\Contracts;

const ABNF_ID = 'id';
const ABNF_NAME = 'name';
const ABNF_NICKNAME = 'nickname';
const ABNF_EMAIL = 'email';
const ABNF_AVATAR = 'avatar';

interface UserInterface
{
    public function getId(): mixed;

    public function getNickname(): ?string;

    public function getName(): ?string;

    public function getEmail(): ?string;

    public function getAvatar(): ?string;

    public function getAccessToken(): ?string;

    public function getRefreshToken(): ?string;

    public function getExpiresIn(): ?int;

    public function getProvider(): ProviderInterface;

    public function setRefreshToken(?string $refreshToken): self;

    public function setExpiresIn(int $expiresIn): self;

    public function setTokenResponse(array $response): self;

    public function getTokenResponse(): mixed;

    public function setProvider(ProviderInterface $provider): self;

    public function getRaw(): array;

    public function setRaw(array $user): self;

    public function setAccessToken(string $token): self;
}
