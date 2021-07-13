<?php
require_once __DIR__ . '/../autoload.php';
use Qiniu\Etag;

$localFile = "/Users/jemy/Documents/qiniu.mp4";
list($etag, $err) = Etag::sum($localFile);
if ($err == null) {
    echo "Etag: $etag";
} else {
    var_dump($err);
}
