<?php


namespace OneSm;


class Sm4
{
    private $ck = [
        0x00070e15, 0x1c232a31, 0x383f464d, 0x545b6269,
        0x70777e85, 0x8c939aa1, 0xa8afb6bd, 0xc4cbd2d9,
        0xe0e7eef5, 0xfc030a11, 0x181f262d, 0x343b4249,
        0x50575e65, 0x6c737a81, 0x888f969d, 0xa4abb2b9,
        0xc0c7ced5, 0xdce3eaf1, 0xf8ff060d, 0x141b2229,
        0x30373e45, 0x4c535a61, 0x686f767d, 0x848b9299,
        0xa0a7aeb5, 0xbcc3cad1, 0xd8dfe6ed, 0xf4fb0209,
        0x10171e25, 0x2c333a41, 0x484f565d, 0x646b7279
    ];

    private $Sbox = [
        0xd6, 0x90, 0xe9, 0xfe, 0xcc, 0xe1, 0x3d, 0xb7, 0x16, 0xb6, 0x14, 0xc2, 0x28, 0xfb, 0x2c, 0x05,
        0x2b, 0x67, 0x9a, 0x76, 0x2a, 0xbe, 0x04, 0xc3, 0xaa, 0x44, 0x13, 0x26, 0x49, 0x86, 0x06, 0x99,
        0x9c, 0x42, 0x50, 0xf4, 0x91, 0xef, 0x98, 0x7a, 0x33, 0x54, 0x0b, 0x43, 0xed, 0xcf, 0xac, 0x62,
        0xe4, 0xb3, 0x1c, 0xa9, 0xc9, 0x08, 0xe8, 0x95, 0x80, 0xdf, 0x94, 0xfa, 0x75, 0x8f, 0x3f, 0xa6,
        0x47, 0x07, 0xa7, 0xfc, 0xf3, 0x73, 0x17, 0xba, 0x83, 0x59, 0x3c, 0x19, 0xe6, 0x85, 0x4f, 0xa8,
        0x68, 0x6b, 0x81, 0xb2, 0x71, 0x64, 0xda, 0x8b, 0xf8, 0xeb, 0x0f, 0x4b, 0x70, 0x56, 0x9d, 0x35,
        0x1e, 0x24, 0x0e, 0x5e, 0x63, 0x58, 0xd1, 0xa2, 0x25, 0x22, 0x7c, 0x3b, 0x01, 0x21, 0x78, 0x87,
        0xd4, 0x00, 0x46, 0x57, 0x9f, 0xd3, 0x27, 0x52, 0x4c, 0x36, 0x02, 0xe7, 0xa0, 0xc4, 0xc8, 0x9e,
        0xea, 0xbf, 0x8a, 0xd2, 0x40, 0xc7, 0x38, 0xb5, 0xa3, 0xf7, 0xf2, 0xce, 0xf9, 0x61, 0x15, 0xa1,
        0xe0, 0xae, 0x5d, 0xa4, 0x9b, 0x34, 0x1a, 0x55, 0xad, 0x93, 0x32, 0x30, 0xf5, 0x8c, 0xb1, 0xe3,
        0x1d, 0xf6, 0xe2, 0x2e, 0x82, 0x66, 0xca, 0x60, 0xc0, 0x29, 0x23, 0xab, 0x0d, 0x53, 0x4e, 0x6f,
        0xd5, 0xdb, 0x37, 0x45, 0xde, 0xfd, 0x8e, 0x2f, 0x03, 0xff, 0x6a, 0x72, 0x6d, 0x6c, 0x5b, 0x51,
        0x8d, 0x1b, 0xaf, 0x92, 0xbb, 0xdd, 0xbc, 0x7f, 0x11, 0xd9, 0x5c, 0x41, 0x1f, 0x10, 0x5a, 0xd8,
        0x0a, 0xc1, 0x31, 0x88, 0xa5, 0xcd, 0x7b, 0xbd, 0x2d, 0x74, 0xd0, 0x12, 0xb8, 0xe5, 0xb4, 0xb0,
        0x89, 0x69, 0x97, 0x4a, 0x0c, 0x96, 0x77, 0x7e, 0x65, 0xb9, 0xf1, 0x09, 0xc5, 0x6e, 0xc6, 0x84,
        0x18, 0xf0, 0x7d, 0xec, 0x3a, 0xdc, 0x4d, 0x20, 0x79, 0xee, 0x5f, 0x3e, 0xd7, 0xcb, 0x39, 0x48
    ];

