<?php

include 'DemoBase.php';
global $client;

$body = [
    "DomainList" => ["example.com", "example2.com"],
    "StartTime" => "2022-04-13T00:00:00Z",
    "EndTime" => "2022-04-14T00:00:00Z",
    "Aggregation" => 300,
];

$response = $client->describeLiveBandwidthData(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    "DomainList" => ["example.com", "example2.com"],
    "StartTime" => "2022-04-13T00:00:00Z",
    "EndTime" => "2022-04-14T00:00:00Z",
    "Aggregation" => 300,
];

$response = $client->describeLiveTrafficData(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    "StartTime" => "2022-04-13T00:00:00Z",
    "EndTime" => "2022-04-14T00:00:00Z",
    "Aggregation" => 300,
];

$response = $client->describeLiveP95PeakBandwidthData(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    "DomainList" => ["example.com", "example2.com"],
    "StartTime" => "2022-04-13T00:00:00Z",
    "EndTime" => "2022-04-13T00:10:00Z",
    "Aggregation" => 300,
];

$response = $client->describeRecordData(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    "DomainList" => ["example.com", "example2.com"],
    "StartTime" => "2022-03-07T00:00:00+08:00",
    "EndTime" => "2022-03-08T00:00:00+08:00",
    "Aggregation" => 86400,
];

$response = $client->describeTranscodeData(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    "DomainList" => ["example.com", "example2.com"],
    "StartTime" => "2022-03-07T00:00:00+08:00",
    "EndTime" => "2022-03-08T00:00:00+08:00",
    "Aggregation" => 86400,
];

$response = $client->describeSnapshotData(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    "DomainList" => "example.com,example2.com",
    "StartTime" => "2022-04-21T14:00:00+08:00",
    "EndTime" => "2022-04-22T14:00:00+08:00",
];

$response = $client->describeLiveDomainLog(['query' => $body]);
echo $response;
echo '<br>';

$body = [
    "Domain" => "example.com",
    "App" => "example_app",
    "Stream" => "example_stream",
    "StartTime" => "2022-04-21T00:00:00Z",
    "EndTime" => "2022-04-23T23:59:59Z",
    "Aggregation" => 30,
];

$response = $client->describePushStreamMetrics(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    "Domain" => "example.com",
    "App" => "example",
    "Stream" => "example080601",
    "ProtocolList" => ["RTMP", "HTTP-FLV"],
    "StartTime" => "2022-04-21T00:00:00Z",
    "EndTime" => "2022-04-21T10:59:59Z",
    "Aggregation" => 60,
];

$response = $client->describeLiveStreamSessions(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    "DomainList" => ["example.com", "example2.com"],
    "StartTime" => "2021-08-16T00:00:00Z",
    "EndTime" => "2021-08-16T00:01:59Z",
    "Aggregation" => 60
];

$response = $client->describePlayResponseStatusStat(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    "DomainList" => ["example.com"],
    "ProtocolList" => ["HTTP-FLV", "RTMP"],
    "ISPList" => ["telecom"],
    "IPList" => ["123.123.123.000"],
    "RegionList" => [
        [
            "Area" => "CN",
            "Country" => "CN",
            "Province" => "beijing",
        ],
    ],
    "StartTime" => "2022-04-13T00:00:00+08:00",
    "EndTime" => "2022-04-13T12:00:00+08:00",
    "Aggregation" => 300,
    "ShowDetail" => True,
];

$response = $client->describeLiveMetricTrafficData(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    "DomainList" => ["example.com"],
    "ProtocolList" => ["HTTP-FLV", "RTMP"],
    "ISPList" => ["telecom"],
    "IPList" => ["123.123.123.000"],
    "RegionList" => [
        [
            "Area" => "CN",
            "Country" => "CN",
            "Province" => "beijing",
        ]
    ],
    "StartTime" => "2021-04-13T00:00:00+08:00",
    "EndTime" => "2021-04-14T00:00:00+08:00",
    "Aggregation" => 300,
    "ShowDetail" => True,
];

$response = $client->describeLiveMetricBandwidthData(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    "Domain" => "example.com",
    "StartTime" => "2022-04-13T00:00:00+08:00",
    "EndTime" => "2022-04-14T00:00:00+08:00",
];

$response = $client->describePlayStreamList(['query' => $body]);
echo $response;
echo '<br>';


$body = [
    "DomainList" => ["example.com"],
    "DstAddrTypeList" => ["live","Third"],
    "StartTime" => "2021-04-13T00:00:00+08:00",
    "EndTime" => "2021-04-14T00:00:00+08:00",
    "Aggregation" => 300,
    "ShowDetail" => True,
];

$response = $client->describePullToPushBandwidthData(['json' => $body]);
echo $response;
echo '<br>';


$body = [
    "DomainList" => ["example.com", "example2.com"],
    "DetailField" => ["Domain"],
    "StartTime" => "2022-07-13T00:00:00+08:00",
    "EndTime" => "2022-07-14T00:00:00+08:00",
    "Aggregation" => 86400,
];

$response = $client->describeLiveAuditData(['json' => $body]);
echo $response;
echo '<br>';