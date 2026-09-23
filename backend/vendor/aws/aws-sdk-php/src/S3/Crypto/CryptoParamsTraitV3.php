<?php
namespace Aws\S3\Crypto;

use Aws\Crypto\MaterialsProviderInterfaceV3;

trait CryptoParamsTraitV3
{
    use CryptoParamsTrait;

    protected function getMaterialsProvider(array $args): MaterialsProviderInterfaceV3
    {
        if ($args['@MaterialsProvider'] instanceof MaterialsProviderInterfaceV3) {
            return $args['@MaterialsProvider'];
        }

        throw new \InvalidArgumentException('An instance of MaterialsProviderInterfaceV3'
            . ' must be passed in the "MaterialsProvider" field.');
    }

    protected function getKeyCommitmentPolicy(array $args): string
    {
        if (empty($args['@CommitmentPolicy'])) {
            throw new \InvalidArgumentException('A commitment policy must be'
                . ' specified in the CommitmentPolicy field.');
        }

        if (!S3EncryptionClientV3::isSupportedKeyCommitmentPolicy($args['@CommitmentPolicy'])) {
            throw new \InvalidArgumentException('The CommitmentPolicy requested is not'
                . ' supported by the SDK.');
        }

        return $args['@CommitmentPolicy'];
    }
}
