<?php

declare(strict_types=1);

require 'autoload.php';

$captchaType = @$_REQUEST['captchaType'];

if (!in_array($captchaType, ['clickWord', 'blockPuzzle'])) {
    throw new Exception('缺少参数:captchaType');
}

$controllerName = ucfirst($captchaType) . 'Controller';

require $controllerName . '.php';

$controller = new $controllerName;

$controller->verification();
