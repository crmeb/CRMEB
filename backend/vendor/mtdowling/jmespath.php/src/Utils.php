<?php
namespace JmesPath;

class Utils
{
    public static $typeMap = [
        'boolean' => 'boolean',
        'string'  => 'string',
        'NULL'    => 'null',
        'double'  => 'number',
        'float'   => 'number',
        'integer' => 'number'
    ];

    /**
     * Returns true if the value is truthy
     *
     * @param mixed $value Value to check
     *
     * @return bool
     */
    public static function isTruthy($value)
    {
        if (!$value) {
            return $value === 0 || $value === 0.0 || $value === '0';
        } elseif ($value instanceof \stdClass) {
            return (bool) get_object_vars($value);
        } else {
            return true;
        }
    }

    /**
     * Gets the JMESPath type equivalent of a PHP variable.
     *
     * @param mixed $arg PHP variable
     * @return string Returns the JSON data type
     * @throws \InvalidArgumentException when an unknown type is given.
     */
    public static function type($arg)
    {
        $type = gettype($arg);
        if (isset(self::$typeMap[$type])) {
            return self::$typeMap[$type];
        } elseif ($type === 'array') {
            if (empty($arg)) {
                return 'array';
            }
            reset($arg);
            return key($arg) === 0 ? 'array' : 'object';
        } elseif ($arg instanceof \stdClass) {
            return 'object';
        } elseif ($arg instanceof \Closure) {
            return 'expression';
        } elseif ($arg instanceof \ArrayAccess
            && $arg instanceof \Countable
        ) {
            return count($arg) == 0 || $arg->offsetExists(0)
                ? 'array'
                : 'object';
        } elseif (is_object($arg)) {
            if (method_exists($arg, '__toString')) {
                return 'string';
            }

            throw new \InvalidArgumentException(
                'Unable to determine JMESPath type from ' . get_class($arg)
            );
        }

        throw new \InvalidArgumentException(
            'Unable to determine JMESPath type from ' . gettype($arg)
        );
    }

    /**
     * Determine if the provided value is a JMESPath compatible object.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isObject($value)
    {
        if (is_array($value)) {
            return !$value || array_keys($value)[0] !== 0;
        }

        // Handle array-like values. Must be empty or offset 0 does not exist
        return $value instanceof \Countable && $value instanceof \ArrayAccess
            ? count($value) == 0 || !$value->offsetExists(0)
            : $value instanceof \stdClass;
    }

    /**
     * Determine if the provided value is a JMESPath compatible array.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isArray($value)
    {
        if (is_array($value)) {
            return !$value || array_keys($value)[0] === 0;
        }

        // Handle array-like values. Must be empty or offset 0 exists.
        return $value instanceof \Countable && $value instanceof \ArrayAccess
            ? count($value) == 0 || $value->offsetExists(0)
            : false;
    }

    /**
     * JSON-semantic equality: one number type, structural comparison for arrays
     * and objects, and no object key-order sensitivity.
     * Empty arrays and empty objects compare equal because PHP cannot represent
     * that distinction after associative JSON decoding.
     *
     * @param mixed $a First value to compare
     * @param mixed $b Second value to compare
     *
     * @return bool
     */
    public static function isEqual($a, $b)
    {
        $typeA = self::type($a);
        $typeB = self::type($b);

        if ($typeA !== $typeB) {
            return ($typeA === 'array' || $typeA === 'object')
                && ($typeB === 'array' || $typeB === 'object')
                && (array) $a === []
                && (array) $b === [];
        }

        if ($typeA === 'number') {
            return $a == $b;
        }

        if ($typeA === 'array' || $typeA === 'object') {
            $a = (array) $a;
            $b = (array) $b;

            if (count($a) !== count($b)) {
                return false;
            }

            foreach ($a as $key => $value) {
                if (!array_key_exists($key, $b) || !self::isEqual($value, $b[$key])) {
                    return false;
                }
            }

            return true;
        }

        return $a === $b;
    }

    /**
     * Safely add together two values.
     *
     * @param mixed $a First value to add
     * @param mixed $b Second value to add
     *
     * @return int|float
     */
    public static function add($a, $b)
    {
        if (is_numeric($a)) {
            if (is_numeric($b)) {
                return $a + $b;
            } else {
                return $a;
            }
        } else {
            if (is_numeric($b)) {
                return $b;
            } else {
                return 0;
            }
        }
    }

    /**
     * JMESPath requires a stable sorting algorithm, so here we'll implement
     * a simple Schwartzian transform that uses array index positions as tie
     * breakers.
     *
     * @param array    $data   List or map of data to sort
     * @param callable $sortFn Callable used to sort values
     *
     * @return array Returns the sorted array
     * @link https://en.wikipedia.org/wiki/Schwartzian_transform
     */
    public static function stableSort(array $data, callable $sortFn)
    {
        // Decorate each item by creating an array of [value, index]
        array_walk($data, function (&$v, $k) {
            $v = [$v, $k];
        });
        // Sort by the sort function and use the index as a tie-breaker
        uasort($data, function ($a, $b) use ($sortFn) {
            return $sortFn($a[0], $b[0]) ?: ($a[1] < $b[1] ? -1 : 1);
        });

        // Undecorate each item and return the resulting sorted array
        return array_map(function ($v) {
            return $v[0];
        }, array_values($data));
    }

    /**
     * Creates a Python-style slice of a string or array.
     *
     * @param array|string $value Value to slice
     * @param int|null     $start Starting position
     * @param int|null     $stop  Stop position
     * @param int          $step  Step (1, 2, -1, -2, etc.)
     *
     * @return array|string
     * @throws \InvalidArgumentException
     */
    public static function slice($value, $start = null, $stop = null, $step = 1)
    {
        if (!is_string($value) && !self::isArray($value)) {
            throw new \InvalidArgumentException('Expects string or array');
        }

        return self::sliceIndices($value, $start, $stop, $step);
    }

    private static function adjustEndpoint($length, $endpoint, $step)
    {
        if ($endpoint < 0) {
            $endpoint += $length;
            if ($endpoint < 0) {
                $endpoint = $step < 0 ? -1 : 0;
            }
        } elseif ($endpoint >= $length) {
            $endpoint = $step < 0 ? $length - 1 : $length;
        }

        return $endpoint;
    }

    private static function adjustSlice($length, $start, $stop, $step)
    {
        if ($step === null) {
            $step = 1;
        } elseif ($step === 0) {
            throw new \RuntimeException('step cannot be 0');
        }

        if ($start === null) {
            $start = $step < 0 ? $length - 1 : 0;
        } else {
            $start = self::adjustEndpoint($length, $start, $step);
        }

        if ($stop === null) {
            $stop = $step < 0 ? -1 : $length;
        } else {
            $stop = self::adjustEndpoint($length, $stop, $step);
        }

        return [$start, $stop, $step];
    }

    private static function sliceIndices($subject, $start, $stop, $step)
    {
        $type = gettype($subject);
        if ($type == 'string') {
            $subject = mb_str_split($subject, 1, 'UTF-8');
        }
        $len = count($subject);
        list($start, $stop, $step) = self::adjustSlice($len, $start, $stop, $step);

        $result = [];
        if ($step > 0) {
            for ($i = $start; $i < $stop; $i += $step) {
                $result[] = $subject[$i];
            }
        } else {
            for ($i = $start; $i > $stop; $i += $step) {
                $result[] = $subject[$i];
            }
        }

        return $type == 'string' ? implode('', $result) : $result;
    }
}
