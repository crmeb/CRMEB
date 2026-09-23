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

use IteratorAggregate;

/**
 * A Page object wraps an API list method response and provides methods
 * to retrieve additional pages using the page token.
 */
class Page implements IteratorAggregate
{
    const FINAL_PAGE_TOKEN = "";

    private $parameters;
    private $callable;
    private $pageStreamingDescriptor;

    private $pageToken;

    private $response;

    public function __construct($params, $callable, $pageStreamingDescriptor)
    {
        if (empty($params) || !is_object($params[0])) {
            throw new InvalidArgumentException('First argument must be a request object.');
        }
        $this->parameters = $params;
        $this->callable = $callable;
        $this->pageStreamingDescriptor = $pageStreamingDescriptor;

        $requestPageTokenField = $this->pageStreamingDescriptor->getRequestPageTokenField();
        $this->pageToken = $params[0]->$requestPageTokenField;

        // Make gRPC call eagerly
        $this->response = call_user_func_array($this->callable, $this->parameters);
    }

    /**
     * Returns true if there are more pages that can be retrieved from the
     * API.
     */
    public function hasNextPage()
    {
        return strcmp($this->getNextPageToken(), Page::FINAL_PAGE_TOKEN) != 0;
    }

    /**
     * Returns the next page token from the response.
     */
    public function getNextPageToken()
    {
        $responsePageTokenField = $this->pageStreamingDescriptor->getResponsePageTokenField();
        return $this->getResponseObject()->$responsePageTokenField;
    }

    /**
     * Retrieves the next Page object using the next page token.
     */
    public function getNextPage($pageSize = null)
    {
        if (!$this->hasNextPage()) {
            throw new ValidationException(
                'Could not complete getNextPage operation: ' .
                'there are no more pages to retrieve.'
            );
        }

        $newRequest = clone $this->getRequestObject();

        $requestPageTokenField = $this->pageStreamingDescriptor->getRequestPageTokenField();
        $newRequest->$requestPageTokenField = $this->getNextPageToken();

        if (isset($pageSize)) {
            if (!$this->pageStreamingDescriptor->requestHasPageSizeField()) {
                throw new ValidationException(
                    'pageSize argument was defined, but the method does not ' .
                    'support a page size parameter in the optional array argument'
                );
            }
            $requestPageSizeField = $this->pageStreamingDescriptor->getRequestPageSizeField();
            $newRequest->$requestPageSizeField = $pageSize;
        }

        $nextParameters = [$newRequest, $this->parameters[1], $this->parameters[2]];

        return new Page($nextParameters, $this->callable, $this->pageStreamingDescriptor);
    }

    /**
     * Return the number of elements in the response.
     */
    public function getPageElementCount()
    {
        $resourceField = $this->pageStreamingDescriptor->getResourceField();
        return count($this->getResponseObject()->$resourceField);
    }

    /**
     * Return an iterator over the elements in the response.
     */
    public function getIterator()
    {
        $resourceField = $this->pageStreamingDescriptor->getResourceField();
        foreach ($this->getResponseObject()->$resourceField as $element) {
            yield $element;
        }
    }

    /**
     * Return an iterator over Page objects, beginning with this object.
     * Additional Page objects are retrieved lazily via API calls until
     * all elements have been retrieved.
     */
    public function iteratePages()
    {
        $currentPage = $this;
        yield $this;
        while ($currentPage->hasNextPage()) {
            $currentPage = $currentPage->getNextPage();
            yield $currentPage;
        }
    }

    /**
     * Gets the request object used to generate the Page.
     */
    public function getRequestObject()
    {
        return $this->parameters[0];
    }

    /**
     * Gets the API response object.
     */
    public function getResponseObject()
    {
        return $this->response;
    }
}
