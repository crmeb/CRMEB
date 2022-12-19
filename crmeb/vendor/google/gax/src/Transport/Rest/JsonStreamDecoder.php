<?php
/*
 * Copyright 2021 Google LLC
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

namespace Google\ApiCore\Transport\Rest;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

class JsonStreamDecoder
{
    const ESCAPE_CHAR = '\\';

    /**
     * @var StreamInterface
     */
    private $stream;

    /**
     * @var bool
     */
    private $closeCalled = false;

    /**
     * @var string
     */
    private $decodeType;

    /**
     * @var bool
     */
    private $ignoreUnknown = true;

    /**
     * @var int
     */
    private $readChunkSize = 1024;

    /**
     * JsonStreamDecoder is a HTTP-JSON response stream decoder for JSON-ecoded
     * protobuf messages. The response stream must be a JSON array, where the first
     * byte is the opening of the array (i.e. '['), and the last byte is the closing
     * of the array (i.e. ']'). Each array item must be a JSON object and comma
     * separated.
     *
     * @param StreamInterface $stream The stream to decode.
     * @param string $decodeType The type name of response messages to decode.
     * @param array<mixed> $options {
     *     An array of optional arguments.
     *
     *     @type bool $ignoreUnknown
     *           Toggles whether or not to throw an exception when an unknown field
     *           is encountered in a response message. The default is true.
     *     @type int $readChunkSizeBytes
     *           The upper size limit in bytes that can be read at a time from the
     *           response stream. The default is 1 KB.
     * }
     *
     * @experimental
     */
    public function __construct(StreamInterface $stream, string $decodeType, array $options = [])
    {
        $this->stream = $stream;
        $this->decodeType = $decodeType;

        if (isset($options['ignoreUnknown'])) {
            $this->ignoreUnknown = $options['ignoreUnknown'];
        }
        if (isset($options['readChunkSize'])) {
            $this->readChunkSize = $options['readChunkSizeBytes'];
        }
    }

    /**
     * Begins decoding the configured response stream. It is a generator which
     * yields messages of the given decode type from the stream until the stream
     * completes. Throws an Exception if the stream is closed before the closing
     * byte is read or if it encounters an error while decoding a message.
     *
     * @throws RuntimeException
     * @return \Generator
     */
    public function decode()
    {
        try {
            foreach ($this->_decode() as $response) {
                yield $response;
            }
        } catch (RuntimeException $re) {
            $msg = $re->getMessage();
            $streamClosedException =
                strpos($msg, 'Stream is detached') !== false ||
                strpos($msg, 'Unexpected stream close') !== false;

            // Only throw the exception if close() was not called and it was not
            // a closing-related exception.
            if (!$this->closeCalled || !$streamClosedException) {
                throw $re;
            }
        }
    }

    /**
     * @return \Generator
     */
    private function _decode()
    {
        $decodeType = $this->decodeType;
        $str = false;
        $prev = $chunk = '';
        $start = $end = $cursor = $level = 0;
        while ($chunk !== '' || !$this->stream->eof()) {
            // Read up to $readChunkSize bytes from the stream.
            $chunk .= $this->stream->read($this->readChunkSize);

            // If the response stream has been closed and the only byte
            // remaining is the closing array bracket, we are done.
            if ($this->stream->eof() && $chunk === ']') {
                $level--;
                break;
            }

            // Parse the freshly read data available in $chunk.
            $chunkLength = strlen($chunk);
            while ($cursor < $chunkLength) {
                // Access the next byte for processing.
                $b = $chunk[$cursor];

                // Track open/close double quotes of a key or value. Do not
                // toggle flag with the pervious byte was an escape character.
                if ($b === '"' && $prev !== self::ESCAPE_CHAR) {
                    $str = !$str;
                }

                // Skip over new lines that break up items.
                if ($b === "\n" && $level === 1) {
                    $start++;
                }

                // Ignore commas separating messages in the stream array.
                if ($b === ',' && $level === 1) {
                    $start++;
                }
                // Track the opening of a new array or object if not in a string
                // value.
                if (($b === '{' || $b === '[') && !$str) {
                    $level++;
                    // Opening of the array/root object.
                    // Do not include it in the messageBuffer.
                    if ($level === 1) {
                        $start++;
                    }
                }
                // Track the closing of an object if not in a string value.
                if ($b === '}' && !$str) {
                    $level--;
                    if ($level === 1) {
                        $end = $cursor+1;
                    }
                }
                // Track the closing of an array if not in a string value.
                if ($b === ']' && !$str) {
                    $level--;
                    // If we are seeing a closing square bracket at the
                    // response message level, e.g. {"foo], there is a problem.
                    if ($level === 1) {
                        throw new \RuntimeException('Received closing byte mid-message');
                    }
                }

                // A message-closing byte was just buffered. Decode the
                // message with the decode type, clearing the messageBuffer,
                // and yield it.
                //
                // TODO(noahdietz): Support google.protobuf.*Value messages that
                // are encoded as primitives and separated by commas.
                if ($end !== 0) {
                    $length = $end - $start;
                    /** @var \Google\Protobuf\Internal\Message $return */
                    $return = new $decodeType();
                    $return->mergeFromJsonString(
                        substr($chunk, $start, $length),
                        $this->ignoreUnknown
                    );
                    yield $return;

                    // Dump the part of the chunk used for parsing the message
                    // and use the remaining for the next message.
                    $remaining = $chunkLength-$length;
                    $chunk = substr($chunk, $end, $remaining);

                    // Reset all indices and exit chunk processing.
                    $start = 0;
                    $end = 0;
                    $cursor = 0;
                    break;
                }

                $cursor++;

                // An escaped back slash should not escape the following character.
                if ($b === self::ESCAPE_CHAR && $prev === self::ESCAPE_CHAR) {
                    $b = '';
                }
                $prev = $b;
            }
            // If after attempting to process the chunk, no progress was made and the
            // stream is closed, we must break as the stream has closed prematurely.
            if ($cursor === $chunkLength && $this->stream->eof()) {
                break;
            }
        }
        if ($level > 0) {
            throw new \RuntimeException('Unexpected stream close before receiving the closing byte');
        }
    }

    /**
     * Closes the underlying stream. If the stream is actively being decoded, an
     * exception will not be thrown due to the interruption.
     *
     * @return void
     */
    public function close()
    {
        $this->closeCalled = true;
        $this->stream->close();
    }
}
