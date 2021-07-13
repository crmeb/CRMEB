<?php
/*
 * PHP QR Code encoder
 *
 * Main encoder classes.
 *
 * Based on libqrencode C library distributed under LGPL 2.1
 * Copyright (C) 2006, 2007, 2008, 2009 Kentaro Fukuchi <fukuchi@megaui.net>
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
 
    class QRrsblock {
        public $dataLength;
        public $data = array();
        public $eccLength;
        public $ecc = array();
        
        public function __construct($dl, $data, $el, &$ecc, QRrsItem $rs)
        {
            $rs->encode_rs_char($data, $ecc);
        
            $this->dataLength = $dl;
            $this->data = $data;
            $this->eccLength = $el;
            $this->ecc = $ecc;
        }
    };
    
    //##########################################################################

    class QRrawcode {
        public $version;
        public $datacode = array();
        public $ecccode = array();
        public $blocks;
        public $rsblocks = array(); //of RSblock
        public $count;
        public $dataLength;
        public $eccLength;
        public $b1;
        
        //----------------------------------------------------------------------
        public function __construct(QRinput $input)
        {
            $spec = array(0,0,0,0,0);
            
            $this->datacode = $input->getByteStream();
            if(is_null($this->datacode)) {
                throw new Exception('null imput string');
            }

            QRspec::getEccSpec($input->getVersion(), $input->getErrorCorrectionLevel(), $spec);

            $this->version = $input->getVersion();
            $this->b1 = QRspec::rsBlockNum1($spec);
            $this->dataLength = QRspec::rsDataLength($spec);
            $this->eccLength = QRspec::rsEccLength($spec);
            $this->ecccode = array_fill(0, $this->eccLength, 0);
            $this->blocks = QRspec::rsBlockNum($spec);
            
            $ret = $this->init($spec);
            if($ret < 0) {
                throw new Exception('block alloc error');
                return null;
            }

            $this->count = 0;
        }
        
        //----------------------------------------------------------------------
        public function init(array $spec)
        {
            $dl = QRspec::rsDataCodes1($spec);
            $el = QRspec::rsEccCodes1($spec);
            $rs = QRrs::init_rs(8, 0x11d, 0, 1, $el, 255 - $dl - $el);
            

            $blockNo = 0;
            $dataPos = 0;
            $eccPos = 0;
            for($i=0; $i<QRspec::rsBlockNum1($spec); $i++) {
                $ecc = array_slice($this->ecccode,$eccPos);
                $this->rsblocks[$blockNo] = new QRrsblock($dl, array_slice($this->datacode, $dataPos), $el,  $ecc, $rs);
                $this->ecccode = array_merge(array_slice($this->ecccode,0, $eccPos), $ecc);
                
                $dataPos += $dl;
                $eccPos += $el;
                $blockNo++;
            }

            if(QRspec::rsBlockNum2($spec) == 0)
                return 0;

            $dl = QRspec::rsDataCodes2($spec);
            $el = QRspec::rsEccCodes2($spec);
            $rs = QRrs::init_rs(8, 0x11d, 0, 1, $el, 255 - $dl - $el);
            
            if($rs == NULL) return -1;
            
            for($i=0; $i<QRspec::rsBlockNum2($spec); $i++) {
                $ecc = array_slice($this->ecccode,$eccPos);
                $this->rsblocks[$blockNo] = new QRrsblock($dl, array_slice($this->datacode, $dataPos), $el, $ecc, $rs);
                $this->ecccode = array_merge(array_slice($this->ecccode,0, $eccPos), $ecc);
                
                $dataPos += $dl;
                $eccPos += $el;
                $blockNo++;
            }

            return 0;
        }
        
        //----------------------------------------------------------------------
        public function getCode()
        {
            $ret;

            if($this->count < $this->dataLength) {
                $row = $this->count % $this->blocks;
                $col = $this->count / $this->blocks;
                if($col >= $this->rsblocks[0]->dataLength) {
                    $row += $this->b1;
                }
                $ret = $this->rsblocks[$row]->data[$col];
            } else if($this->count < $this->dataLength + $this->eccLength) {
                $row = ($this->count - $this->dataLength) % $this->blocks;
                $col = ($this->count - $this->dataLength) / $this->blocks;
                $ret = $this->rsblocks[$row]->ecc[$col];
            } else {
                return 0;
            }
            $this->count++;
            
            return $ret;
        }
    }

    //##########################################################################
    
    class QRcode {
    
        public $version;
        public $width;
        public $data; 
        
        //----------------------------------------------------------------------
        public function encodeMask(QRinput $input, $mask)
        {
            if($input->getVersion() < 0 || $input->getVersion() > QRSPEC_VERSION_MAX) {
                throw new Exception('wrong version');
            }
            if($input->getErrorCorrectionLevel() > QR_ECLEVEL_H) {
                throw new Exception('wrong level');
            }

            $raw = new QRrawcode($input);
            
            QRtools::markTime('after_raw');
            
            $version = $raw->version;
            $width = QRspec::getWidth($version);
            $frame = QRspec::newFrame($version);
            
            $filler = new FrameFiller($width, $frame);
            if(is_null($filler)) {
                return NULL;
            }

            // inteleaved data and ecc codes
            for($i=0; $i<$raw->dataLength + $raw->eccLength; $i++) {
                $code = $raw->getCode();
                $bit = 0x80;
                for($j=0; $j<8; $j++) {
                    $addr = $filler->next();
                    $filler->setFrameAt($addr, 0x02 | (($bit & $code) != 0));
                    $bit = $bit >> 1;
                }
            }
            
            QRtools::markTime('after_filler');
            
            unset($raw);
            
            // remainder bits
            $j = QRspec::getRemainder($version);
            for($i=0; $i<$j; $i++) {
                $addr = $filler->next();
                $filler->setFrameAt($addr, 0x02);
            }
            
            $frame = $filler->frame;
            unset($filler);
            
            
            // masking
            $maskObj = new QRmask();
            if($mask < 0) {
            
                if (QR_FIND_BEST_MASK) {
                    $masked = $maskObj->mask($width, $frame, $input->getErrorCorrectionLevel());
                } else {
                    $masked = $maskObj->makeMask($width, $frame, (intval(QR_DEFAULT_MASK) % 8), $input->getErrorCorrectionLevel());
                }
            } else {
                $masked = $maskObj->makeMask($width, $frame, $mask, $input->getErrorCorrectionLevel());
            }
            
            if($masked == NULL) {
                return NULL;
            }
            
            QRtools::markTime('after_mask');
            
            $this->version = $version;
            $this->width = $width;
            $this->data = $masked;
            
            return $this;
        }
    
        //----------------------------------------------------------------------
        public function encodeInput(QRinput $input)
        {
            return $this->encodeMask($input, -1);
        }
        
        //----------------------------------------------------------------------
        public function encodeString8bit($string, $version, $level)
        {
            if(string == NULL) {
                throw new Exception('empty string!');
                return NULL;
            }

            $input = new QRinput($version, $level);
            if($input == NULL) return NULL;

            $ret = $input->append($input, QR_MODE_8, strlen($string), str_split($string));
            if($ret < 0) {
                unset($input);
                return NULL;
            }
            return $this->encodeInput($input);
        }

        //----------------------------------------------------------------------
        public function encodeString($string, $version, $level, $hint, $casesensitive)
        {

            if($hint != QR_MODE_8 && $hint != QR_MODE_KANJI) {
                throw new Exception('bad hint');
                return NULL;
            }

            $input = new QRinput($version, $level);
            if($input == NULL) return NULL;

            $ret = QRsplit::splitStringToQRinput($string, $input, $hint, $casesensitive);
            if($ret < 0) {
                return NULL;
            }

            return $this->encodeInput($input);
        }
        
        //----------------------------------------------------------------------
        public static function png($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4, $saveandprint=false) 
        {
            $enc = QRencode::factory($level, $size, $margin);
            return $enc->encodePNG($text, $outfile, $saveandprint=false);
        }

        //----------------------------------------------------------------------
        public static function text($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4) 
        {
            $enc = QRencode::factory($level, $size, $margin);
            return $enc->encode($text, $outfile);
        }

        //----------------------------------------------------------------------
        public static function raw($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4) 
        {
            $enc = QRencode::factory($level, $size, $margin);
            return $enc->encodeRAW($text, $outfile);
        }
    }
    
    //##########################################################################
    
    class FrameFiller {
    
        public $width;
        public $frame;
        public $x;
        public $y;
        public $dir;
        public $bit;
        
        //----------------------------------------------------------------------
        public function __construct($width, &$frame)
        {
            $this->width = $width;
            $this->frame = $frame;
            $this->x = $width - 1;
            $this->y = $width - 1;
            $this->dir = -1;
            $this->bit = -1;
        }
        
        //----------------------------------------------------------------------
        public function setFrameAt($at, $val)
        {
            $this->frame[$at['y']][$at['x']] = chr($val);
        }
        
        //----------------------------------------------------------------------
        public function getFrameAt($at)
        {
            return ord($this->frame[$at['y']][$at['x']]);
        }
        
        //----------------------------------------------------------------------
        public function next()
        {
            do {
            
                if($this->bit == -1) {
                    $this->bit = 0;
                    return array('x'=>$this->x, 'y'=>$this->y);
                }

                $x = $this->x;
                $y = $this->y;
                $w = $this->width;

                if($this->bit == 0) {
                    $x--;
                    $this->bit++;
                } else {
                    $x++;
                    $y += $this->dir;
                    $this->bit--;
                }

                if($this->dir < 0) {
                    if($y < 0) {
                        $y = 0;
                        $x -= 2;
                        $this->dir = 1;
                        if($x == 6) {
                            $x--;
                            $y = 9;
                        }
                    }
                } else {
                    if($y == $w) {
                        $y = $w - 1;
                        $x -= 2;
                        $this->dir = -1;
                        if($x == 6) {
                            $x--;
                            $y -= 8;
                        }
                    }
                }
                if($x < 0 || $y < 0) return null;

                $this->x = $x;
                $this->y = $y;

            } while(ord($this->frame[$y][$x]) & 0x80);
                        
            return array('x'=>$x, 'y'=>$y);
        }
        
    } ;
    
    //##########################################################################    
    
    class QRencode {
    
        public $casesensitive = true;
        public $eightbit = false;
        
        public $version = 0;
        public $size = 3;
        public $margin = 4;
        
        public $structured = 0; // not supported yet
        
        public $level = QR_ECLEVEL_L;
        public $hint = QR_MODE_8;
        
        //----------------------------------------------------------------------
        public static function factory($level = QR_ECLEVEL_L, $size = 3, $margin = 4)
        {
            $enc = new QRencode();
            $enc->size = $size;
            $enc->margin = $margin;
            
            switch ($level.'') {
                case '0':
                case '1':
                case '2':
                case '3':
                        $enc->level = $level;
                    break;
                case 'l':
                case 'L':
                        $enc->level = QR_ECLEVEL_L;
                    break;
                case 'm':
                case 'M':
                        $enc->level = QR_ECLEVEL_M;
                    break;
                case 'q':
                case 'Q':
                        $enc->level = QR_ECLEVEL_Q;
                    break;
                case 'h':
                case 'H':
                        $enc->level = QR_ECLEVEL_H;
                    break;
            }
            
            return $enc;
        }
        
        //----------------------------------------------------------------------
        public function encodeRAW($intext, $outfile = false) 
        {
            $code = new QRcode();

            if($this->eightbit) {
                $code->encodeString8bit($intext, $this->version, $this->level);
            } else {
                $code->encodeString($intext, $this->version, $this->level, $this->hint, $this->casesensitive);
            }
            
            return $code->data;
        }

        //----------------------------------------------------------------------
        public function encode($intext, $outfile = false) 
        {
            $code = new QRcode();

            if($this->eightbit) {
                $code->encodeString8bit($intext, $this->version, $this->level);
            } else {
                $code->encodeString($intext, $this->version, $this->level, $this->hint, $this->casesensitive);
            }
            
            QRtools::markTime('after_encode');
            
            if ($outfile!== false) {
                file_put_contents($outfile, join("\n", QRtools::binarize($code->data)));
            } else {
                return QRtools::binarize($code->data);
            }
        }
        
        //----------------------------------------------------------------------
        public function encodePNG($intext, $outfile = false,$saveandprint=false) 
        {
            try {
            
                ob_start();
                $tab = $this->encode($intext);
                $err = ob_get_contents();
                ob_end_clean();
                
                if ($err != '')
                    QRtools::log($outfile, $err);
                
                $maxSize = (int)(QR_PNG_MAXIMUM_SIZE / (count($tab)+2*$this->margin));
                
                QRimage::png($tab, $outfile, min(max(1, $this->size), $maxSize), $this->margin,$saveandprint);
            
            } catch (Exception $e) {
            
                QRtools::log($outfile, $e->getMessage());
            
            }
        }
    }
