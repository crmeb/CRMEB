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

use Generator;
use IteratorAggregate;

/**
 * Response object for paged results from a list API method
 *
 * The PagedListResponse object is returned by API methods that implement
 * pagination, and makes it easier to access multiple pages of results
 * without having to manually manipulate page tokens. Pages are retrieved
 * lazily, with additional API calls being made as additional results
 * are required.
 *
 * The list elements can be accessed in the following ways:
 *  - As a single iterable used in a foreach loop or via the getIterator method
 *  - As pages of elements, using the getPage and iteratePages methods
 *  - As fixed size collections of elements, using the
 *    getFixedSizeCollection and iterateFixedSizeCollections methods
 *
 * Example of using PagedListResponse as an iterator:
 * ```
 * $pagedListResponse = $client->getList(...);
 * foreach ($pagedListResponse as $element) {
 *     // doSomethingWith($element);
 * }
 * ```
 *
 * Example of iterating over each page of elements:
 * ```
 * $pagedListResponse = $client->getList(...);
 * foreach ($pagedListResponse->iteratePages() as $page) {
 *     foreach ($page as $element) {
 *         // doSomethingWith($element);
 *     }
 * }
 * ```
 *
 * Example of accessing the current page, and manually iterating
 * over pages:
 * ```
 * $pagedListResponse = $client->getList(...);
 * $page = $pagedListResponse->getPage();
 * // doSomethingWith($page);
 * while ($page->hasNextPage()) {
 *     $page = $page->getNextPage();
 *     // doSomethingWith($page);
 * }
 * ```
 */
class PagedListResponse implements IteratorAggregate
{
    private $firstPage;

    /**
     * PagedListResponse constructor.
     *
     * @param Page $firstPage A page containing response details.
     */
    public function __construct(
        Page $firstPage
    ) {
        $this->firstPage = $firstPage;
    }

    /**
     * Returns an iterator over the full list of elements. If the
     * API response contains a (non-empty) next page token, then
     * the PagedListResponse object will make calls to the underlying
     * API to retrieve additional elements as required.
     *
     * NOTE: The result of this method is the same as getIterator().
     * Prefer using getIterator(), or iterate directly on the
     * PagedListResponse object.
     *
     * @return Generator
     * @throws ValidationException
     */
    public function iterateAllElements()
    {
        return $this->getIterator();
    }

    /**
     * Returns an iterator over the full list of elements. If the
     * API response contains a (non-empty) next page token, then
     * the PagedListResponse object will make calls to the underlying
     * API to retrieve additional elements as required.
     *
     * @return Generator
     * @throws ValidationException
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        foreach ($this->iteratePages() as $page) {
            foreach ($page as $key => $element) {
                yield $key => $element;
            }
        }
    }

    /**
     * Return the current page of results.
     *
     * @return Page
     */
    public function getPage()
    {
        return $this->firstPage;
    }

    /**
     * Returns an iterator over pages of results. The pages are
     * retrieved lazily from the underlying API.
     *
     * @return Page[]
     * @throws ValidationException
     */
    public function iteratePages()
    {
        return $this->getPage()->iteratePages();
    }

    /**
     * Returns a collection of elements with a fixed size set by
     * the collectionSize parameter. The collection will only contain
     * fewer than collectionSize elements if there are no more
     * pages to be retrieved from the server.
     *
     * NOTE: it is an error to call this method if an optional parameter
     * to set the page size is not supported or has not been set in the
     * original API call. It is also an error if the collectionSize parameter
     * is less than the page size that has been set.
     *
     * @param int $collectionSize
     * @throws ValidationException if a FixedSizeCollection of the specified size cannot be constructed
     * @return FixedSizeCollection
     */
    public function expandToFixedSizeCollection(int $collectionSize)
    {
        return $this->getPage()->expandToFixedSizeCollection($collectionSize);
    }

    /**
     * Returns an iterator over fixed size collections of results.
     * The collections are retrieved lazily from the underlying API.
     *
     * Each collection will have collectionSize elements, with the
     * exception of the final collection which may contain fewer
     * elements.
     *
     * NOTE: it is an error to call this method if an optional parameter
     * to set the page size is not supported or has not been set in the
     * original API call. It is also an error if the collectionSize parameter
     * is less than the page size that has been set.
     *
     * @param int $collectionSize
     * @throws ValidationException if a FixedSizeCollection of the specified size cannot be constructed
     * @return Generator|FixedSizeCollection[]
     */
    public function iterateFixedSizeCollections(int $collectionSize)
    {
        return $this->expandToFixedSizeCollection($collectionSize)->iterateCollections();
    }
}
