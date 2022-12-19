<?php

use Fastknife\Domain\Factory;

require 'autoload.php';

$config = require '../src/config.php';


function showBlock()
{
    global $config;
    $factory = new Factory($config);
    $blockImage = $factory->makeBlockImage();
    $blockImage->run();
    $blockImage->echo();
}

function showWord()
{
    global $config;
    $factory = new Factory($config);
    $blockImage = $factory->makeWordImage();
    $blockImage->run();
    $blockImage->echo();
}

showWord();