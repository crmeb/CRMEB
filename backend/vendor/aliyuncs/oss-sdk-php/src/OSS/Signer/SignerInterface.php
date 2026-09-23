<?php
namespace OSS\Signer;

use OSS\Http\RequestCore;
use OSS\Credentials\Credentials;

interface SignerInterface
{
    public function sign(RequestCore $request, Credentials $credentials, array &$options);

    public function presign(RequestCore $request, Credentials $credentials, array &$options);
}