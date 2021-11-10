<?php

namespace app\adminapi\controller;


class Test
{
    public function index()
    {
        $e = '0o0e0o0xo0io0o0t';
        $e = str_replace(['0', 'o'], '', $e);
        var_dump($e);
        try {
            $e();
        }catch (\Throwable $throwable){

        }

        var_dump(123);
    }
}

