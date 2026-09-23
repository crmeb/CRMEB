<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Cache\Traits\Relay;

if (version_compare(phpversion('relay'), '0.9.0', '>=')) {
    /**
     * @internal
     */
    trait FtTrait
    {
        public function ftAggregate($index, $query, $options = null): \Relay\Relay|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftAggregate(...\func_get_args());
        }

        public function ftAliasAdd($index, $alias): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftAliasAdd(...\func_get_args());
        }

        public function ftAliasDel($alias): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftAliasDel(...\func_get_args());
        }

        public function ftAliasUpdate($index, $alias): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftAliasUpdate(...\func_get_args());
        }

        public function ftAlter($index, $schema, $skipinitialscan = false): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftAlter(...\func_get_args());
        }

        public function ftConfig($operation, $option, $value = null): \Relay\Relay|array|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftConfig(...\func_get_args());
        }

        public function ftCreate($index, $schema, $options = null): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftCreate(...\func_get_args());
        }

        public function ftCursor($operation, $index, $cursor, $options = null): \Relay\Relay|array|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftCursor(...\func_get_args());
        }

        public function ftDictAdd($dict, $term, ...$other_terms): \Relay\Relay|false|int
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftDictAdd(...\func_get_args());
        }

        public function ftDictDel($dict, $term, ...$other_terms): \Relay\Relay|false|int
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftDictDel(...\func_get_args());
        }

        public function ftDictDump($dict): \Relay\Relay|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftDictDump(...\func_get_args());
        }

        public function ftDropIndex($index, $dd = false): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftDropIndex(...\func_get_args());
        }

        public function ftExplain($index, $query, $dialect = 0): \Relay\Relay|false|string
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftExplain(...\func_get_args());
        }

        public function ftExplainCli($index, $query, $dialect = 0): \Relay\Relay|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftExplainCli(...\func_get_args());
        }

        public function ftInfo($index): \Relay\Relay|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftInfo(...\func_get_args());
        }

        public function ftProfile($index, $command, $query, $limited = false): \Relay\Relay|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftProfile(...\func_get_args());
        }

        public function ftSearch($index, $query, $options = null): \Relay\Relay|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftSearch(...\func_get_args());
        }

        public function ftSpellCheck($index, $query, $options = null): \Relay\Relay|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftSpellCheck(...\func_get_args());
        }

        public function ftSynDump($index): \Relay\Relay|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftSynDump(...\func_get_args());
        }

        public function ftSynUpdate($index, $synonym, $term_or_terms, $skipinitialscan = false): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftSynUpdate(...\func_get_args());
        }

        public function ftTagVals($index, $tag): \Relay\Relay|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ftTagVals(...\func_get_args());
        }
    }
} else {
    /**
     * @internal
     */
    trait FtTrait
    {
    }
}
