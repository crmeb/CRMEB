<?php
/**
 * Dot - PHP dot notation access to arrays
 *
 * @author  Riku Särkinen <riku@adbar.io>
 * @link    https://github.com/adbario/php-dot-notation
 * @license https://github.com/adbario/php-dot-notation/blob/2.x/LICENSE.md (MIT License)
 */

use Adbar\Dot;

if (! function_exists('dot')) {
    /**
     * Create a new Dot object with the given items
     *
     * @param  mixed $items
     * @return \Adbar\Dot
     */
    function dot($items)
    {
        return new Dot($items);
    }
}
