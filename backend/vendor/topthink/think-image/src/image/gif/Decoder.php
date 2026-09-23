<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\image\gif;


class Decoder
{
    public $GIF_buffer = [];
    public $GIF_arrays = [];
    public $GIF_delays = [];
    public $GIF_stream = "";
    public $GIF_string = "";
    public $GIF_bfseek = 0;
    public $GIF_screen = [];
    public $GIF_global = [];
    public $GIF_sorted;
    public $GIF_colorS;
    public $GIF_colorC;
    public $GIF_colorF;

    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::    GIFDecoder ( $GIF_pointer )
    ::
     */
    public function __construct($GIF_pointer)
    {
        $this->GIF_stream = $GIF_pointer;
        $this->getByte(6); // GIF89a
        $this->getByte(7); // Logical Screen Descriptor
        $this->GIF_screen = $this->GIF_buffer;
        $this->GIF_colorF = $this->GIF_buffer[4] & 0x80 ? 1 : 0;
        $this->GIF_sorted = $this->GIF_buffer[4] & 0x08 ? 1 : 0;
        $this->GIF_colorC = $this->GIF_buffer[4] & 0x07;
        $this->GIF_colorS = 2 << $this->GIF_colorC;
        if (1 == $this->GIF_colorF) {
            $this->getByte(3 * $this->GIF_colorS);
            $this->GIF_global = $this->GIF_buffer;
        }

        for ($cycle = 1; $cycle;) {
            if ($this->getByte(1)) {
                switch ($this->GIF_buffer[0]) {
                    case 0x21:
                        $this->readExtensions();
                        break;
                    case 0x2C:
                        $this->readDescriptor();
                        break;
                    case 0x3B:
                        $cycle = 0;
                        break;
                }
            } else {
                $cycle = 0;
            }
        }
    }

    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::    GIFReadExtension ( )
    ::
     */
    public function readExtensions()
    {
        $this->getByte(1);
        for (; ;) {
            $this->getByte(1);
            if (($u = $this->GIF_buffer[0]) == 0x00) {
                break;
            }
            $this->getByte($u);
            /*
             * 07.05.2007.
             * Implemented a new line for a new function
             * to determine the originaly delays between
             * frames.
             *
             */
            if (4 == $u) {
                $this->GIF_delays[] = ($this->GIF_buffer[1] | $this->GIF_buffer[2] << 8);
            }
        }
    }

    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::    GIFReadExtension ( )
    ::
     */
    public function readDescriptor()
    {
        $this->getByte(9);
        $GIF_screen = $this->GIF_buffer;
        $GIF_colorF = $this->GIF_buffer[8] & 0x80 ? 1 : 0;
        if ($GIF_colorF) {
            $GIF_code = $this->GIF_buffer[8] & 0x07;
            $GIF_sort = $this->GIF_buffer[8] & 0x20 ? 1 : 0;
        } else {
            $GIF_code = $this->GIF_colorC;
            $GIF_sort = $this->GIF_sorted;
        }
        $GIF_size = 2 << $GIF_code;
        $this->GIF_screen[4] &= 0x70;
        $this->GIF_screen[4] |= 0x80;
        $this->GIF_screen[4] |= $GIF_code;
        if ($GIF_sort) {
            $this->GIF_screen[4] |= 0x08;
        }
        $this->GIF_string = "GIF87a";
        $this->putByte($this->GIF_screen);
        if (1 == $GIF_colorF) {
            $this->getByte(3 * $GIF_size);
            $this->putByte($this->GIF_buffer);
        } else {
            $this->putByte($this->GIF_global);
        }
        $this->GIF_string .= chr(0x2C);
        $GIF_screen[8] &= 0x40;
        $this->putByte($GIF_screen);
        $this->getByte(1);
        $this->putByte($this->GIF_buffer);
        for (; ;) {
            $this->getByte(1);
            $this->putByte($this->GIF_buffer);
            if (($u = $this->GIF_buffer[0]) == 0x00) {
                break;
            }
            $this->getByte($u);
            $this->putByte($this->GIF_buffer);
        }
        $this->GIF_string .= chr(0x3B);
        /*
        Add frames into $GIF_stream array...
         */
        $this->GIF_arrays[] = $this->GIF_string;
    }

    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::    GIFGetByte ( $len )
    ::
    */
    public function getByte($len)
    {
        $this->GIF_buffer = [];
        for ($i = 0; $i < $len; $i++) {
            if ($this->GIF_bfseek > strlen($this->GIF_stream)) {
                return 0;
            }
            $this->GIF_buffer[] = ord($this->GIF_stream{$this->GIF_bfseek++});
        }
        return 1;
    }

    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::    GIFPutByte ( $bytes )
    ::
     */
    public function putByte($bytes)
    {
        for ($i = 0; $i < count($bytes); $i++) {
            $this->GIF_string .= chr($bytes[$i]);
        }
    }

    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::    PUBLIC FUNCTIONS
    ::
    ::
    ::    GIFGetFrames ( )
    ::
     */
    public function getFrames()
    {
        return ($this->GIF_arrays);
    }

    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::    GIFGetDelays ( )
    ::
     */
    public function getDelays()
    {
        return ($this->GIF_delays);
    }
}