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
 * Represents an active call that allows for sending and recieving messages
 * in streams in any order.
 */
class GCPBidiStreamingCall extends GcpBaseCall
{
    private $response = null;

    protected function createRealCall($data = null)
    {
        $channel_ref = $this->_rpcPreProcess($data);
        $this->real_call = new \Grpc\BidiStreamingCall($channel_ref->getRealChannel(
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
        $this->metadata_rpc = $metadata;
    }

    /**
     * Reads the next value from the server.
     *
     * @return mixed The next value from the server, or null if there is none
     */
    public function read()
    {
        if (!$this->has_real_call) {
            $this->createRealCall();
            $this->has_real_call = true;
        }
        $response = $this->real_call->read();
        if ($response) {
            $this->response = $response;
        }
        return $response;
    }

    /**
     * Write a single message to the server. This cannot be called after
     * writesDone is called.
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
     * Indicate that no more writes will be sent.
     */
    public function writesDone()
    {
        if (!$this->has_real_call) {
            $this->createRealCall();
            $this->has_real_call = true;
        }
        $this->real_call->writesDone();
    }

    /**
     * Wait for the server to send the status, and return it.
     *
     * @return \stdClass The status object, with integer $code, string
     *                   $details, and array $metadata members
     */
    public function getStatus()
    {
        $status = $this->real_call->getStatus();
        $this->_rpcPostProcess($status, $this->response);
        return $status;
    }
}
