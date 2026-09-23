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

use Google\GAX\Jison\Parser as BaseParser;
use Google\GAX\Jison\Segment;
use Exception;

class Parser
{
    private $parser;
    private $segmentCount;

    public function __construct()
    {
        $this->parser = new BaseParser();
    }
    /**
     * Returns an array of path template segments parsed from data.
     *
     * @param string $data A path template string
     *
     * @throws ValidationException when $data cannot be parsed
     *
     * @return array An array of Segment
     */
    public function parse($data)
    {
        try {
            Segment::resetSegmentCount();
            Segment::resetBindingCount();
            $segments = $this->parser->parse($data);
            $this->segmentCount = Segment::getSegmentCount();
            // Validation step: checks that there are no nested bindings.
            $pathWildcard = false;
            foreach ($segments as $segment) {
                if ($segment->kind == Segment::TERMINAL &&
                        $segment->literal == '**') {
                    if ($pathWildcard) {
                        throw new ValidationException(
                            'validation error: path template cannot contain '.
                            'more than one path wildcard'
                        );
                    }
                    $pathWildcard = true;
                }
            }
            return $segments;
        } catch (Exception $e) {
            throw new ValidationException('Exception in parser', 0, $e);
        }
    }

    public function getSegmentCount()
    {
        return $this->segmentCount;
    }
}
