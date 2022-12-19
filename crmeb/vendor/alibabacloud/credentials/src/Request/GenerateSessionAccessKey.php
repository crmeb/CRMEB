<?php

namespace AlibabaCloud\Credentials\Request;

use AlibabaCloud\Credentials\Providers\Provider;
use AlibabaCloud\Credentials\RsaKeyPairCredential;
use AlibabaCloud\Credentials\Signature\ShaHmac256WithRsaSignature;

/**
 * Use the RSA key pair to complete the authentication (supported only on Japanese site)
 */
class GenerateSessionAccessKey extends Request
{
    /**
     * GenerateSessionAccessKey constructor.
     *
     * @param RsaKeyPairCredential $credential
     */
    public function __construct(RsaKeyPairCredential $credential)
    {
        parent::__construct();
        $this->signature                           = new ShaHmac256WithRsaSignature();
        $this->credential                          = $credential;
        $this->uri                                 = $this->uri->withHost('sts.ap-northeast-1.aliyuncs.com');
        $this->options['verify']                   = false;
        $this->options['query']['Version']         = '2015-04-01';
        $this->options['query']['Action']          = 'GenerateSessionAccessKey';
        $this->options['query']['RegionId']        = 'cn-hangzhou';
        $this->options['query']['AccessKeyId']     = $credential->getPublicKeyId();
        $this->options['query']['PublicKeyId']     = $credential->getPublicKeyId();
        $this->options['query']['DurationSeconds'] = Provider::DURATION_SECONDS;
    }
}
