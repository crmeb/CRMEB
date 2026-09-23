<?php

namespace Volc\Service\Live\V20230101;


// sdk 入口文件，可在此进行自定义（该文件在 codegen 时不会覆盖；但如果没有，会重新生成）
use Volc\Base\V4Curl;

class Live extends V4Curl
{
    use LiveTrait;

    /**
     * @var array[]
     */
    public $config;
    
    /**
     * @var array[]
     */
    public $apiList;

    public function __construct($config)
    {
        $this->config = LiveConfig::region();
        $this->apiList = LiveConfig::apiList();
        parent::__construct($config);
    }

    protected function getConfig(string $region)
    {
        if (!isset($this->config[$region])) {
            throw new \Exception(sprintf("Live not support region, %s", $region));
        }
        return $this->config[$region];
    }

    public function __call($name, $arguments)
    {
        if (strlen($name) > 1) {
            $commitName = strtoupper($name[0]) . substr($name, 1);
        } else {
            $commitName = strtoupper($name[0]);
        }

        $apiConfig = $this->apiList[$commitName];
        if ($apiConfig == null) {
            throw new \Exception("no such api");
        }

        $response = $this->makeApiRequestDelegate($apiConfig)($arguments);
        return json_decode((string)$response->getBody(), true);
    }

    private function makeApiRequestDelegate(array $apiConfig)
    {
        $hasQuery = $apiConfig['has_query'];
        $hasJson = $apiConfig['has_json'];
        $action = $apiConfig['config']['query']['Action'];
        if (!$hasQuery && !$hasJson) {
            return function () use ($action) {
                return $this->request($action, ['action' => $action]);
            };
        } else if (!$hasQuery && $hasJson) {
            return function ($args) use ($action) {
                $json = $args[0];
                return $this->request($action, ['action' => $action, 'json' => $json]);
            };
        } else if ($hasQuery && !$hasJson) {
            return function ($args) use ($action) {
                $query = $args[0];
                return $this->request($action, ['action' => $action, 'query' => $query]);
            };
        } else {
            return function ($args) use ($action) {
                $query = $args[0];
                $json = $args[1];
                return $this->request($action, ['action' => $action, 'query' => $query, 'json' => $json]);
            };
        }
    }
}

