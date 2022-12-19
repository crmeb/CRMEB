<?php
declare(strict_types=1);
require "autoload.php";
use \Fastknife\Utils\AesUtils;
$key = "oc7ygju8xf03ov4ttd5ito7qnuapyp6s";
$point = [
    ['x' => 135, 'y' => 58],
    ['x' => 82, 'y' => 72],
    ['x' => 56, 'y' => 112]
];
$point = json_encode($point);
//[{"x":135,"y":58},{"x":82,"y":72},{"x":56,"y":112}]
//var_dump();
//print_r(AesUtils::encrypt($point,$key));
//php  w2GGF3+0q0K0AdTysBEKynRo9hXBbXBPUZU1GPWJKlM4SwtrbmV17CFcTq/T53Kvlk0FWSbFzfCC1NuAA6wsmw==
//js  FOl3kz52f4xptJ/Zf7MoZYQmYa7C1pjQaWP8QqhcX8FH43SvpCBPhaSqqMbE8D55ufhgjBVor01UZRH3uE6DNw==




//js +5s4V1MeDYk1jpvfwJACJA==
//php C01B0oArq8aMhlMmSdRbDA==
