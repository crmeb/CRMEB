<?php

/**
 * @property string $ak
 * @property string $sk
 * @property string $operateDomain
 * @property int $startTime
 * @property int $endTime
 * @property string $exampleDomain
 */
class Config {

    public function __construct() {
        $this->ak = "ak";
        $this->sk = "sk";
        $this->operateDomain = 'operate.com';
        $this->exampleDomain = 'example.com';
        $this->startTime = time();
        $this->endTime = time() + 60*10;
    }
}