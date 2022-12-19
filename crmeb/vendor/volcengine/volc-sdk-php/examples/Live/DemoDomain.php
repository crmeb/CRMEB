<?php

include 'DemoBase.php';
global $client;

$body = [
    'Domain' => 'domain',
    'Type' => 'push',
];

$response = $client->createDomain(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'Domain' => 'domain',
];

$response = $client->deletedomain(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'PageNum' => 1,
    'PageSize' => 1,
];

$response = $client->listDomainDetail(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'DomainList' => ['domain1', 'domain2'],
];
$response = $client->describeDomain(['json' => $body]);
echo $response;
echo '<br>';

$response = $client->enableDomain(['json' => $body]);
echo $response;
echo '<br>';

$response = $client->disableDomain(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'PullDomain' => 'pullDomain',
    'PushDomain' => 'pushDomain',
];
$response = $client->managerPullPushDomainBind(['json' => $body]);
echo $response;
echo '<br>';

$body = [
    'PullDomain' => 'pullDomain'
];
$response = $client->managerPullPushDomainBind(['json' => $body]);
echo $response;
echo '<br>';

