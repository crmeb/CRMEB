<?php
// @codingStandardsIgnoreStart
// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

namespace Qiniu\Enum;

use MyCLabs\Enum\Enum;

if (method_exists("MyCLabs\\Enum\\Enum", "from")) {
    abstract class QiniuEnum extends Enum
    {
        // @codingStandardsIgnoreEnd
        // @codingStandardsIgnoreStart
    }
} else {
    /**
     * poly fill MyCLabs\Enum\Enum::from in low version
     *
     * @link https://github.com/myclabs/php-enum
     */
    abstract class QiniuEnum extends Enum
    {
        // @codingStandardsIgnoreEnd
        /**
         * @param mixed $value
         * @return static
         */
        public static function from($value)
        {
            $key = self::assertValidValueReturningKey($value);

            return self::__callStatic($key, array());
        }

        /**
         * Asserts valid enum value
         *
         * @psalm-pure
         * @psalm-assert T $value
         * @param mixed $value
         * @return string
         */
        private static function assertValidValueReturningKey($value)
        {
            if (false === ($key = self::search($value))) {
                throw new \UnexpectedValueException("Value '$value' is not part of the enum " . __CLASS__);
            }

            return $key;
        }
        // @codingStandardsIgnoreStart
    }
}
