<?php


namespace crmeb\interfaces;


interface ListenerInterface
{
    public function handle($event): void;
}