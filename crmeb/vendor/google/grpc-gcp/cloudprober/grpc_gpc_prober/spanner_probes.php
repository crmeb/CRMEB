<?php

require('../vendor/autoload.php');

$_DATABASE = 'projects/grpc-prober-testing/instances/test-instance/databases/test-db';
$_TEST_USERNAME = 'test_username';

function hardAssert($value, $error_message)
{
    if (!$value) {
        echo $error_message."\n";
        exit(1);
    }
}
function hardAssertIfStatusOk($status)
{
    if ($status->code !== Grpc\STATUS_OK) {
        echo "Call did not complete successfully. Status object:\n";
        var_dump($status);
        exit(1);
    }
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

/*
Probes to test session related grpc call from Spanner stub.

  Includes tests against CreateSession, GetSession, ListSessions, and
  DeleteSession of Spanner stub.

  Args:
    stub: An object of SpannerStub.
    metrics: A list of metrics.

*/

function sessionManagement($client, &$metrics){
	global $_DATABASE;

	$createSessionRequest = new Google\Cloud\Spanner\V1\CreateSessionRequest();
	$createSessionRequest->setDatabase($_DATABASE);
	#Create Session test
	#Create
	$time_start = microtime_float();
	list($session, $status) = $client->CreateSession($createSessionRequest)->wait();

	hardAssertIfStatusOk($status);
	hardAssert($session !== null, 'Call completed with a null response');

	$lantency =  (microtime_float()- $time_start) * 1000;
	$metrics['create_session_latency_ms'] = $lantency;

	#Get Session
	$getSessionRequest = new Google\Cloud\Spanner\V1\GetSessionRequest();
	$getSessionRequest->setName($session->getName());
	$time_start = microtime_float();
	$response = $client->GetSession($getSessionRequest);
	$response->wait();
	$lantency =  (microtime_float() - $time_start) * 1000;
	$metrics['get_session_latency_ms'] = $lantency;

	#List session
	$listSessionsRequest = new Google\Cloud\Spanner\V1\ListSessionsRequest();
	$listSessionsRequest->setDatabase($_DATABASE);
	$time_start = microtime_float();
	$response = $client->ListSessions($listSessionsRequest);
	$lantency =  (microtime_float() - $time_start) * 1000;
	$metrics['list_sessions_latency_ms'] = $lantency;

	#Delete session
	$deleteSessionRequest = new Google\Cloud\Spanner\V1\DeleteSessionRequest();
	$deleteSessionRequest->setName($session->getName());
	$time_start = microtime_float();
	$client->deleteSession($deleteSessionRequest);
	$lantency =  (microtime_float() - $time_start) * 1000;
	$metrics['delete_session_latency_ms'] = $lantency;

}

/*
Probes to test ExecuteSql and ExecuteStreamingSql call from Spanner stub.

  Args:
    stub: An object of SpannerStub.
    metrics: A list of metrics.

*/
function executeSql($client, &$metrics){
	global $_DATABASE;

	$createSessionRequest = new Google\Cloud\Spanner\V1\CreateSessionRequest();
	$createSessionRequest->setDatabase($_DATABASE);
	list($session, $status) = $client->CreateSession($createSessionRequest)->wait();

	hardAssertIfStatusOk($status);
	hardAssert($session !== null, 'Call completed with a null response');

	# Probing ExecuteSql call
	$time_start = microtime_float();
	$executeSqlRequest = new Google\Cloud\Spanner\V1\ExecuteSqlRequest();
	$executeSqlRequest->setSession($session->getName());
	$executeSqlRequest->setSql('select * FROM users');
	$result_set = $client->ExecuteSql($executeSqlRequest);
	$lantency =  (microtime_float() - $time_start) * 1000;
	$metrics['execute_sql_latency_ms'] = $lantency;

	// TODO: Error check result_set

	# Probing ExecuteStreamingSql call
	$partial_result_set = $client->ExecuteStreamingSql($executeSqlRequest);

	$time_start = microtime_float();
	$first_result = array_values($partial_result_set->getMetadata())[0];
	$lantency =  (microtime_float() - $time_start) * 1000;
	$metrics['execute_streaming_sql_latency_ms'] = $lantency;

	// TODO: Error Check for sreaming sql first result
	
	$deleteSessionRequest = new Google\Cloud\Spanner\V1\DeleteSessionRequest();
	$deleteSessionRequest->setName($session->getName());
	$client->deleteSession($deleteSessionRequest);
}

/*
Probe to test Read and StreamingRead grpc call from Spanner stub.

  Args:
    stub: An object of SpannerStub.
    metrics: A list of metrics.
*/

function read($client, &$metrics){
	global $_DATABASE;

	$createSessionRequest = new Google\Cloud\Spanner\V1\CreateSessionRequest();
	$createSessionRequest->setDatabase($_DATABASE);
	list($session, $status) = $client->CreateSession($createSessionRequest)->wait();

	hardAssertIfStatusOk($status);
	hardAssert($session !== null, 'Call completed with a null response');

	# Probing Read call
	$time_start = microtime_float();
	$readRequest = new Google\Cloud\Spanner\V1\ReadRequest();
	$readRequest->setSession($session->getName());
	$readRequest->setTable('users');
	$readRequest->setColumns(['username', 'firstname', 'lastname']);
	$keyset = new Google\Cloud\Spanner\V1\KeySet();
	$keyset->setAll(True);
	$readRequest->setKeySet($keyset);
	$result_set = $client->Read($readRequest);
	$lantency =  (microtime_float() - $time_start) * 1000;
	$metrics['read_latency_ms'] = $lantency;

	// TODO: Error Check for result_set

	# Probing StreamingRead call
	$partial_result_set = $client->StreamingRead($readRequest);
	$time_start = microtime_float();
	$first_result = array_values($partial_result_set->getMetadata())[0];
	$lantency =  (microtime_float() - $time_start) * 1000;
	$metrics['streaming_read_latency_ms'] = $lantency;

	//TODO: Error Check for streaming read first result

	$deleteSessionRequest = new Google\Cloud\Spanner\V1\DeleteSessionRequest();
	$deleteSessionRequest->setName($session->getName());
	$client->deleteSession($deleteSessionRequest);
}

/*
Probe to test BeginTransaction, Commit and Rollback grpc from Spanner stub.

  Args:
    stub: An object of SpannerStub.
    metrics: A list of metrics.
*/

function transaction($client, &$metrics){
	global $_DATABASE;

	$createSessionRequest = new Google\Cloud\Spanner\V1\CreateSessionRequest();
	$createSessionRequest->setDatabase($_DATABASE);
	list($session, $status) = $client->CreateSession($createSessionRequest)->wait();

	hardAssertIfStatusOk($status);
	hardAssert($session !== null, 'Call completed with a null response');

	$txn_options = new Google\Cloud\Spanner\V1\TransactionOptions();
	$rw = new Google\Cloud\Spanner\V1\TransactionOptions\ReadWrite();
	$txn_options->setReadWrite($rw);
	$txn_request = new Google\Cloud\Spanner\V1\BeginTransactionRequest();
	$txn_request->setSession($session->getName());
	$txn_request->setOptions($txn_options);

	# Probing BeginTransaction call
	$time_start = microtime_float();
	list($txn, $status) = $client->BeginTransaction($txn_request)->wait();
	$lantency =  (microtime_float() - $time_start) * 1000;
	$metrics['begin_transaction_latency_ms'] = $lantency;

	hardAssertIfStatusOk($status);
	hardAssert($txn !== null, 'Call completed with a null response');

	# Probing Commit Call
	$commit_request = new Google\Cloud\Spanner\V1\CommitRequest();
	$commit_request->setSession($session->getName());
	$commit_request->setTransactionId($txn->getId());

	$time_start = microtime_float();
	$client->Commit($commit_request);
	$latency =  (microtime_float() - $time_start) * 1000;
	$metrics['commit_latency_ms'] = $lantency;

	# Probing Rollback call
	list($txn, $status) = $client->BeginTransaction($txn_request)->wait();
	$rollback_request = new Google\Cloud\Spanner\V1\RollbackRequest();
	$rollback_request->setSession($session->getName());
	$rollback_request->setTransactionId($txn->getId());

	hardAssertIfStatusOk($status);
	hardAssert($txn !== null, 'Call completed with a null response');

	$time_start = microtime_float();
	$client->Rollback($rollback_request);
	$latency =  (microtime_float() - $time_start) * 1000;
	$metrics['rollback_latency_ms'] = $latency;

	$deleteSessionRequest = new Google\Cloud\Spanner\V1\DeleteSessionRequest();
	$deleteSessionRequest->setName($session->getName());
	$client->deleteSession($deleteSessionRequest); 
}

/*
Probe to test PartitionQuery and PartitionRead grpc call from Spanner stub.

  Args:
    stub: An object of SpannerStub.
    metrics: A list of metrics.
*/

function partition($client, &$metrics){
	global $_DATABASE;
	global $_TEST_USERNAME;

	$createSessionRequest = new Google\Cloud\Spanner\V1\CreateSessionRequest();
	$createSessionRequest->setDatabase($_DATABASE);
	list($session, $status) = $client->CreateSession($createSessionRequest)->wait();

	hardAssertIfStatusOk($status);
	hardAssert($session !== null, 'Call completed with a null response');

	$txn_options = new Google\Cloud\Spanner\V1\TransactionOptions();
	$ro = new Google\Cloud\Spanner\V1\TransactionOptions\ReadOnly();
	$txn_options->setReadOnly($ro);
	$txn_selector = new Google\Cloud\Spanner\V1\TransactionSelector();
	$txn_selector->setBegin($txn_options);

	#Probing PartitionQuery call
	$ptn_query_request = new Google\Cloud\Spanner\V1\PartitionQueryRequest();
	$ptn_query_request->setSession($session->getName());
	$ptn_query_request->setSql('select * FROM users');
	$ptn_query_request->setTransaction($txn_selector);

	$time_start = microtime_float();
	$client->PartitionQuery($ptn_query_request);
	$lantency =  (microtime_float() - $time_start) * 1000;
	$metrics['partition_query_latency_ms'] = $lantency;

	#Probing PartitionRead call
	$ptn_read_request = new Google\Cloud\Spanner\V1\PartitionReadRequest();
	$ptn_read_request->setSession($session->getName());
	$ptn_read_request->setTable('users');
	$ptn_read_request->setTransaction($txn_selector);
	$keyset = new Google\Cloud\Spanner\V1\KeySet();
	$keyset->setAll(True);
	$ptn_read_request->setKeySet($keyset);
	$ptn_read_request->setColumns(['username', 'firstname', 'lastname']);

	$time_start = microtime_float();
	$client->PartitionRead($ptn_read_request);
	$latency =  (microtime_float() - $time_start) * 1000;
	$metrics['partition_read_latency_ms'] = $latency;

	# Delete Session
	$deleteSessionRequest = new Google\Cloud\Spanner\V1\DeleteSessionRequest();
	$deleteSessionRequest->setName($session->getName());
	$client->deleteSession($deleteSessionRequest); 
}

$PROBE_FUNCTIONS = [
	'session_management' => 'sessionManagement',
	'execute_sql' => 'executeSql',
	'read' => 'read',
	'transaction' => 'transaction',
	'partition' => 'partition'
];

return $PROBE_FUNCTIONS;



