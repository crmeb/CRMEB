<?php

require __DIR__ . '/../../vendor/autoload.php';
// also load our custom processor...
require __DIR__ . '/SchemaQueryParameter.php';

// merge our custom processor
$processors = [];
foreach (\OpenApi\Analysis::processors() as $processor) {
    $processors[] = $processor;
    if ($processor instanceof \OpenApi\Processors\BuildPaths) {
        $processors[] = new \SchemaQueryParameterProcessor\SchemaQueryParameter();
    }
}

$options = [
    'processors' => $processors,
];

$openapi = OpenApi\scan(__DIR__ . '/app', $options);
$spec = json_encode($openapi, JSON_PRETTY_PRINT);
file_put_contents(__DIR__ . '/schema-query-parameter-processor.json', $spec);
//echo $spec;