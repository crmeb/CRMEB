<?php

namespace AlibabaCloud\Endpoint;

/**
 * Get endpoint.
 */
class Endpoint
{
    const ENDPOINT_TYPE_REGIONAL = 'regional';
    const ENDPOINT_TYPE_CENTRAL  = 'central';

    const REGIONAL_RULES = '<product><suffix><network>.<region_id>.aliyuncs.com';
    const CENTRAL_RULES  = '<product><suffix><network>.aliyuncs.com';

    /**
     * @param string $product      required
     * @param string $regionId     optional It will be required when endpoint type is 'regional'
     * @param string $endpointType optional regional|central
     * @param string $network      optional
     * @param string $suffix       optional
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function getEndpointRules($product, $regionId, $endpointType = '', $network = '', $suffix = '')
    {
        if (empty($product)) {
            throw new \InvalidArgumentException('Product name cannot be empty.');
        }
        $endpoint = self::REGIONAL_RULES;
        if (self::ENDPOINT_TYPE_REGIONAL === $endpointType) {
            if (empty($regionId)) {
                throw new \InvalidArgumentException('RegionId is empty, please set a valid RegionId');
            }
            $endpoint = self::render($endpoint, 'region_id', strtolower($regionId));
        } elseif (self::ENDPOINT_TYPE_CENTRAL === $endpointType) {
            $endpoint = self::CENTRAL_RULES;
        } else {
            throw new \InvalidArgumentException('Invalid EndpointType');
        }
        if (!empty($network) && 'public' !== $network) {
            $endpoint = self::render($endpoint, 'network', '-' . $network);
        } else {
            $endpoint = self::render($endpoint, 'network', '');
        }
        if (!empty($suffix)) {
            $endpoint = self::render($endpoint, 'suffix', '-' . $suffix);
        } else {
            $endpoint = self::render($endpoint, 'suffix', '');
        }

        return self::render($endpoint, 'product', strtolower($product));
    }

    private static function render($str, $tag, $replace)
    {
        return str_replace('<' . $tag . '>', $replace, $str);
    }
}
