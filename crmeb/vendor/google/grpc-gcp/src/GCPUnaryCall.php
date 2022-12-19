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
 * Represents an active call that sends a single message and then gets a
 * single response.
 */
class GCPUnaryCall extends GcpBaseCall
{
    protected function createRealCall($channel)
    {
        $this->real_call = new \Grpc\UnaryCall($channel, $this->method, $this->deserialize, $this->options);
        $this->has_real_call = true;
        return $this->real_call;
    }

    /**
     * Pick a channel and start the call.
     *
     * @param mixed $data     The data to send
     * @param array $metadata Metadata to send with the call, if applicable
     *                        (optional)
     * @param array $options  An array of options, possible keys:
     *                        'flags' => a number (optional)
     */
    public function start($argument, $metadata, $options)
    {
        $channel_ref = $this->_rpcPreProcess($argument);
        $real_channel = $channel_ref->getRealChannel($this->gcp_channel->credentials);
        $this->createRealCall($real_channel);
        $this->real_call->start($argument, $metadata, $options);
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

    /**
     * @return mixed The metadata sent by the server
     */
    public function getMetadata()
    {
        return $this->real_call->getMetadata();
    }
}
