<?php

include __DIR__ . '/../classes/Bootstrap.php';

$grid1 = [
    [1, 3, 2],
    [2, 3, 1],
];

$grid2 = [
    [1, 6],
    [0, 1],
];

$matrix = new Matrix\Matrix($grid1);

$new = $matrix->directsum(new Matrix\Matrix($grid2));

var_dump($new);
