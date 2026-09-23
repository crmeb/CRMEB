<?php

declare (strict_types = 1);

namespace think\model\type;

use Stringable;
use think\model\contract\Modelable;
use think\model\contract\Typeable;

class DateTime implements Typeable
{
    protected $data;
    protected $format;

    public static function from(mixed $value, Modelable $model)
    {
        $static = new static();
        $static->data($value, $model->getDateFormat());
        return $static;
    }

    public function data($time, $format)
    {
        if ($format) {
            if (class_exists($format)) {
                $time = $time instanceof $format ? $time : new $format($time);
                $this->format = 'Y-m-d H:i:s.u';
            } else {
                if (is_object($time)) {
                } elseif (is_numeric($time)) {
                    $time  = (new \DateTime())->setTimestamp((int) $time);
                } elseif (strpos('.', $time)) {
                    $time  = \DateTime::createFromFormat('Y-m-d H:i:s.u', $time);
                } else {
                    $time  = $time ? (new \DateTime($time)) : null;
                }
                $this->format = $format;
            }
        } 
        $this->data  = $time;
    }

    public function setFormat(string $format)
    {
        $this->format = $format;
    }

    public function format(string $format = '')
    {
        if ($this->data instanceof Stringable) {
            return $this->data->__toString();
        }

        if (is_null($this->data) || is_string($this->data)) {
            return $this->data;
        }

        return $this->data->format($format ?: $this->format);
    }

    public function value()
    {
        return $this->format();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value();
    }
}
