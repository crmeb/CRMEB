<?php
declare(strict_types=1);
(function () {
    foreach ([
                 'vendor/autoload.php',
                 '../vendor/autoload.php',
                 '../../vendor/autoload.php',
                 '../../../vendor/autoload.php',
                 '../../../../vendor/autoload.php'
             ] as $file) {
        if (is_file($file)) {
            /** @noinspection PhpIncludeInspection */
            include $file;
        }
    }
})();
