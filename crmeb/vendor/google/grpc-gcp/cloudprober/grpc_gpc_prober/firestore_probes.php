<?php

require('../vendor/autoload.php');

$_PARENT_RESOURCE = 'projects/grpc-prober-testing/databases/(default)/documents';

/*
Probes to test ListDocuments grpc call from Firestore stub.

  Args:
    stub: An object of FirestoreStub.
    metrics: A dict of metrics.
*/

function document($client, &$metrics){
	global $_PARENT_RESOURCE;
	
	$list_document_request = new Google\Cloud\Firestore\V1beta1\ListDocumentsRequest();
	$list_document_request->setParent($_PARENT_RESOURCE);
	$time_start = microtime_float();

	$client->ListDocuments($list_document_request);

	$lantency = (microtime_float()- $time_start) * 1000;
	$metrics['list_documents_latency_ms'] = $lantency;

}

$probFunctions = [
	'documents' => 'document'
];

return $probFunctions;