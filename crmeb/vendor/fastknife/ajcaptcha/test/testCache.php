<?php
use Fastknife\Domain\Logic\Cache;

include '../src/Domain/Logic/Cache.php';
include '../src/Utils/CacheUtils.php';

$config = include '../src/config.php';

$cacheEntity = new Cache($config['cache']);

var_dump($cacheEntity->get('haha'));

$cacheEntity->set('haha', 1, 60);

var_dump($cacheEntity->has('haha'));

var_dump($cacheEntity->get('haha'));

$cacheEntity->delete('haha');

var_dump($cacheEntity->get('haha'));