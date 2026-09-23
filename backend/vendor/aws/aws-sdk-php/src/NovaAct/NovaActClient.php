<?php
namespace Aws\NovaAct;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Nova Act Service** service.
 * @method \Aws\Result createAct(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createActAsync(array $args = [])
 * @method \Aws\Result createSession(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createSessionAsync(array $args = [])
 * @method \Aws\Result createWorkflowDefinition(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createWorkflowDefinitionAsync(array $args = [])
 * @method \Aws\Result createWorkflowRun(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createWorkflowRunAsync(array $args = [])
 * @method \Aws\Result deleteWorkflowDefinition(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteWorkflowDefinitionAsync(array $args = [])
 * @method \Aws\Result deleteWorkflowRun(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteWorkflowRunAsync(array $args = [])
 * @method \Aws\Result getWorkflowDefinition(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getWorkflowDefinitionAsync(array $args = [])
 * @method \Aws\Result getWorkflowRun(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getWorkflowRunAsync(array $args = [])
 * @method \Aws\Result invokeActStep(array $args = [])
 * @method \GuzzleHttp\Promise\Promise invokeActStepAsync(array $args = [])
 * @method \Aws\Result listActs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listActsAsync(array $args = [])
 * @method \Aws\Result listModels(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listModelsAsync(array $args = [])
 * @method \Aws\Result listSessions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSessionsAsync(array $args = [])
 * @method \Aws\Result listWorkflowDefinitions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listWorkflowDefinitionsAsync(array $args = [])
 * @method \Aws\Result listWorkflowRuns(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listWorkflowRunsAsync(array $args = [])
 * @method \Aws\Result updateAct(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateActAsync(array $args = [])
 * @method \Aws\Result updateWorkflowRun(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateWorkflowRunAsync(array $args = [])
 */
class NovaActClient extends AwsClient {}