    private $fk = [0xA3B1BAC6, 0x56AA3350, 0x677D9197, 0xB27022DC];

    private $rk = [];

    private $b = '';

    private $len = 16;


    /**
     * Sm4 constructor.
     * @param string $key 秘钥长度16位
     * @param string $b 不是16的倍数 需要的补码
     * @throws \Exception
     */
    public function __construct($key, $b = ' ')
    {
        $this->ck16($key);
        $this->crk($key);
    }

    private function dd(&$data)
    {
        $n    = strlen($data) % $this->len;
        $data = $data . str_repeat($this->b, $n);
    }

    private function ck16($str)
    {
        if (strlen($str) !== $this->len) {
            throw new \Exception('秘钥长度为16位');
        }
    }

    private function add($v)
    {
        $arr = unpack('N*', $v);
        $max = 0xffffffff;
        $j   = 1;
        for ($i = 4; $i > 0; $i--) {
            if ($arr[$i] > $max - $j) {
                $j       = 1;
                $arr[$i] = 0;
            } else {
                $arr[$i] += $j;
                break;
            }
        }
        return pack('N*', ...$arr);
    }

    /**
     * @param string $str 加密字符串
     * @param string $iv 初始化字符串16位
     * @return string
     * @throws \Exception
     */
    public function deDataCtr($str, $iv)
    {
        return $this->enDataCtr($str, $iv);
    }

    /**
     * @param string $str 加密字符串
     * @param string $iv 初始化字符串16位
     * @return string
     * @throws \Exception
     */
    public function enDataCtr($str, $iv)
    {
        $this->ck16($iv);
        $r = '';
        $this->dd($str);
        $l = strlen($str) / $this->len;
        for ($i = 0; $i < $l; $i++) {
            $s  = substr($str, $i * $this->len, $this->len);
            $tr = [];
            $this->encode(array_values(unpack('N*', $iv)), $tr);
            $s1 = pack('N*', ...$tr);
            $s1 = $s1 ^ $s;
            $iv = $this->add($iv);
            $r  .= $s1;
        }
        return $r;
    }


    /**
     * @param string $str 加密字符串
     * @param string $iv 初始化字符串16位
     * @return string
     * @throws \Exception
     */
    public function enDataOfb($str, $iv)
    {
        $this->ck16($iv);
        $r = '';
        $this->dd($str);
        $l = strlen($str) / $this->len;
        for ($i = 0; $i < $l; $i++) {
            $s  = substr($str, $i * $this->len, $this->len);
            $tr = [];
            $this->encode(array_values(unpack('N*', $iv)), $tr);
            $iv = pack('N*', ...$tr);
            $s1 = $s ^ $iv;
            $r  .= $s1;
        }
        return $r;
    }

    /**
     * @param string $str 加密字符串
     * @param string $iv 初始化字符串16位
     * @return string
     * @throws \Exception
     */
    public function deDataOfb($str, $iv)
    {
        return $this->enDataOfb($str, $iv);
    }

    /**
     * @param string $str 加密字符串
     * @param string $iv 初始化字符串16位
     * @return string
     * @throws \Exception
     */
    public function deDataCfb($str, $iv)
    {
        $this->ck16($iv);
        $r = '';
        $this->dd($str);
        $l = strlen($str) / $this->len;
        for ($i = 0; $i < $l; $i++) {
            $s  = substr($str, $i * $this->len, $this->len);
            $tr = [];
            $this->encode(array_values(unpack('N*', $iv)), $tr);
            $s1 = pack('N*', ...$tr);
            $s1 = $s ^ $s1;
            $iv = $s;
            $r  .= $s1;
        }
        return $r;
    }

    /**
     * @param string $str 加密字符串
     * @param string $iv 初始化字符串16位
     * @return string
     * @throws \Exception
     */
    public function enDataCfb($str, $iv)
    {
        $this->ck16($iv);
        $r = '';
        $this->dd($str);
        $l = strlen($str) / $this->len;
        for ($i = 0; $i < $l; $i++) {
            $s  = substr($str, $i * $this->len, $this->len);
            $tr = [];
            $this->encode(array_values(unpack('N*', $iv)), $tr);
            $s1 = pack('N*', ...$tr);
            $iv = $s ^ $s1;
            $r  .= $iv;
        }
        return $r;
    }


