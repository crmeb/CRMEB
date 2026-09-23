<?php declare(strict_types=1);

namespace TheNorthMemory\Xml;

use ArrayIterator;

/**
 * Labeled ArrayIterator with special wrapped flag
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @extends ArrayIterator<TKey,TValue>
 */
class LabeledArrayIterator extends ArrayIterator
{
    /**
     * @var string - The label, default is `item`
     */
    private $label = 'item';

    /**
     * @var boolean - The wrapped flag, default is `false`
     */
    private $wrapped = false;

    /**
     * `Label` setter
     *
     * @param string $value - the label value
     * @return self<TKey,TValue>
     */
    public function withLabel(string $value): self
    {
        $this->label = $value;

        return $this;
    }

    /**
     * `Label` getter
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * `wrapped` flag setter
     *
     * @param bool $value - the flag value, default is `true`
     * @return self<TKey,TValue>
     */
    public function wrapped(bool $value = true): self
    {
        $this->wrapped = $value;

        return $this;
    }

    /**
     * `wrapped` flag checker
     */
    public function isWrapped(): bool
    {
        return $this->wrapped === true;
    }
}
