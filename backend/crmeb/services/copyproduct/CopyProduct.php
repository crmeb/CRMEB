<?php
namespace crmeb\services\copyproduct;

abstract class CopyProduct
{
    protected $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    abstract public function copy($url, array $options = []);

    abstract public function getPlatforms();

    abstract public function getCapabilities();

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }
}