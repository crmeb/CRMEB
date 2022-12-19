<?php
/*
 * Copyright 2016 Google LLC
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
namespace Google\ApiCore;

use InvalidArgumentException;

/**
 * Holds the description information used for page streaming.
 */
class PageStreamingDescriptor
{
    private $descriptor;

    /**
     * @param array $descriptor {
     *     Required.
     *     @type string $requestPageTokenGetMethod the get method for the page token in the request object.
     *     @type string $requestPageTokenSetMethod the set method for the page token in the request object.
     *     @type string $responsePageTokenGetMethod the get method for the page token in the response object.
     *     @type string resourcesGetMethod the get method for the resources in the response object.
     *
     *     Optional.
     *     @type string $requestPageSizeGetMethod the get method for the page size in the request object.
     * }
     */
    public function __construct(array $descriptor)
    {
        self::validate($descriptor);
        $this->descriptor = $descriptor;
    }

    /**
     * @param array $fields {
     *     Required.
     *
     *     @type string $requestPageTokenField the page token field in the request object.
     *     @type string $responsePageTokenField the page token field in the response object.
     *     @type string $resourceField the resource field in the response object.
     *
     *     Optional.
     *     @type string $requestPageSizeField the page size field in the request object.
     * }
     * @return PageStreamingDescriptor
     */
    public static function createFromFields(array $fields)
    {
        $requestPageToken = $fields['requestPageTokenField'];
        $responsePageToken = $fields['responsePageTokenField'];
        $resources = $fields['resourceField'];

        $descriptor = [
            'requestPageTokenGetMethod' => PageStreamingDescriptor::getMethod($requestPageToken),
            'requestPageTokenSetMethod' => PageStreamingDescriptor::setMethod($requestPageToken),
            'responsePageTokenGetMethod' => PageStreamingDescriptor::getMethod($responsePageToken),
            'resourcesGetMethod' => PageStreamingDescriptor::getMethod($resources),
        ];

        if (isset($fields['requestPageSizeField'])) {
            $requestPageSize = $fields['requestPageSizeField'];
            $descriptor['requestPageSizeGetMethod'] = PageStreamingDescriptor::getMethod($requestPageSize);
            $descriptor['requestPageSizeSetMethod'] = PageStreamingDescriptor::setMethod($requestPageSize);
        }

        return new PageStreamingDescriptor($descriptor);
    }

    private static function getMethod(string $field)
    {
        return 'get' . ucfirst($field);
    }

    private static function setMethod(string $field)
    {
        return 'set' . ucfirst($field);
    }

    /**
     * @return string The page token get method on the request object
     */
    public function getRequestPageTokenGetMethod()
    {
        return $this->descriptor['requestPageTokenGetMethod'];
    }

    /**
     * @return string The page size get method on the request object
     */
    public function getRequestPageSizeGetMethod()
    {
        return $this->descriptor['requestPageSizeGetMethod'];
    }

    /**
     * @return bool True if the request object has a page size field
     */
    public function requestHasPageSizeField()
    {
        return array_key_exists('requestPageSizeGetMethod', $this->descriptor);
    }

    /**
     * @return string The page token get method on the response object
     */
    public function getResponsePageTokenGetMethod()
    {
        return $this->descriptor['responsePageTokenGetMethod'];
    }

    /**
     * @return string The resources get method on the response object
     */
    public function getResourcesGetMethod()
    {
        return $this->descriptor['resourcesGetMethod'];
    }

    /**
     * @return string The page token set method on the request object
     */
    public function getRequestPageTokenSetMethod()
    {
        return $this->descriptor['requestPageTokenSetMethod'];
    }

    /**
     * @return string The page size set method on the request object
     */
    public function getRequestPageSizeSetMethod()
    {
        return $this->descriptor['requestPageSizeSetMethod'];
    }

    private static function validate(array $descriptor)
    {
        $requiredFields = [
            'requestPageTokenGetMethod',
            'requestPageTokenSetMethod',
            'responsePageTokenGetMethod',
            'resourcesGetMethod',
        ];
        foreach ($requiredFields as $field) {
            if (empty($descriptor[$field])) {
                throw new InvalidArgumentException(
                    "$field is required for PageStreamingDescriptor"
                );
            }
        }
    }
}
