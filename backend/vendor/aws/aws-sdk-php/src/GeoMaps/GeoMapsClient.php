<?php
namespace Aws\GeoMaps;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Location Service Maps V2** service.
 * @method \Aws\Result getGlyphs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getGlyphsAsync(array $args = [])
 * @method \Aws\Result getSprites(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getSpritesAsync(array $args = [])
 * @method \Aws\Result getStaticMap(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getStaticMapAsync(array $args = [])
 * @method \Aws\Result getStyleDescriptor(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getStyleDescriptorAsync(array $args = [])
 * @method \Aws\Result getTile(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getTileAsync(array $args = [])
 */
class GeoMapsClient extends AwsClient {}
