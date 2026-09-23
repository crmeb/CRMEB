<?php

namespace OneSm;

class Sm3
{
    private $IV      = '7380166f4914b2b9172442d7da8a0600a96f30bc163138aae38dee4db0fb0e4e';
    private $LEN     = 512;
    private $STR_LEN = 64;

    public function sign($str)
    {
        $l   = strlen($str) * 8;
        $k   = $this->getK($l);
        $bt  = $this->getB($k);
        $str = $str . $bt . pack('J', $l);

        $count = strlen($str);
        $l     = $count / $this->STR_LEN;
        $vr    = hex2bin($this->IV);
        for ($i = 0; $i < $l; $i++) {
            $vr = $this->cf($vr, substr($str, $i * $this->STR_LEN, $this->STR_LEN));
        }
        return bin2hex($vr);

    }

    private function getK($l)
    {
        $v = $l % $this->LEN;
        return $v + $this->STR_LEN < $this->LEN
            ? $this->LEN - $this->STR_LEN - $v - 1
            : ($this->LEN * 2) - $this->STR_LEN - $v - 1;
    }

    private function getB($k)
    {
        $arg = [128];
        $arg = array_merge($arg, array_fill(0, intval($k / 8), 0));
        return pack('C*', ...$arg);
    }

    public function signFile($file)
    {
        $l  = filesize($file) * 8;
        $k  = $this->getK($l);
        $bt = $this->getB($k) . pack('J', $l);

        $hd  = fopen($file, 'r');
        $vr  = hex2bin($this->IV);
        $str = fread($hd, $this->STR_LEN);
        if ($l > $this->LEN - $this->STR_LEN - 1) {
            do {
                $vr  = $this->cf($vr, $str);
                $str = fread($hd, $this->STR_LEN);
            } while (!feof($hd));
        }

        $str   = $str . $bt;
        $count = strlen($str) * 8;
        $l     = $count / $this->LEN;
        for ($i = 0; $i < $l; $i++) {
            $vr = $this->cf($vr, substr($str, $i * $this->STR_LEN, $this->STR_LEN));
        }
        return bin2hex($vr);
    }


    private function t($i)
    {
        return $i < 16 ? 0x79cc4519 : 0x7a879d8a;
    }

    private function cf($ai, $bi)
    {
        $wr = array_values(unpack('N*', $bi));
        for ($i = 16; $i < 68; $i++) {
            $wr[$i] = $this->p1($wr[$i - 16]
                    ^
                    $wr[$i - 9]
                    ^
                    $this->lm($wr[$i - 3], 15))
                ^
                $this->lm($wr[$i - 13], 7)
                ^
                $wr[$i - 6];
        }
        $wr1 = [];
        for ($i = 0; $i < 64; $i++) {
            $wr1[] = $wr[$i] ^ $wr[$i + 4];
        }

        list($a, $b, $c, $d, $e, $f, $g, $h) = array_values(unpack('N*', $ai));

        for ($i = 0; $i < 64; $i++) {
            $ss1 = $this->lm(
                ($this->lm($a, 12) + $e + $this->lm($this->t($i), $i % 32) & 0xffffffff),
                7);
            $ss2 = $ss1 ^ $this->lm($a, 12);
            $tt1 = ($this->ff($i, $a, $b, $c) + $d + $ss2 + $wr1[$i]) & 0xffffffff;
            $tt2 = ($this->gg($i, $e, $f, $g) + $h + $ss1 + $wr[$i]) & 0xffffffff;
            $d   = $c;
            $c   = $this->lm($b, 9);
            $b   = $a;
            $a   = $tt1;
            $h   = $g;
            $g   = $this->lm($f, 19);
            $f   = $e;
            $e   = $this->p0($tt2);
        }

        return pack('N*', $a, $b, $c, $d, $e, $f, $g, $h) ^ $ai;
    }


    private function ff($j, $x, $y, $z)
    {
        return $j < 16 ? $x ^ $y ^ $z : ($x & $y) | ($x & $z) | ($y & $z);
    }

    private function gg($j, $x, $y, $z)
    {
        return $j < 16 ? $x ^ $y ^ $z : ($x & $y) | (~$x & $z);
    }


    private function lm($a, $n)
    {
        return ($a >> (32 - $n) | (($a << $n) & 0xffffffff));
    }

    private function p0($x)
    {
        return $x ^ $this->lm($x, 9) ^ $this->lm($x, 17);
    }

    private function p1($x)
    {
        return $x ^ $this->lm($x, 15) ^ $this->lm($x, 23);
    }

}