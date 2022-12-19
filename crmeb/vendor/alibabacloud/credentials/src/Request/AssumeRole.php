<?php

namespace AlibabaCloud\Credentials\Request;

use AlibabaCloud\Credentials\Providers\Provider;
use AlibabaCloud\Credentials\RamRoleArnCredential;
use AlibabaCloud\Credentials\Signature\ShaHmac1Signature;

/**
 * Retrieving assume role credentials.
 */
class AssumeRole extends Request
{
    /**
     * AssumeRole constructor.
     *
     * @param RamRoleArnCredential $arnCredential
     */
    public function __construct(RamRoleArnCredential $arnCredential)
    {
        parent::__construct();
        $this->signature                           = new ShaHmac1Signature();
        $this->credential                          = $arnCredential;
        $this->uri                                 = $this->uri->withHost('sts.aliyuncs.com');
        $this->options['verify']                   = false;
        $this->options['query']['RoleArn']         = $arnCredential->getRoleArn();
        $this->options['query']['RoleSessionName'] = $arnCredential->getRoleSessionName();
        $this->options['query']['DurationSeconds'] = Provider::DURATION_SECONDS;
        $this->options['query']['AccessKeyId']     = $this->credential->getOriginalAccessKeyId();
        $this->options['query']['Version']         = '2015-04-01';
        $this->options['query']['Action']          = 'AssumeRole';
        $this->options['query']['RegionId']        = 'cn-hangzhou';
        if ($arnCredential->getPolicy()) {
            $this->options['query']['Policy'] = $arnCredential->getPolicy();
        }
    }
}
