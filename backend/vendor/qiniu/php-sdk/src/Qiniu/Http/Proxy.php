<?php

namespace Qiniu\Http;

use Qiniu\Http\RequestOptions;

final class Proxy
{
    private $proxy;
    private $proxy_auth;
    private $proxy_user_password;

    public function __construct($proxy = null, $proxy_auth = null, $proxy_user_password = null)
    {
        $this->proxy = $proxy;
        $this->proxy_auth = $proxy_auth;
        $this->proxy_user_password = $proxy_user_password;
    }

    public function makeReqOpt()
    {
        $reqOpt = new RequestOptions();
        if ($this->proxy !== null) {
            $reqOpt->proxy = $this->proxy;
        }
        if ($this->proxy_auth !== null) {
            $reqOpt->proxy_auth = $this->proxy_auth;
        }
        if ($this->proxy_user_password !== null) {
            $reqOpt->proxy_user_password = $this->proxy_user_password;
        }
        return $reqOpt;
    }
}
