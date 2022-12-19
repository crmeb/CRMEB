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

namespace Google\ApiCore;

/**
 * Provides basic array helper methods.
 *
 * @internal
 */
trait ArrayTrait
{
    /**
     * Pluck a value out of an array.
     *
     * @param string $key
     * @param array $arr
     * @param bool $isRequired
     * @return mixed|null
     * @throws \InvalidArgumentException
     */
    private function pluck(string $key, array &$arr, bool $isRequired = true)
    {
        if (!array_key_exists($key, $arr)) {
            if ($isRequired) {
                throw new \InvalidArgumentException(
                    "Key $key does not exist in the provided array."
                );
            }

            return null;
        }

        $value = $arr[$key];
        unset($arr[$key]);
        return $value;
    }

    /**
     * Pluck a subset of an array.
     *
     * @param array $keys
     * @param array $arr
     * @return array
     */
    private function pluckArray(array $keys, array &$arr)
    {
        $values = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $arr)) {
                $values[$key] = $this->pluck($key, $arr, false);
            }
        }

        return $values;
    }

    /**
     * Determine whether given array is associative.
     *
     * @param array $arr
     * @return bool
     */
    private function isAssoc(array $arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Just like array_filter(), but preserves falsey values except null.
     *
     * @param array $arr
     * @return array
     */
    private function arrayFilterRemoveNull(array $arr)
    {
        return array_filter($arr, function ($element) {
            if (!is_null($element)) {
                return true;
            }

            return false;
        });
    }

    /**
     * Return a subset of an array, like pluckArray, without modifying the original array.
     *
     * @param array $keys
     * @param array $arr
     * @return array
     */
    private function subsetArray(array $keys, array $arr)
    {
        return array_intersect_key(
            $arr,
            array_flip($keys)
        );
    }
}
