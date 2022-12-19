<?php
/**
 * Copyright 2018 Google LLC
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
 */

namespace Google\ApiCore\Testing;

use Google\Protobuf\GPBEmpty;
use Google\Protobuf\Internal\Message;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * @internal
 */
class ProtobufGPBEmptyComparator extends Comparator
{
    /**
     * Returns whether the comparator can compare two values.
     *
     * @param mixed $expected The first value to compare
     * @param mixed $actual The second value to compare
     * @return boolean
     */
    public function accepts($expected, $actual)
    {
       return $expected instanceof GPBEmpty && $actual instanceof GPBEmpty;
    }

    /**
     * Asserts that two values are equal.
     *
     * @param Message $expected The first value to compare
     * @param Message $actual The second value to compare
     * @param float|int $delta The allowed numerical distance between two values to
     *                      consider them equal
     * @param  bool $canonicalize If set to TRUE, arrays are sorted before
     *                             comparison
     * @param  bool $ignoreCase If set to TRUE, upper- and lowercasing is
     *                           ignored when comparing string values
     * @throws ComparisonFailure Thrown when the comparison
     *                           fails. Contains information about the
     *                           specific errors that lead to the failure.
     */
    public function assertEquals($expected, $actual, $delta = 0, $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        // No need to do anything here.
    }
}
