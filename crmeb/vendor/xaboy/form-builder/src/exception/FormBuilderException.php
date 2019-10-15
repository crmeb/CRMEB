<?php
/**
 * Created by PhpStorm.
 * User: xaboy
 * Date: 2018/12/12
 * Time: 19:48
 */

namespace FormBuilder\exception;


use Throwable;

class FormBuilderException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}