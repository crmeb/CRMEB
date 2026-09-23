<?php

namespace Aws\S3\S3Transfer\Progress;

/**
 * Defines a progress bar format.
 */
abstract class AbstractProgressBarFormat
{
    /** @var array */
    private array $args;

    /**
     * @param array $args
     */
    public function __construct(
        array $args = []
    ) {
        $this->args = $args;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * To set multiple arguments at once.
     * It does not override all the values, instead
     * it adds the arguments individually and if a value
     * already exists then that value will be overridden.
     *
     * @param array $args
     *
     * @return void
     */
    public function setArgs(array $args): void
    {
        foreach ($args as $key => $value) {
            $this->args[$key] = $value;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function setArg(string $key, mixed $value): void
    {
        $this->args[$key] = $value;
    }

    /**
     * @return string
     */
    public function format(): string
    {
        $parameters = $this->getFormatParameters();
        $defaultParameterValues = $this->getFormatDefaultParameterValues();
        foreach ($parameters as $param) {
            if (!array_key_exists($param, $this->args)) {
                $this->args[$param] = $defaultParameterValues[$param] ?? '';
            }
        }

        $replacements = [];
        foreach ($parameters as $param) {
            $replacements["|$param|"] = $this->args[$param] ?? '';
        }

        return strtr($this->getFormatTemplate(), $replacements);
    }

    /**
     * @return string
     */
    abstract public function getFormatTemplate(): string;

    /**
     * @return array
     */
    abstract public function getFormatParameters(): array;

    /**
     * @return array
     */
    abstract protected function getFormatDefaultParameterValues(): array;
}
