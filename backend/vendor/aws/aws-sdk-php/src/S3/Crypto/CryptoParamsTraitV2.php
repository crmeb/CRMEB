<?php
namespace Aws\S3\Crypto;

use Aws\Crypto\MaterialsProviderInterfaceV2;

trait CryptoParamsTraitV2
{
    use CryptoParamsTrait;

    protected function getMaterialsProvider(array $args)
    {
        if ($args['@MaterialsProvider'] instanceof MaterialsProviderInterfaceV2) {
            return $args['@MaterialsProvider'];
        }

        throw new \InvalidArgumentException('An instance of MaterialsProviderInterfaceV2'
            . ' must be passed in the "MaterialsProvider" field.');
    }

    protected function getKeyCommitmentPolicy(array $args): string
    {
        if (empty($args['@CommitmentPolicy'])) {
            throw new \InvalidArgumentException('A commitment policy must be'
                . ' specified in the CommitmentPolicy field.');
        }

        if (!S3EncryptionClientV2::isSupportedKeyCommitmentPolicy($args['@CommitmentPolicy'])) {
            throw new \InvalidArgumentException('The CommitmentPolicy requested is not'
                . ' supported by the SDK.');
        }

        return $args['@CommitmentPolicy'];
    }
}
