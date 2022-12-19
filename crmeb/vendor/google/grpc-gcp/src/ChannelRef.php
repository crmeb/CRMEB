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
 * ChannelRef is used to record how many active streams the channel has.
 * This is a private class
 */
class ChannelRef
{
    // $opts has all information except Credentials for creating a Grpc\Channel.
    private $opts;

    private $channel_id;
    private $affinity_ref;
    private $active_stream_ref;
    private $target;

    private $has_deserialized;
    private $real_channel;

    public function __construct($target, $channel_id, $opts, $affinity_ref=0, $active_stream_ref=0)
    {
        $this->target = $target;
        $this->channel_id = $channel_id;
        $this->affinity_ref = $affinity_ref;
        $this->active_stream_ref = $active_stream_ref;
        $this->opts = $opts;
        $this->has_deserialized = new CreatedByDeserializeCheck();
    }

    public function getRealChannel($credentials)
    {
        // TODO(ddyihai): remove this check once the serialize handler for
        // \Grpc\Channel is implemented(issue https://github.com/grpc/grpc/issues/15870).
        if (!$this->has_deserialized->getData()) {
            // $real_channel exists and is not created by the deserialization.
            return $this->real_channel;
        }
        // If this ChannelRef is created by deserialization, $real_channel is invalid
        // thus needs to be recreated becasue Grpc\Channel don't have serialize and
        // deserialize handler.
        // Since [target + augments + credentials] will be the same during the recreation,
        // it will reuse the underline grpc channel in C extension without creating a
        // new connection.

        // 'credentials' in the array $opts will be unset during creating the channel.
        if (!array_key_exists('credentials', $this->opts)) {
            $this->opts['credentials'] = $credentials;
        }
        $real_channel = new \Grpc\Channel($this->target, $this->opts);
        $this->real_channel = $real_channel;
        // Set deserialization to false so it won't be recreated within the same script.
        $this->has_deserialized->setData(0);
        return $real_channel;
    }

    public function getAffinityRef()
    {
        return $this->affinity_ref;
    }
    public function getActiveStreamRef()
    {
        return $this->active_stream_ref;
    }
    public function affinityRefIncr()
    {
        $this->affinity_ref += 1;
    }
    public function affinityRefDecr()
    {
        $this->affinity_ref -= 1;
    }
    public function activeStreamRefIncr()
    {
        $this->active_stream_ref += 1;
    }
    public function activeStreamRefDecr()
    {
        $this->active_stream_ref -= 1;
    }
}
