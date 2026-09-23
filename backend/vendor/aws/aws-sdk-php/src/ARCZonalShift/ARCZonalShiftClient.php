<?php
namespace Aws\ARCZonalShift;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS ARC - Zonal Shift** service.
 * @method \Aws\Result cancelPracticeRun(array $args = [])
 * @method \GuzzleHttp\Promise\Promise cancelPracticeRunAsync(array $args = [])
 * @method \Aws\Result cancelZonalShift(array $args = [])
 * @method \GuzzleHttp\Promise\Promise cancelZonalShiftAsync(array $args = [])
 * @method \Aws\Result createPracticeRunConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createPracticeRunConfigurationAsync(array $args = [])
 * @method \Aws\Result deletePracticeRunConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deletePracticeRunConfigurationAsync(array $args = [])
 * @method \Aws\Result getAutoshiftObserverNotificationStatus(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getAutoshiftObserverNotificationStatusAsync(array $args = [])
 * @method \Aws\Result getManagedResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getManagedResourceAsync(array $args = [])
 * @method \Aws\Result listAutoshifts(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listAutoshiftsAsync(array $args = [])
 * @method \Aws\Result listManagedResources(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listManagedResourcesAsync(array $args = [])
 * @method \Aws\Result listZonalShifts(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listZonalShiftsAsync(array $args = [])
 * @method \Aws\Result startPracticeRun(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startPracticeRunAsync(array $args = [])
 * @method \Aws\Result startZonalShift(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startZonalShiftAsync(array $args = [])
 * @method \Aws\Result updateAutoshiftObserverNotificationStatus(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateAutoshiftObserverNotificationStatusAsync(array $args = [])
 * @method \Aws\Result updatePracticeRunConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updatePracticeRunConfigurationAsync(array $args = [])
 * @method \Aws\Result updateZonalAutoshiftConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateZonalAutoshiftConfigurationAsync(array $args = [])
 * @method \Aws\Result updateZonalShift(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateZonalShiftAsync(array $args = [])
 */
class ARCZonalShiftClient extends AwsClient {}
