<?php
/*
 * Copyright 2016, Google Inc.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 *
 *     * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above
 * copyright notice, this list of conditions and the following disclaimer
 * in the documentation and/or other materials provided with the
 * distribution.
 *     * Neither the name of Google Inc. nor the names of its
 * contributors may be used to endorse or promote products derived from
 * this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
namespace Google\GAX;

use InvalidArgumentException;

/**
 * Holds the description information used for page streaming.
 */
class PageStreamingDescriptor
{
    private $requestPageTokenField;
    private $requestPageSizeField;
    private $responsePageTokenField;
    private $resourceField;

    /**
     * @param array $descriptor {
     *     Required.
     *
     *     @type string $requestPageTokenField the page token field in the request object.
     *     @type string $responsePageTokenField the page token field in the response object.
     *     @type string $resourceField the resource field in the response object.
     * }
     */
    public function __construct($descriptor)
    {
        self::validate($descriptor);
        $this->requestPageTokenField = $descriptor['requestPageTokenField'];
        if (isset($descriptor['requestPageSizeField'])) {
            $this->requestPageSizeField = $descriptor['requestPageSizeField'];
        } else {
            $this->requestPageSizeField = null;
        }
        $this->responsePageTokenField = $descriptor['responsePageTokenField'];
        $this->resourceField = $descriptor['resourceField'];
    }

    public function getRequestPageTokenField()
    {
        return $this->requestPageTokenField;
    }

    public function getRequestPageSizeField()
    {
        return $this->requestPageSizeField;
    }

    public function requestHasPageSizeField()
    {
        return isset($this->requestPageSizeField);
    }

    public function getResponsePageTokenField()
    {
        return $this->responsePageTokenField;
    }

    public function getResourceField()
    {
        return $this->resourceField;
    }

    private static function validate($descriptor)
    {
        $requiredFields = ['requestPageTokenField', 'responsePageTokenField', 'resourceField'];
        foreach ($requiredFields as $field) {
            if (empty($descriptor[$field])) {
                throw new InvalidArgumentException(
                    "$field is required for PageStreamingDescriptor"
                );
            }
        }
    }
}
