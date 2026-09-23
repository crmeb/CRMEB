<?php

namespace AlibabaCloud\Tea\FileForm;

use AlibabaCloud\Tea\FileForm\FileForm\FileField;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;

/**
 * @internal
 * @coversNothing
 */
class FileFormStream implements StreamInterface
{
    /**
     * @var resource
     */
    private $stream;
    private $index     = 0;
    private $form      = [];
    private $boundary  = '';
    private $streaming = false;
    private $keys      = [];

    /**
     * @var Stream
     */
    private $currStream;

    private $size;
    private $uri;
    private $seekable;
    private $readable = true;
    private $writable = true;

    public function __construct($map, $boundary)
    {
        $this->stream   = fopen('php://memory', 'a+');
        $this->form     = $map;
        $this->boundary = $boundary;
        $this->keys     = array_keys($map);
        do {
            $read = $this->readForm(1024);
        } while (null !== $read);
        $meta           = stream_get_meta_data($this->stream);
        $this->seekable = $meta['seekable'];
        $this->uri      = $this->getMetadata('uri');
        $this->seek(0);
        $this->seek(0);
    }

    /**
     * Closes the stream when the destructed.
     */
    public function __destruct()
    {
        $this->close();
    }

    public function __toString()
    {
        try {
            $this->seek(0);

            return (string) stream_get_contents($this->stream);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param int $length
     *
     * @return false|int|string
     */
    public function readForm($length)
    {
        if ($this->streaming) {
            if (null !== $this->currStream) {
                // @var string $content
                $content = $this->currStream->read($length);
                if (false !== $content && '' !== $content) {
                    fwrite($this->stream, $content);

                    return $content;
                }

                return $this->next("\r\n");
            }

            return $this->next();
        }
        $keysCount = \count($this->keys);
        if ($this->index > $keysCount) {
            return null;
        }
        if ($keysCount > 0) {
            if ($this->index < $keysCount) {
                $this->streaming = true;

                $name  = $this->keys[$this->index];
                $field = $this->form[$name];
                if (!empty($field) && $field instanceof FileField) {
                    if (!empty($field->content)) {
                        $this->currStream = $field->content;

                        $str = '--' . $this->boundary . "\r\n" .
                            'Content-Disposition: form-data; name="' . $name . '"; filename="' . $field->filename . "\"\r\n" .
                            'Content-Type: ' . $field->contentType . "\r\n\r\n";
                        $this->write($str);

                        return $str;
                    }

                    return $this->next();
                }
                $val = $field;
                $str = '--' . $this->boundary . "\r\n" .
                    'Content-Disposition: form-data; name="' . $name . "\"\r\n\r\n" .
                    $val . "\r\n";
                fwrite($this->stream, $str);

                return $str;
            }
            if ($this->index == $keysCount) {
                return $this->next('--' . $this->boundary . "--\r\n");
            }

            return null;
        }

        return null;
    }

    public function getContents()
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        $contents = stream_get_contents($this->stream);

        if (false === $contents) {
            throw new \RuntimeException('Unable to read stream contents');
        }

        return $contents;
    }

    public function close()
    {
        if (isset($this->stream)) {
            if (\is_resource($this->stream)) {
                fclose($this->stream);
            }
            $this->detach();
        }
    }

    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }

        $result = $this->stream;
        unset($this->stream);
        $this->size = $this->uri = null;

        return $result;
    }

    public function getSize()
    {
        if (null !== $this->size) {
            return $this->size;
        }

        if (!isset($this->stream)) {
            return null;
        }

        // Clear the stat cache if the stream has a URI
        if ($this->uri) {
            clearstatcache(true, $this->uri);
        }

        $stats = fstat($this->stream);
        if (isset($stats['size'])) {
            $this->size = $stats['size'];

            return $this->size;
        }

        return null;
    }

    public function isReadable()
    {
        return $this->readable;
    }

    public function isWritable()
    {
        return $this->writable;
    }

    public function isSeekable()
    {
        return $this->seekable;
    }

    public function eof()
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        return feof($this->stream);
    }

    public function tell()
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        $result = ftell($this->stream);

        if (false === $result) {
            throw new \RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    public function rewind()
    {
        $this->seek(0);
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        $whence = (int) $whence;

        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }
        if (!$this->seekable) {
            throw new \RuntimeException('Stream is not seekable');
        }
        if (-1 === fseek($this->stream, $offset, $whence)) {
            throw new \RuntimeException('Unable to seek to stream position ' . $offset . ' with whence ' . var_export($whence, true));
        }
    }

    public function read($length)
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }
        if (!$this->readable) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }
        if ($length < 0) {
            throw new \RuntimeException('Length parameter cannot be negative');
        }

        if (0 === $length) {
            return '';
        }

        $string = fread($this->stream, $length);
        if (false === $string) {
            throw new \RuntimeException('Unable to read from stream');
        }

        return $string;
    }

    public function write($string)
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }
        if (!$this->writable) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }

        // We can't know the size after writing anything
        $this->size = null;
        $result     = fwrite($this->stream, $string);

        if (false === $result) {
            throw new \RuntimeException('Unable to write to stream');
        }

        return $result;
    }

    public function getMetadata($key = null)
    {
        if (!isset($this->stream)) {
            return $key ? null : [];
        }

        $meta = stream_get_meta_data($this->stream);

        return isset($meta[$key]) ? $meta[$key] : null;
    }

    private function next($endStr = '')
    {
        $this->streaming = false;
        ++$this->index;
        $this->write($endStr);
        $this->currStream = null;

        return $endStr;
    }
}
