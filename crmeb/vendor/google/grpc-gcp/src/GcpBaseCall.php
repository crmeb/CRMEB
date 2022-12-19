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

abstract class GcpBaseCall
{
    const BOUND = 'BOUND';
    const UNBIND = 'UNBIND';
    const BIND = 'BIND';


    protected $gcp_channel;
    // It has the Grpc\Channel and related ref_count information for this RPC.
    protected $channel_ref;
    // If this RPC is 'UNBIND', use it instead of the one from response.
    protected $affinity_key;
    // Array of [affinity_key, command]
    protected $_affinity;

    // Information needed to create Grpc\Call object when the RPC starts.
    protected $method;
    protected $argument;
    protected $metadata;
    protected $options;

    // In GCP extension, it is when a RPC calls "start", we pick a channel.
    // Thus we need to save the $me
    protected $metadata_rpc = array();
    // first_rpc is used to check whether the first request is sent for client
    // streaming RPC.
    protected $has_real_call = null;

    protected $real_call;

    /**
     * Create a new Call wrapper object.
     *
     * @param Channel  $channel     The channel to communicate on
     * @param string   $method      The method to call on the
     *                              remote server
     * @param callback $deserialize A callback function to deserialize
     *                              the response
     * @param array    $options     Call options (optional)
     */
    public function __construct($channel, $method, $deserialize, $options)
    {
        $this->gcp_channel = $channel;
        $this->method = $method;
        $this->deserialize = $deserialize;
        $this->options = $options;
        $this->_affinity = null;

        if (isset($this->gcp_channel->affinity_conf['affinity_by_method'][$method])) {
            $this->_affinity = $this->gcp_channel->affinity_conf['affinity_by_method'][$method];
        }
    }

    /**
     * Pick a ChannelRef from the channel pool based on the request and
     * the affinity config.
     *
     * @param mixed $argument Requests.
     *
     * @return ChannelRef
     */
    protected function _rpcPreProcess($argument)
    {
        $this->affinity_key = null;
        if ($this->_affinity) {
            $command = $this->_affinity['command'];
            if ($command == self::BOUND || $command == self::UNBIND) {
                $this->affinity_key = $this->getAffinityKeyFromProto($argument);
            }
        }
        $this->channel_ref = $this->gcp_channel->getChannelRef($this->affinity_key);
        $this->channel_ref->activeStreamRefIncr();
        return $this->channel_ref;
    }

    /**
     * Update ChannelRef when RPC finishes.
     *
     * @param \stdClass $status The status object, with integer $code, string
     *                   $details, and array $metadata members
     * @param mixed $response Response.
     */
    protected function _rpcPostProcess($status, $response)
    {
        if ($this->_affinity) {
            $command = $this->_affinity['command'];
            if ($command == self::BIND) {
                if ($status->code != \Grpc\STATUS_OK) {
                    return;
                }
                $affinity_key = $this->getAffinityKeyFromProto($response);
                $this->gcp_channel->bind($this->channel_ref, $affinity_key);
            } elseif ($command == self::UNBIND) {
                $this->gcp_channel->unbind($this->affinity_key);
            }
        }
        $this->channel_ref->activeStreamRefDecr();
    }

    /**
     * Get the affinity key based on the affinity config.
     *
     * @param mixed $proto Objects may contain the affinity key.
     *
     * @return string Affinity key.
     */
    protected function getAffinityKeyFromProto($proto)
    {
        if ($this->_affinity) {
            $names = $this->_affinity['affinityKey'];
            $names_arr = explode(".", $names);
            foreach ($names_arr as $name) {
                $getAttrMethod = 'get' . ucfirst($name);
                $proto = call_user_func_array(array($proto, $getAttrMethod), array());
            }
            return $proto;
        }
        echo "Cannot find the field in the proto\n";
    }

    /**
     * @return mixed The metadata sent by the server
     */
    public function getMetadata()
    {
        if (!$this->has_real_call) {
            $this->createRealCall();
            $this->has_real_call = true;
        }
        return $this->real_call->getMetadata();
    }

    /**
     * @return mixed The trailing metadata sent by the server
     */
    public function getTrailingMetadata()
    {
        if (!$this->has_real_call) {
            $this->createRealCall();
            $this->has_real_call = true;
        }
        return $this->real_call->getTrailingMetadata();
    }

    /**
     * @return string The URI of the endpoint
     */
    public function getPeer()
    {
        if (!$this->has_real_call) {
            $this->createRealCall();
            $this->has_real_call = true;
        }
        return $this->real_call->getPeer();
    }

    /**
     * Cancels the call.
     */
    public function cancel()
    {
        if (!$this->has_real_call) {
            $this->has_real_call = true;
            $this->createRealCall();
        }
        $this->real_call->cancel();
    }

    /**
     * Serialize a message to the protobuf binary format.
     *
     * @param mixed $data The Protobuf message
     *
     * @return string The protobuf binary format
     */
    protected function _serializeMessage($data)
    {
        return $this->real_call->_serializeMessage($data);
    }

    /**
     * Deserialize a response value to an object.
     *
     * @param string $value The binary value to deserialize
     *
     * @return mixed The deserialized value
     */
    protected function _deserializeResponse($value)
    {
        return $this->real_call->_deserializeResponse($value);
    }

    /**
     * Set the CallCredentials for the underlying Call.
     *
     * @param CallCredentials $call_credentials The CallCredentials object
     */
    public function setCallCredentials($call_credentials)
    {
        $this->call->setCredentials($call_credentials);
    }
}
