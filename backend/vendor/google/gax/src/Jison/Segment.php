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

namespace Google\GAX\Jison;

use ArrayObject;

/**
 * This class represents a segment in a path template.
 * We extend ArrayObject to allow property access from
 * JisonParser
 */
class Segment extends ArrayObject
{
    const BINDING = 1;
    const END_BINDING = 2;
    const TERMINAL = 3;

    public $kind;
    public $literal;

    private static $bindingCount = 0;
    private static $segmentCount = 0;

    public function __construct($kind, $literal)
    {
        $this->kind = $kind;
        $this->literal = $literal;
        $this['kind'] = $kind;
        $this['literal'] = $literal;
    }

    public static function getBindingCount()
    {
        return self::$bindingCount;
    }

    public static function incrementBindingCount()
    {
        self::$bindingCount++;
    }

    public static function resetBindingCount()
    {
        self::$bindingCount = 0;
    }

    public static function getSegmentCount()
    {
        return self::$segmentCount;
    }

    public static function incrementSegmentCount()
    {
        self::$segmentCount++;
    }

    public static function resetSegmentCount()
    {
        self::$segmentCount = 0;
    }
}
