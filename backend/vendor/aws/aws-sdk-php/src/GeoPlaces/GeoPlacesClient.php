<?php
namespace Aws\GeoPlaces;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Location Service Places V2** service.
 * @method \Aws\Result autocomplete(array $args = [])
 * @method \GuzzleHttp\Promise\Promise autocompleteAsync(array $args = [])
 * @method \Aws\Result geocode(array $args = [])
 * @method \GuzzleHttp\Promise\Promise geocodeAsync(array $args = [])
 * @method \Aws\Result getPlace(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getPlaceAsync(array $args = [])
 * @method \Aws\Result reverseGeocode(array $args = [])
 * @method \GuzzleHttp\Promise\Promise reverseGeocodeAsync(array $args = [])
 * @method \Aws\Result searchNearby(array $args = [])
 * @method \GuzzleHttp\Promise\Promise searchNearbyAsync(array $args = [])
 * @method \Aws\Result searchText(array $args = [])
 * @method \GuzzleHttp\Promise\Promise searchTextAsync(array $args = [])
 * @method \Aws\Result suggest(array $args = [])
 * @method \GuzzleHttp\Promise\Promise suggestAsync(array $args = [])
 */
class GeoPlacesClient extends AwsClient {}
