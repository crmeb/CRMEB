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

use Google\Protobuf\Internal\Message;
use SebastianBergmann\Exporter\Exporter;

/**
 * @internal
 */
class MessageAwareExporter extends Exporter
{
    /**
     * Exports a value into a single-line string
     *
     * @param mixed $value
     * @return string
     *
     * @see    \SebastianBergmann\Exporter\Exporter::export
     */
    public function shortenedExport($value)
    {
        if (\is_object($value) && $value instanceof Message) {
            return \sprintf(
                '%s Object (%s)',
                \get_class($value),
                \spl_object_hash($value)
            );
        }
        return parent::shortenedExport($value);
    }
}
