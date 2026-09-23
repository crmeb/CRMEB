<?php
namespace Aws\Route53Profiles;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Route 53 Profiles** service.
 * @method \Aws\Result associateProfile(array $args = [])
 * @method \GuzzleHttp\Promise\Promise associateProfileAsync(array $args = [])
 * @method \Aws\Result associateResourceToProfile(array $args = [])
 * @method \GuzzleHttp\Promise\Promise associateResourceToProfileAsync(array $args = [])
 * @method \Aws\Result createProfile(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createProfileAsync(array $args = [])
 * @method \Aws\Result deleteProfile(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteProfileAsync(array $args = [])
 * @method \Aws\Result disassociateProfile(array $args = [])
 * @method \GuzzleHttp\Promise\Promise disassociateProfileAsync(array $args = [])
 * @method \Aws\Result disassociateResourceFromProfile(array $args = [])
 * @method \GuzzleHttp\Promise\Promise disassociateResourceFromProfileAsync(array $args = [])
 * @method \Aws\Result getProfile(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getProfileAsync(array $args = [])
 * @method \Aws\Result getProfileAssociation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getProfileAssociationAsync(array $args = [])
 * @method \Aws\Result getProfileResourceAssociation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getProfileResourceAssociationAsync(array $args = [])
 * @method \Aws\Result listProfileAssociations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listProfileAssociationsAsync(array $args = [])
 * @method \Aws\Result listProfileResourceAssociations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listProfileResourceAssociationsAsync(array $args = [])
 * @method \Aws\Result listProfiles(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listProfilesAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateProfileResourceAssociation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateProfileResourceAssociationAsync(array $args = [])
 */
class Route53ProfilesClient extends AwsClient {}
