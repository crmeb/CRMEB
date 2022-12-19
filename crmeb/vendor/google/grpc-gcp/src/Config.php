<?php
/*
 *
 * Copyright 2018 gRPC authors.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */
namespace Grpc\Gcp;

use Psr\Cache\CacheItemPoolInterface;

/**
 * Config is used to enable the support for the channel management.
 */
class Config
{
    private $hostname;
    private $gcp_call_invoker;
    private $cross_script_shmem_enabled;
    private $supported_sapis = ['fpm-fcgi', 'cli-server'];

    /**
     * @param string  $target The target API we want to manage the connection.
     * @param \Grpc\Gcp\ApiConfig   $conf
     * @param CacheItemPoolInterface $cacheItemPool A pool for storing configuration and channels
     *                                            cross requests within a single worker process.
     * @throws \RuntimeException When a failure occurs while attempting to attach to shared memory.
     */
    public function __construct($target, $conf = null, CacheItemPoolInterface $cacheItemPool = null)
    {
        if ($conf == null) {
            // If there is no configure file, use the default gRPC channel.
            $this->gcp_call_invoker = new \Grpc\DefaultCallInvoker();
            return;
        }

        $gcp_channel = null;
        $url_host = parse_url($target, PHP_URL_HOST);
        $this->hostname = $url_host ? $url_host : $target;
        $channel_pool_key = $this->hostname . '.gcp.channel.' . getmypid();

        if (!$cacheItemPool) {
            $affinity_conf = $this->parseConfObject($conf);
            $gcp_call_invoker = new GCPCallInvoker($affinity_conf);
            $this->gcp_call_invoker = $gcp_call_invoker;
        } else {
            $item = $cacheItemPool->getItem($channel_pool_key);
            if ($item->isHit()) {
                // Channel pool for the $hostname API has already created.
                $gcp_call_invoker = unserialize($item->get());
            } else {
                $affinity_conf = $this->parseConfObject($conf);
                // Create GCP channel based on the information.
                $gcp_call_invoker = new GCPCallInvoker($affinity_conf);
            }
            $this->gcp_call_invoker = $gcp_call_invoker;
            register_shutdown_function(function ($gcp_call_invoker, $cacheItemPool, $item) {
                // Push the current gcp_channel back into the pool when the script finishes.
                $item->set(serialize($gcp_call_invoker));
                $cacheItemPool->save($item);
            }, $gcp_call_invoker, $cacheItemPool, $item);
        }
    }

    /**
     * @return \Grpc\CallInvoker The call invoker to be hooked into the gRPC
     */
    public function callInvoker()
    {
        return $this->gcp_call_invoker;
    }

    /**
     * @return string The URI of the endpoint
     */
    public function getTarget()
    {
        return $this->channel->getTarget();
    }

    private function parseConfObject($conf_object)
    {
        $config = json_decode($conf_object->serializeToJsonString(), true);
        if (isset($config['channelPool'])) {
            $affinity_conf['channelPool'] = $config['channelPool'];
        }
        $aff_by_method = array();
        if (isset($config['method'])) {
            for ($i = 0; $i < count($config['method']); $i++) {
                // In proto3, if the value is default, eg 0 for int, it won't be serialized.
                // Thus serialized string may not have `command` if the value is default 0(BOUND).
                if (!array_key_exists('command', $config['method'][$i]['affinity'])) {
                    $config['method'][$i]['affinity']['command'] = 'BOUND';
                }
                $aff_by_method[$config['method'][$i]['name'][0]] = $config['method'][$i]['affinity'];
            }
        }
        $affinity_conf['affinity_by_method'] = $aff_by_method;
        return $affinity_conf;
    }

}
