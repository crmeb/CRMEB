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
 * Represents a segment in a resource template. This is used internally by RelativeResourceTemplate,
 * but is not intended for public use and may change without notice.
 *
 * @internal
 */
class Segment
{
    const LITERAL_SEGMENT = 0;
    const WILDCARD_SEGMENT = 1;
    const DOUBLE_WILDCARD_SEGMENT = 2;
    const VARIABLE_SEGMENT = 3;

    /** @var int */
    private $segmentType;

    /** @var string|null */
    private $value;

    /** @var string|null */
    private $key;

    /** @var RelativeResourceTemplate|null */
    private $template;

    /** @var string */
    private $stringRepr;

    /** @var string */
    private $separator;

    /**
     * Segment constructor.
     * @param int $segmentType
     * @param string|null $value
     * @param string|null $key
     * @param RelativeResourceTemplate|null $template
     * @param string $separator The separator that belongs at the end of a segment. Ending segments should use '/'.
     * @throws ValidationException
     */
    public function __construct(
        int $segmentType,
        string $value = null,
        string $key = null,
        RelativeResourceTemplate $template = null,
        string $separator = '/'
    ) {
        $this->segmentType = $segmentType;
        $this->value = $value;
        $this->key = $key;
        $this->template = $template;
        $this->separator = $separator;

        switch ($this->segmentType) {
            case Segment::LITERAL_SEGMENT:
                $this->stringRepr = "{$this->value}";
                break;
            case Segment::WILDCARD_SEGMENT:
                $this->stringRepr = "*";
                break;
            case Segment::DOUBLE_WILDCARD_SEGMENT:
                $this->stringRepr = "**";
                break;
            case Segment::VARIABLE_SEGMENT:
                $this->stringRepr = "{{$this->key}={$this->template}}";
                break;
            default:
                throw new ValidationException(
                    "Unexpected Segment type: {$this->segmentType}"
                );
        }
    }

    /**
     * @return string A string representation of the segment.
     */
    public function __toString()
    {
        return $this->stringRepr;
    }

    /**
     * Checks if $value matches this Segment.
     *
     * @param string $value
     * @return bool
     * @throws ValidationException
     */
    public function matches(string $value)
    {
        switch ($this->segmentType) {
            case Segment::LITERAL_SEGMENT:
                return $this->value === $value;
            case Segment::WILDCARD_SEGMENT:
                return self::isValidBinding($value);
            case Segment::DOUBLE_WILDCARD_SEGMENT:
                return self::isValidDoubleWildcardBinding($value);
            case Segment::VARIABLE_SEGMENT:
                return $this->template->matches($value);
            default:
                throw new ValidationException(
                    "Unexpected Segment type: {$this->segmentType}"
                );
        }
    }

    /**
     * @return int
     */
    public function getSegmentType()
    {
        return $this->segmentType;
    }

    /**
     * @return string|null
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return RelativeResourceTemplate|null
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * Check if $binding is a valid segment binding. Segment bindings may contain any characters
     * except a forward slash ('/'), and may not be empty.
     *
     * @param string $binding
     * @return bool
     */
    private static function isValidBinding(string $binding)
    {
        return preg_match("-^[^/]+$-", $binding) === 1;
    }

    /**
     * Check if $binding is a valid double wildcard binding. Segment bindings may contain any
     * characters, but may not be empty.
     *
     * @param string $binding
     * @return bool
     */
    private static function isValidDoubleWildcardBinding(string $binding)
    {
        return preg_match("-^.+$-", $binding) === 1;
    }
}
