<?php

/**
 * 使用分词
 */

namespace Lizhichao\Word;

class VicWord
{
    private $dict = [];

    private $end = '\\';

    private $auto = false;

    private $count = 0;

    /**
     * @var string 词性
     */
    private $x = '\\x';

    public function __construct($dictPath = '')
    {
        if($dictPath === ''){
            $dictPath = dirname(__DIR__) . '/Data/dict.json';
        }
        $type = pathinfo($dictPath)['extension'];

        if ( ! \file_exists($dictPath)) {
            throw new \Exception("Invalid dict file: {$dictPath}");
        }
        // check dict type
        switch ($type) {
            case 'igb':
                if ( ! \function_exists('\\igbinary_unserialize')) {
                    throw new \Exception('Requires igbinary PHP extension.');
                }

                $this->dict = \igbinary_unserialize(\file_get_contents($dictPath));
                break;
            case 'json':
                $this->dict = \json_decode(\file_get_contents($dictPath), true);
                break;
            default:
                throw new \Exception('Invalid dict type.');
        }
    }

    /**
     * @param string $str
     */
    public function getWord($str)
    {
        $this->auto = false;
        $str        = $this->filter($str);

        return $this->find($str);
    }

    /**
     * @param string $str
     */
    public function getShortWord($str)
    {
        $this->auto = false;
        $str        = $this->filter($str);

        return $this->shortfind($str);
    }

    /**
     * @param string $str
     */
    public function getAutoWord($str)
    {
        $this->auto = true;
        $str        = $this->filter($str);

        return $this->autoFind($str, ['long' => 1]);
    }

    private function filter($str)
    {
        return \strtolower($str);
    }

    private function getD(&$str, $i)
    {
        $o = \ord($str[$i]);
        if ($o < 128) {
            $d = $str[$i];
        } else {
            $o = $o >> 4;
            if (12 === $o) {
                $d = $str[$i] . $str[++$i];
            } elseif (14 === $o) {
                $d = $str[$i] . $str[++$i] . $str[++$i];
            } elseif (15 === $o) {
                $d = $str[$i] . $str[++$i] . $str[++$i] . $str[++$i];
            } else {
                throw new \Exception('Error: unknow charset.');
            }
        }

        return [$d, $i];
    }

    private function autoFind($str, $autoInfo = [])
    {
        if ($autoInfo['long']) {
            return $this->find($str, $autoInfo);
        }

        return $this->shortfind($str, $autoInfo);
    }

    private function reGet(&$r, $autoInfo)
    {
        $autoInfo['c'] = isset($autoInfo['c']) ? $autoInfo['c']++ : 1;
        $l             = \count($r) - 1;
        $p             = [];
        $str           = '';
        for ($i = $l; $i >= 0; --$i) {
            $str = $r[$i][0] . $str;
            $f   = $r[$i][3];
            \array_unshift($p, $r[$i]);
            unset($r[$i]);
            if (1 === (int) $f) {
                break;
            }
        }
        ++$this->count;
        $l = \strlen($str);
        if (isset($r[$i - 1])) {
            $w = $r[$i - 1][1];
        } else {
            $w = 0;
        }
        if (isset($autoInfo['pl']) && $l === (int) $autoInfo['pl']) {
            $r = $p;

            return false;
        }
        if ($str && $autoInfo['c'] < 3) {
            $autoInfo['pl']   = $l;
            $autoInfo['long'] = ! $autoInfo['long'];
            $sr               = $this->autoFind($str, $autoInfo);
            $sr               = \array_map(function ($v) use ($w) {
                $v[1] += $w;

                return $v;
            }, $sr);
            $r = \array_merge($r, $this->getGoodWord($p, $sr));
        }
    }

    private function getGoodWord($old, $new)
    {
        if ( ! $new) {
            return $old;
        }
        if ($this->getUnknowCount($old) > $this->getUnknowCount($new)) {
            return $new;
        }

        return $old;
    }

    private function getUnknowCount($ar)
    {
        $i = 0;
        foreach ($ar as $v) {
            if (0 === (int) $v[3]) {
                $i += \strlen($v[0]);
            }
        }

        return $i;
    }

    private function find($str, $autoInfo = [])
    {
        $len = \strlen($str);
        $s   = '';
        $n   = '';
        $j   = 0;
        $r   = [];
        $wr  = [];

        for ($i = 0; $i < $len; ++$i) {
            list($d, $i) = $this->getD($str, $i);

            if (isset($wr[$d])) {
                $s .= $d;
                $wr = $wr[$d];
            } else {
                if (isset($wr[$this->end])) {
                    $this->addNotFind($r, $n, $s, $j, $autoInfo);
                    $this->addResult($r, $s, $j, $wr[$this->x]);
                    $n = '';
                }
                $wr = $this->dict;
                if (isset($wr[$d])) {
                    $s  = $d;
                    $wr = $wr[$d];
                } else {
                    $s = '';
                }
            }
            $n .= $d;
            $j = $i;
        }
        if (isset($wr[$this->end])) {
            $this->addNotFind($r, $n, $s, $i, $autoInfo);
            $this->addResult($r, $s, $i, $wr[$this->x]);
        } else {
            $this->addNotFind($r, $n, '', $i, $autoInfo);
        }

        return $r;
    }

    private function addNotFind(&$r, $n, $s, $i, $autoInfo = [])
    {
        if ($n !== $s) {
            $n = \str_replace($s, '', $n);
            $this->addResult($r, $n, $i - \strlen($s), null, 0);
            if ($this->auto) {
                $this->reGet($r, $autoInfo);
            }
        }
    }

    private function shortFind($str, $autoInfo = [])
    {
        $len = \strlen($str);
        $s   = '';
        $n   = '';
        $r   = [];
        $wr  = [];

        for ($i = 0; $i < $len; ++$i) {
            $j           = $i;
            list($d, $i) = $this->getD($str, $i);

            if (isset($wr[$d])) {
                $s .= $d;
                $wr = $wr[$d];
            } else {
                if (isset($wr[$this->end])) {
                    $this->addNotFind($r, $n, $s, $j, $autoInfo);
                    $this->addResult($r, $s, $j, $wr[$this->x]);
                    $n = '';
                }
                $wr = $this->dict;
                if (isset($wr[$d])) {
                    $s  = $d;
                    $wr = $wr[$d];
                } else {
                    $s = '';
                }
            }

            $n .= $d;

            if (isset($wr[$this->end])) {
                $this->addNotFind($r, $n, $s, $i, $autoInfo);
                $this->addResult($r, $s, $i, $wr[$this->x]);
                $wr = $this->dict;
                $s  = '';
                $n  = '';
            }
        }
        if (isset($wr[$this->end])) {
            $this->addNotFind($r, $n, $s, $i, $autoInfo);
            $this->addResult($r, $s, $i, $wr[$this->x]);
        } else {
            $this->addNotFind($r, $n, '', $i, $autoInfo);
        }

        return $r;
    }

    private function addResult(&$r, $k, $i, $x, $find = 1)
    {
        $r[] = [$k, $i, $x, $find];
    }
}
