<?php
namespace JmesPath;

/**
 * Dispatches to named JMESPath functions using a single function that has the
 * following signature:
 *
 *     mixed $result = fn(string $function_name, array $args)
 */
class FnDispatcher
{
    /**
     * Gets a cached instance of the default function implementations.
     *
     * @return FnDispatcher
     */
    public static function getInstance()
    {
        static $instance = null;
        if (!$instance) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * @param string $fn   Function name.
     * @param array  $args Function arguments.
     *
     * @return mixed
     */
    public function __invoke($fn, array $args)
    {
        return $this->{'fn_' . $fn}($args);
    }

    private function fn_abs(array $args)
    {
        $this->validate('abs', $args, [['number']]);
        return abs($args[0]);
    }

    private function fn_avg(array $args)
    {
        $this->validate('avg', $args, [['array']]);
        $sum = $this->reduce('avg:0', $args[0], ['number'], function ($a, $b) {
            return Utils::add($a, $b);
        });
        return $args[0] ? ($sum / count($args[0])) : null;
    }

    private function fn_ceil(array $args)
    {
        $this->validate('ceil', $args, [['number']]);
        return ceil($args[0]);
    }

    private function fn_contains(array $args)
    {
        $this->validate('contains', $args, [['string', 'array'], ['any']]);
        if (is_array($args[0])) {
            foreach ($args[0] as $value) {
                if (Utils::isEqual($value, $args[1])) {
                    return true;
                }
            }

            return false;
        }

        return is_string($args[1])
            ? mb_strpos($args[0], $args[1], 0, 'UTF-8') !== false
            : false;
    }

    private function fn_ends_with(array $args)
    {
        $this->validate('ends_with', $args, [['string'], ['string']]);
        list($search, $suffix) = $args;
        return $suffix === '' || mb_substr($search, -mb_strlen($suffix, 'UTF-8'), null, 'UTF-8') === $suffix;
    }

    private function fn_floor(array $args)
    {
        $this->validate('floor', $args, [['number']]);
        return floor($args[0]);
    }

    private function fn_not_null(array $args)
    {
        if (!$args) {
            throw new \RuntimeException(
                "not_null() expects 1 or more arguments, 0 were provided"
            );
        }

        return array_reduce($args, function ($carry, $item) {
            return $carry !== null ? $carry : $item;
        });
    }

    private function fn_join(array $args)
    {
        $this->validate('join', $args, [['string'], ['array']]);
        $fn = function ($a, $b, $i) use ($args) {
            return $i ? ($a . $args[0] . $b) : $b;
        };
        return $args[1] ? $this->reduce('join:0', $args[1], ['string'], $fn) : '';
    }

    private function fn_keys(array $args)
    {
        $this->validate('keys', $args, [['object']]);
        return array_keys((array) $args[0]);
    }

    private function fn_length(array $args)
    {
        $this->validate('length', $args, [['string', 'array', 'object']]);
        return is_string($args[0]) ? mb_strlen($args[0], 'UTF-8') : count((array) $args[0]);
    }

    private function fn_max(array $args)
    {
        $this->validate('max', $args, [['array']]);
        $fn = function ($a, $b, $i) {
            return $i && self::compareValues($a, $b) >= 0 ? $a : $b;
        };
        return $this->reduce('max:0', $args[0], ['number', 'string'], $fn);
    }

    private function fn_max_by(array $args)
    {
        $this->validate('max_by', $args, [['array'], ['expression']]);
        $expr = $this->wrapExpression('max_by:1', $args[1], ['number', 'string']);
        $carryKey = null;
        $fn = function ($carry, $item, $index) use ($expr, &$carryKey) {
            if (!$index) {
                return $item;
            }
            if ($index === 1) {
                $carryKey = $expr($carry);
            }
            $itemKey = $expr($item);
            $this->validateSeq('max_by:0', ['number', 'string'], $carryKey, $itemKey);
            if (self::compareValues($carryKey, $itemKey) >= 0) {
                return $carry;
            }
            $carryKey = $itemKey;
            return $item;
        };
        return $this->reduce('max_by:1', $args[0], ['any'], $fn);
    }

    private function fn_min(array $args)
    {
        $this->validate('min', $args, [['array']]);
        $fn = function ($a, $b, $i) {
            return $i && self::compareValues($a, $b) <= 0 ? $a : $b;
        };
        return $this->reduce('min:0', $args[0], ['number', 'string'], $fn);
    }

    private function fn_min_by(array $args)
    {
        $this->validate('min_by', $args, [['array'], ['expression']]);
        $expr = $this->wrapExpression('min_by:1', $args[1], ['number', 'string']);
        $carryKey = null;
        $fn = function ($carry, $item, $index) use ($expr, &$carryKey) {
            if (!$index) {
                return $item;
            }
            if ($index === 1) {
                $carryKey = $expr($carry);
            }
            $itemKey = $expr($item);
            $this->validateSeq('min_by:0', ['number', 'string'], $carryKey, $itemKey);
            if (self::compareValues($carryKey, $itemKey) <= 0) {
                return $carry;
            }
            $carryKey = $itemKey;
            return $item;
        };
        return $this->reduce('min_by:1', $args[0], ['any'], $fn);
    }

    private function fn_reverse(array $args)
    {
        $this->validate('reverse', $args, [['array', 'string']]);
        if (is_array($args[0])) {
            return array_reverse($args[0]);
        } elseif (is_string($args[0])) {
            return implode('', array_reverse(mb_str_split($args[0], 1, 'UTF-8')));
        } else {
            throw new \RuntimeException('Cannot reverse provided argument');
        }
    }

    private function fn_sum(array $args)
    {
        $this->validate('sum', $args, [['array']]);
        $fn = function ($a, $b) {
            return Utils::add($a, $b);
        };
        return $args[0] ? $this->reduce('sum:0', $args[0], ['number'], $fn) : 0;
    }

    private function fn_sort(array $args)
    {
        $this->validate('sort', $args, [['array']]);
        $valid = ['string', 'number'];
        return Utils::stableSort($args[0], function ($a, $b) use ($valid) {
            $this->validateSeq('sort:0', $valid, $a, $b);
            return self::compareValues($a, $b);
        });
    }

    private function fn_sort_by(array $args)
    {
        $this->validate('sort_by', $args, [['array'], ['expression']]);
        $expr = $args[1];
        $valid = ['string', 'number'];
        return Utils::stableSort(
            $args[0],
            function ($a, $b) use ($expr, $valid) {
                $va = $expr($a);
                $vb = $expr($b);
                $this->validateSeq('sort_by:0', $valid, $va, $vb);
                return self::compareValues($va, $vb);
            }
        );
    }

    private function fn_starts_with(array $args)
    {
        $this->validate('starts_with', $args, [['string'], ['string']]);
        list($search, $prefix) = $args;
        return $prefix === '' || mb_strpos($search, $prefix, 0, 'UTF-8') === 0;
    }

    private function fn_type(array $args)
    {
        $this->validateArity('type', count($args), 1);
        return Utils::type($args[0]);
    }

    private function fn_to_string(array $args)
    {
        $this->validateArity('to_string', count($args), 1);
        $v = $args[0];
        if (is_string($v)) {
            return $v;
        } elseif (is_object($v)
            && !($v instanceof \JsonSerializable)
            && method_exists($v, '__toString')
        ) {
            return (string) $v;
        }

        return json_encode($v);
    }

    private function fn_to_number(array $args)
    {
        $this->validateArity('to_number', count($args), 1);
        $value = $args[0];

        if (Utils::type($value) == 'number') {
            return $value;
        }

        if (!is_string($value)) {
            return null;
        }

        return $this->parseJsonNumber($value);
    }

    /**
     * Parses a string conforming to the JSON number grammar (RFC 8259) into
     * an int when exactly representable, otherwise a float. Returns null for
     * non-conforming or non-finite input.
     */
    private function parseJsonNumber($value)
    {
        if (!preg_match('/^-?(?:0|[1-9][0-9]*)(?:\.[0-9]+)?(?:[eE][+-]?[0-9]+)?$/D', $value)) {
            return null;
        }

        if (preg_match('/^-?(?:0|[1-9][0-9]*)$/D', $value)) {
            return $this->parseJsonInteger($value);
        }

        $number = (float) $value;

        return is_finite($number) ? $number : null;
    }

    private function parseJsonInteger($value)
    {
        $negative = $value[0] === '-';
        $digits = $negative ? substr($value, 1) : $value;
        $limit = $negative ? substr((string) PHP_INT_MIN, 1) : (string) PHP_INT_MAX;

        if (strlen($digits) < strlen($limit)
            || (strlen($digits) === strlen($limit) && strcmp($digits, $limit) <= 0)
        ) {
            return (int) $value;
        }

        $number = (float) $value;

        return is_finite($number) ? $number : null;
    }

    private function fn_values(array $args)
    {
        $this->validate('values', $args, [['array', 'object']]);
        return array_values((array) $args[0]);
    }

    private function fn_merge(array $args)
    {
        if (!$args) {
            throw new \RuntimeException(
                "merge() expects 1 or more arguments, 0 were provided"
            );
        }

        return call_user_func_array('array_replace', $args);
    }

    private function fn_to_array(array $args)
    {
        $this->validate('to_array', $args, [['any']]);

        return Utils::isArray($args[0]) ? $args[0] : [$args[0]];
    }

    private function fn_map(array $args)
    {
        $this->validate('map', $args, [['expression'], ['array']]);
        $result = [];
        foreach ($args[1] as $a) {
            $result[] = $args[0]($a);
        }
        return $result;
    }

    private function typeError($from, $msg)
    {
        if (mb_strpos($from, ':', 0, 'UTF-8')) {
            list($fn, $pos) = explode(':', $from);
            throw new \RuntimeException(
                sprintf('Argument %d of %s %s', $pos, $fn, $msg)
            );
        } else {
            throw new \RuntimeException(
                sprintf('Type error: %s %s', $from, $msg)
            );
        }
    }

    private function validateArity($from, $given, $expected)
    {
        if ($given != $expected) {
            $err = "%s() expects {$expected} arguments, {$given} were provided";
            throw new \RuntimeException(sprintf($err, $from));
        }
    }

    private function validate($from, $args, $types = [])
    {
        $this->validateArity($from, count($args), count($types));
        foreach ($args as $index => $value) {
            if (!isset($types[$index]) || !$types[$index]) {
                continue;
            }
            $this->validateType("{$from}:{$index}", $value, $types[$index]);
        }
    }

    private function validateType($from, $value, array $types)
    {
        if ($types[0] == 'any'
            || in_array(Utils::type($value), $types)
            || ($value === [] && in_array('object', $types))
        ) {
            return;
        }
        $msg = 'must be one of the following types: ' . implode(', ', $types)
            . '. ' . Utils::type($value) . ' found';
        $this->typeError($from, $msg);
    }

    /**
     * Validates value A and B, ensures they both are correctly typed, and of
     * the same type.
     *
     * @param string   $from   String of function:argument_position
     * @param array    $types  Array of valid value types.
     * @param mixed    $a      Value A
     * @param mixed    $b      Value B
     */
    private function validateSeq($from, array $types, $a, $b)
    {
        $ta = Utils::type($a);
        $tb = Utils::type($b);

        if ($ta !== $tb) {
            $msg = "encountered a type mismatch in sequence: {$ta}, {$tb}";
            $this->typeError($from, $msg);
        }

        $typeMatch = ($types && $types[0] == 'any') || in_array($ta, $types);
        if (!$typeMatch) {
            $msg = 'encountered a type error in sequence. The argument must be '
                . 'an array of ' . implode('|', $types) . ' types. '
                . "Found {$ta}, {$tb}.";
            $this->typeError($from, $msg);
        }
    }

    /**
     * Compares two values of the same JMESPath type.
     *
     * @param mixed $a Value A
     * @param mixed $b Value B
     *
     * @return int Negative if $a < $b, zero if equal, positive if $a > $b.
     */
    private static function compareValues($a, $b)
    {
        return Utils::type($a) === 'string'
            ? strcmp((string) $a, (string) $b)
            : ($a <=> $b);
    }

    /**
     * Reduces and validates an array of values to a single value using a fn.
     *
     * @param string   $from   String of function:argument_position
     * @param array    $values Values to reduce.
     * @param array    $types  Array of valid value types.
     * @param callable $reduce Reduce function that accepts ($carry, $item).
     *
     * @return mixed
     */
    private function reduce($from, array $values, array $types, callable $reduce)
    {
        $i = -1;
        return array_reduce(
            $values,
            function ($carry, $item) use ($from, $types, $reduce, &$i) {
                if (++$i > 0) {
                    $this->validateSeq($from, $types, $carry, $item);
                }
                return $reduce($carry, $item, $i);
            }
        );
    }

    /**
     * Validates the return values of expressions as they are applied.
     *
     * @param string   $from  Function name : position
     * @param callable $expr  Expression function to validate.
     * @param array    $types Array of acceptable return type values.
     *
     * @return callable Returns a wrapped function
     */
    private function wrapExpression($from, callable $expr, array $types)
    {
        list($fn, $pos) = explode(':', $from);
        $from = "The expression return value of argument {$pos} of {$fn}";
        return function ($value) use ($from, $expr, $types) {
            $value = $expr($value);
            $this->validateType($from, $value, $types);
            return $value;
        };
    }

    /** @internal Pass function name validation off to runtime */
    public function __call($name, $args)
    {
        $name = str_replace('fn_', '', $name);
        throw new \RuntimeException("Call to undefined function {$name}");
    }
}
