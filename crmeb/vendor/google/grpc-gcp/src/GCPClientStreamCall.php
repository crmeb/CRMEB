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

/**
 * Represents an active call that sends a stream of messages and then gets
 * a single response.
 */
class GCPClientStreamCall extends GcpBaseCall
{
    protected function createRealCall($data = null)
    {
        $channel_ref = $this->_rpcPreProcess($data);
        $this->real_call = new \Grpc\ClientStreamingCall($channel_ref->getRealChannel(
          $this->gcp_channel->credentials), $this->method, $this->deserialize, $this->options);
        $this->real_call->start($this->metadata_rpc);
        return $this->real_call;
    }
    /**
     * Pick a channel and start the call.
     *
     * @param array $metadata Metadata to send with the call, if applicable
     *                        (optional)
     */
    public function start(array $metadata = [])
    {
        // Postpone first rpc to write function(), where we can pick a channel
        // from the channel pool.
        $this->metadata_rpc = $metadata;
    }

    /**
     * Write a single message to the server. This cannot be called after
     * wait is called.
     *
     * @param ByteBuffer $data    The data to write
     * @param array      $options An array of options, possible keys:
     *                            'flags' => a number (optional)
     */
    public function write($data, array $options = [])
    {
        if (!$this->has_real_call) {
            $this->createRealCall($data);
            $this->has_real_call = true;
        }
        $this->real_call->write($data, $options);
    }

    /**
     * Wait for the server to respond with data and a status.
     *
     * @return array [response data, status]
     */
    public function wait()
    {
        list($response, $status) = $this->real_call->wait();
        $this->_rpcPostProcess($status, $response);
        return [$response, $status];
    }
}
