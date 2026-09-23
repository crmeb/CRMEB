<?php
namespace Aws\MarketplaceDiscovery;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Marketplace Discovery** service.
 * @method \Aws\Result getListing(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getListingAsync(array $args = [])
 * @method \Aws\Result getOffer(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getOfferAsync(array $args = [])
 * @method \Aws\Result getOfferSet(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getOfferSetAsync(array $args = [])
 * @method \Aws\Result getOfferTerms(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getOfferTermsAsync(array $args = [])
 * @method \Aws\Result getProduct(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getProductAsync(array $args = [])
 * @method \Aws\Result listFulfillmentOptions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listFulfillmentOptionsAsync(array $args = [])
 * @method \Aws\Result listPurchaseOptions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listPurchaseOptionsAsync(array $args = [])
 * @method \Aws\Result searchFacets(array $args = [])
 * @method \GuzzleHttp\Promise\Promise searchFacetsAsync(array $args = [])
 * @method \Aws\Result searchListings(array $args = [])
 * @method \GuzzleHttp\Promise\Promise searchListingsAsync(array $args = [])
 */
class MarketplaceDiscoveryClient extends AwsClient {}
