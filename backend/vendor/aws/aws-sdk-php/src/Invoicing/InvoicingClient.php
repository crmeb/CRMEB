<?php
namespace Aws\Invoicing;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Invoicing** service.
 * @method \Aws\Result batchGetInvoiceProfile(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchGetInvoiceProfileAsync(array $args = [])
 * @method \Aws\Result createInvoiceUnit(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createInvoiceUnitAsync(array $args = [])
 * @method \Aws\Result createProcurementPortalPreference(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createProcurementPortalPreferenceAsync(array $args = [])
 * @method \Aws\Result deleteInvoiceUnit(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteInvoiceUnitAsync(array $args = [])
 * @method \Aws\Result deleteProcurementPortalPreference(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteProcurementPortalPreferenceAsync(array $args = [])
 * @method \Aws\Result getInvoicePDF(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getInvoicePDFAsync(array $args = [])
 * @method \Aws\Result getInvoiceUnit(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getInvoiceUnitAsync(array $args = [])
 * @method \Aws\Result getProcurementPortalPreference(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getProcurementPortalPreferenceAsync(array $args = [])
 * @method \Aws\Result listInvoiceSummaries(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listInvoiceSummariesAsync(array $args = [])
 * @method \Aws\Result listInvoiceUnits(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listInvoiceUnitsAsync(array $args = [])
 * @method \Aws\Result listProcurementPortalPreferences(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listProcurementPortalPreferencesAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result putProcurementPortalPreference(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putProcurementPortalPreferenceAsync(array $args = [])
 * @method \Aws\Result sendProcurementPortalValidation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise sendProcurementPortalValidationAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateInvoiceUnit(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateInvoiceUnitAsync(array $args = [])
 * @method \Aws\Result updateProcurementPortalPreferenceStatus(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateProcurementPortalPreferenceStatusAsync(array $args = [])
 * @method \Aws\Result verifyProcurementPortalValidation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise verifyProcurementPortalValidationAsync(array $args = [])
 */
class InvoicingClient extends AwsClient {}
