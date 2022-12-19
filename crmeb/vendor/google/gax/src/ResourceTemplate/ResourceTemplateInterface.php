<?php
/*
 * Copyright 2018 Google LLC
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

namespace Google\ApiCore\ResourceTemplate;

use Google\ApiCore\ValidationException;

/**
 * Represents a resource template that may or may not contain a leading slash, and if a leading
 * slash is present may contain a trailing verb (":<verb>"). (Note that a trailing verb without a
 * leading slash is not permitted).
 *
 * Examples:
 *   projects
 *   /projects
 *   foo/{bar=**}/fizz/*
 *   /foo/{bar=**}/fizz/*:action
 *
 * Templates use the syntax of the API platform; see
 * https://github.com/googleapis/api-common-protos/blob/master/google/api/http.proto
 * for details. A template consists of a sequence of literals, wildcards, and variable bindings,
 * where each binding can have a sub-path. A string representation can be parsed into an
 * instance of AbsoluteResourceTemplate, which can then be used to perform matching and instantiation.
 *
 * @internal
 */
interface ResourceTemplateInterface
{
    /**
     * @return string A string representation of the resource template
     */
    public function __toString();

    /**
     * Renders a resource template using the provided bindings.
     *
     * @param array $bindings An array matching var names to binding strings.
     * @return string A rendered representation of this resource template.
     * @throws ValidationException If $bindings does not contain all required keys
     *         or if a sub-template can't be parsed.
     */
    public function render(array $bindings);

    /**
     * Check if $path matches a resource string.
     *
     * @param string $path A resource string.
     * @return bool
     */
    public function matches(string $path);

    /**
     * Matches a given $path to a resource template, and returns an array of bindings between
     * wildcards / variables in the template and values in the path. If $path does not match the
     * template, then a ValidationException is thrown.
     *
     * @param string $path A resource string.
     * @throws ValidationException if path can't be matched to the template.
     * @return array Array matching var names to binding values.
     */
    public function match(string $path);
}
