<?php
namespace Aws\Artifact;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Artifact** service.
 * @method \Aws\Result createComplianceInquiry(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createComplianceInquiryAsync(array $args = [])
 * @method \Aws\Result exportComplianceInquiry(array $args = [])
 * @method \GuzzleHttp\Promise\Promise exportComplianceInquiryAsync(array $args = [])
 * @method \Aws\Result getAccountSettings(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getAccountSettingsAsync(array $args = [])
 * @method \Aws\Result getComplianceInquiryMetadata(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getComplianceInquiryMetadataAsync(array $args = [])
 * @method \Aws\Result getReport(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getReportAsync(array $args = [])
 * @method \Aws\Result getReportMetadata(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getReportMetadataAsync(array $args = [])
 * @method \Aws\Result getTermForReport(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getTermForReportAsync(array $args = [])
 * @method \Aws\Result listComplianceInquiries(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listComplianceInquiriesAsync(array $args = [])
 * @method \Aws\Result listComplianceInquiryQueries(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listComplianceInquiryQueriesAsync(array $args = [])
 * @method \Aws\Result listCustomerAgreements(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listCustomerAgreementsAsync(array $args = [])
 * @method \Aws\Result listReportVersions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listReportVersionsAsync(array $args = [])
 * @method \Aws\Result listReports(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listReportsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result putAccountSettings(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putAccountSettingsAsync(array $args = [])
 * @method \Aws\Result putComplianceInquiryFeedback(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putComplianceInquiryFeedbackAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class ArtifactClient extends AwsClient {}
