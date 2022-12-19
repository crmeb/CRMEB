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
 * DeserializeCheck is used to check whether _ChannelRef is created by deserialization or not.
 * If it is, $real_channel is invalid thus we need to recreate it using $opts.
 * If not, we can use $real_channel directly instead of creating a new one.
 * It is useful to handle 'force_new' channel option.
 * This is a private class
 */
class CreatedByDeserializeCheck implements \Serializable
{
    // TODO(ddyihai): remove it once the serialzer handler for \Grpc\Channel is implemented.
    private $data;
    public function __construct()
    {
        $this->data = 1;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return '0';
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        $this->data = 1;
    }

    /**
     * @param $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getData()
    {
        return $this->data;
    }
}
