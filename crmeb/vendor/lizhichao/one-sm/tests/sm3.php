<?php
require __DIR__ . '/../vendor/autoload.php';
function eq($a, $b)
{
    if ($a !== $b) {
        print_r([$a, '!==', $b]);
        throw new \Exception('error');
    }
    print_r([$a, $b, 'true']);
}

function hmac($data = null, $secret = null, $raw_output = false)
{
    $sm3  = new \OneSm\Sm3();
    $size = \strlen($sm3->sign('test'));
    $pack = 'H' . (string)$size;
    if (\strlen($secret) > $size) {
        $secret = pack($pack, $sm3->sign($secret));
    }
    $key  = str_pad($secret, $size, \chr(0x00));
    $ipad = $key ^ str_repeat(\chr(0x36), $size);
    $opad = $key ^ str_repeat(\chr(0x5C), $size);
    $hmac = $sm3->sign($opad . pack($pack, $sm3->sign($ipad . $data)));

    return $raw_output ? pack($pack, $hmac) : $hmac;
}

$sm3 = new \OneSm\Sm3();
eq('66c7f0f462eeedd9d1f2d46bdc10e4e24167c4875cf2f7a2297da02b8f4ba8e0', $sm3->sign('abc'));
eq('1294da78431a20991584c68a669f2c59618e08bf0d7989f35f6ae1d7d570e143', $sm3->sign(str_repeat("adfas哈哈哈", 100)));
eq('1ab21d8355cfa17f8e61194831e81a8f22bec8c728fefb747ed035eb5082aa2b', $sm3->sign(''));

eq(
    '8e4bd77d8a10526fae772bb6014dfaed0335491e1cdfa92d3aca1481ae5d9a83',
    hmac(str_repeat('abc', 1000), 'secret')
);
eq(
    hex2bin('8e4bd77d8a10526fae772bb6014dfaed0335491e1cdfa92d3aca1481ae5d9a83'),
    hmac(str_repeat('abc', 1000), 'secret', true)
);

