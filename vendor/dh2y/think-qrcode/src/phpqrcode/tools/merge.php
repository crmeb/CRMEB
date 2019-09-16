<?php

/*
 * PHP QR Code encoder
 *
 * Tool for merging all library files into one, simpler to incorporate.
 * 
 * MAKE SURE THAT RESULTING PHPQRCode.php (and its dir) ARE WRITABLE!
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
 
    $QR_BASEDIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
    $QR_TOOLSDIR = dirname(__FILE__).DIRECTORY_SEPARATOR;
    
    $outputFile = $QR_BASEDIR.'phpqrcode.php';
    
    // Required libs
    
    $fileList = array(
        $QR_BASEDIR.'qrconst.php',
        $QR_TOOLSDIR.'merged_config.php',
        $QR_BASEDIR.'qrtools.php',
        $QR_BASEDIR.'qrspec.php',
        $QR_BASEDIR.'qrimage.php',
        $QR_BASEDIR.'qrinput.php',
        $QR_BASEDIR.'qrbitstream.php',
        $QR_BASEDIR.'qrsplit.php',
        $QR_BASEDIR.'qrrscode.php',
        $QR_BASEDIR.'qrmask.php',
        $QR_BASEDIR.'qrencode.php'
    );
    
    $headerFile = $QR_TOOLSDIR.'merged_header.php';
    $versionFile = $QR_BASEDIR.'VERSION';
    
    $outputCode = '';
    
    foreach($fileList as $fileName) {
        $outputCode .= "\n\n".'//---- '.basename($fileName).' -----------------------------'."\n\n";
        $anotherCode = file_get_contents($fileName);
        $anotherCode = preg_replace ('/^<\?php/', '', $anotherCode);
        $anotherCode = preg_replace ('/\?>\*$/', '', $anotherCode);
        $outputCode .= "\n\n".$anotherCode."\n\n";
    }
    
	$versionDataEx = explode("\n", file_get_contents($versionFile));
	
    $outputContents = file_get_contents($headerFile);
    $outputContents .= "\n\n/*\n * Version: ".trim($versionDataEx[0])."\n * Build: ".trim($versionDataEx[1])."\n */\n\n";
    $outputContents .= $outputCode;
    
    file_put_contents($outputFile, $outputContents);
    
    