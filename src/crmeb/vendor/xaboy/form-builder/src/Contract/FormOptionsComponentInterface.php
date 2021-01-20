<?php
/**
 * @package crmeb_merchant
 *
 * @author xaboy
 * @day 2020-05-13
 *
 * Copyright (c) http://crmeb.net
 */

namespace FormBuilder\Contract;


interface FormOptionsComponentInterface extends FormComponentInterface
{
    public function setOptions(array $options);

    public function getOptions();
}