    /**
     * @param string $str 加密字符串
     * @param string $iv 初始化字符串16位
     * @return string
     * @throws \Exception
     */
    public function enDataCbc($str, $iv)
    {
        $this->ck16($iv);
        $r = '';
        $this->dd($str);
        $l = strlen($str) / $this->len;
        for ($i = 0; $i < $l; $i++) {
            $s  = substr($str, $i * $this->len, $this->len);
            $s  = $iv ^ $s;
            $tr = [];
            $this->encode(array_values(unpack('N*', $s)), $tr);
            $iv = pack('N*', ...$tr);
            $r  .= $iv;
        }
        return $r;
    }

    /**
     * @param string $str 加密字符串
     * @param string $iv 初始化字符串16位
     * @return string
     * @throws \Exception
     */
    public function deDataCbc($str, $iv)
    {
        $this->ck16($iv);
        $r = '';
        $this->dd($str);
        $l = strlen($str) / $this->len;
        for ($i = 0; $i < $l; $i++) {
            $s  = substr($str, $i * $this->len, $this->len);
            $tr = [];
            $this->decode(array_values(unpack('N*', $s)), $tr);
            $s1 = pack('N*', ...$tr);
            $s1 = $iv ^ $s1;
            $iv = $s;
            $r  .= $s1;
        }
        return $r;
    }


    /**
     * @param string $str 加密字符串
     * @return string
     */
    public function enDataEcb($str)
    {
        $r = [];
        $this->dd($str);
        $ar = unpack('N*', $str);
        do {
            $this->encode([current($ar), next($ar), next($ar), next($ar)], $r);
        } while (next($ar));
        return pack('N*', ...$r);
    }

    /**
     * @param string $str 解密字符串
     * @return string
     */
    public function deDataEcb($str)
    {
        $r = [];
        $this->dd($str);
        $ar = unpack('N*', $str);
        do {
            $this->decode([current($ar), next($ar), next($ar), next($ar)], $r);
        } while (next($ar));
        return pack('N*', ...$r);
    }

    private function encode($ar, &$r)
    {
        for ($i = 0; $i < 32; $i++) {
            $ar[$i + 4] = $this->f($ar[$i], $ar[$i + 1], $ar[$i + 2], $ar[$i + 3], $this->rk[$i]);
        }
        $r[] = $ar[35];
        $r[] = $ar[34];
        $r[] = $ar[33];
        $r[] = $ar[32];
    }

    private function decode($ar, &$r)
    {
        for ($i = 0; $i < 32; $i++) {
            $ar[$i + 4] = $this->f($ar[$i], $ar[$i + 1], $ar[$i + 2], $ar[$i + 3], $this->rk[31 - $i]);
        }
        $r[] = $ar[35];
        $r[] = $ar[34];
        $r[] = $ar[33];
        $r[] = $ar[32];
    }

    private function crk($key)
    {
        $keys = array_values(unpack('N*', $key));
        $keys = [
            $keys[0] ^ $this->fk[0],
            $keys[1] ^ $this->fk[1],
            $keys[2] ^ $this->fk[2],
            $keys[3] ^ $this->fk[3]
        ];
        for ($i = 0; $i < 32; $i++) {
            $this->rk[] = $keys[] = $keys[$i] ^ $this->t1($keys[$i + 1] ^ $keys[$i + 2] ^ $keys[$i + 3] ^ $this->ck[$i]);
        }
    }

    private function lm($a, $n)
    {
        return ($a >> (32 - $n) | (($a << $n) & 0xffffffff));
    }

    private function f($x0, $x1, $x2, $x3, $r)
    {
        return $x0 ^ $this->t($x1 ^ $x2 ^ $x3 ^ $r);
    }

    private function s($n)
    {
        return $this->Sbox[($n & 0xff)] | $this->Sbox[(($n >> 8) & 0xff)] << 8 | $this->Sbox[(($n >> 16) & 0xff)] << 16 | $this->Sbox[(($n >> 24) & 0xff)] << 24;
    }

    private function t($n)
    {
        $b = $this->s($n);
        return $b ^ $this->lm($b, 2) ^ $this->lm($b, 10) ^ $this->lm($b, 18) ^ $this->lm($b, 24);
    }

    private function t1($n)
    {
        $b = $this->s($n);
        return $b ^ $this->lm($b, 13) ^ $this->lm($b, 23);
    }


}