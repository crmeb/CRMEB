<?php
namespace Aws\BCMDashboards;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Billing and Cost Management Dashboards** service.
 * @method \Aws\Result createDashboard(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createDashboardAsync(array $args = [])
 * @method \Aws\Result createScheduledReport(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createScheduledReportAsync(array $args = [])
 * @method \Aws\Result deleteDashboard(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteDashboardAsync(array $args = [])
 * @method \Aws\Result deleteScheduledReport(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteScheduledReportAsync(array $args = [])
 * @method \Aws\Result executeScheduledReport(array $args = [])
 * @method \GuzzleHttp\Promise\Promise executeScheduledReportAsync(array $args = [])
 * @method \Aws\Result getDashboard(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getDashboardAsync(array $args = [])
 * @method \Aws\Result getResourcePolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getResourcePolicyAsync(array $args = [])
 * @method \Aws\Result getScheduledReport(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getScheduledReportAsync(array $args = [])
 * @method \Aws\Result listDashboards(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDashboardsAsync(array $args = [])
 * @method \Aws\Result listScheduledReports(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listScheduledReportsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateDashboard(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateDashboardAsync(array $args = [])
 * @method \Aws\Result updateScheduledReport(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateScheduledReportAsync(array $args = [])
 */
class BCMDashboardsClient extends AwsClient {}
