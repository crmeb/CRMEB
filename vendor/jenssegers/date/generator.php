<?php
require 'vendor/autoload.php';

use Jenssegers\Date\Date;

// Set the language
setlocale(LC_ALL, $argv[1] ?: 'en');

$months = array(
    'january',
    'february',
    'march',
    'april',
    'may',
    'june',
    'july',
    'august',
    'september',
    'october',
    'november',
    'december',
);

$days = array(
    'monday',
    'tuesday',
    'wednesday',
    'thursday',
    'friday',
    'saturday',
    'sunday',
);

$translations = array();

foreach ($months as $month) {
    $date = new Date($month);
    $translation = strftime('%B', $date->getTimestamp());
    $translations[$month] = $translation;

    echo "'" . $month . "'\t=> '" . $translation . "',\n";
}

echo "\n";

foreach ($days as $day) {
    $date = new Date($day);
    $translation = strftime('%A', $date->getTimestamp());
    $translations[$day] = $translation;

    echo "'" . $day . "'\t=> '" . $translation . "',\n";
}
