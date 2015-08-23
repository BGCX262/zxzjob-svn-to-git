<?php
// +----------------------------------------------------------------------+
// | ThinkPHP                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2006~2007 http://thinkphp.cn All rights reserved.      |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the 'License');      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an 'AS IS' BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: liu21st <liu21st@gmail.com>                                  |
// +----------------------------------------------------------------------+
// $Id$

define("BCCOMP_LARGER", 1);
/**
 +------------------------------------------------------------------------------
 * Rsa 加密实现类
 +------------------------------------------------------------------------------
 * @category   ORG
 * @package  ORG
 * @subpackage  Crypt
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class Rsa extends Base
{
	
    /**
     +----------------------------------------------------------
     * 加密字符串
     * 
     +----------------------------------------------------------
     * @access static 
     +----------------------------------------------------------
     * @param string $str 字符串
     * @param string $key 加密key
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
      
    /**
     +----------------------------------------------------------
     * 解密字符串
     * 
     +----------------------------------------------------------
     * @access static 
     +----------------------------------------------------------
     * @param string $str 字符串
     * @param string $key 加密key
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
/*
* Implementation of the RSA algorithm
* (C) Copyright 2004 Edsko de Vries, Ireland
*
* Licensed under the GNU Public License (GPL)
*
* This implementation has been verified against [3] 
* (tested Java/PHP interoperability).
*
* References:
* [1] "Applied Cryptography", Bruce Schneier, John Wiley & Sons, 1996
* [2] "Prime Number Hide-and-Seek", Brian Raiter, Muppetlabs (online)
* [3] "The Bouncy Castle Crypto Package", Legion of the Bouncy Castle,
* (open source cryptography library for Java, online)
* [4] "PKCS #1: RSA Encryption Standard", RSA Laboratories Technical Note,
* version 1.5, revised November 1, 1993
*/

function encrypt($message, $public_key, $modulus, $keylength)
{
    $padded = Rsa::add_PKCS1_padding($message, true, $keylength / 8);
    $number = Rsa::binary_to_number($padded);
    $encrypted = Rsa::pow_mod($number, $public_key, $modulus);
    $result = Rsa::number_to_binary($encrypted, $keylength / 8);
    
    return $result;
}

function decrypt($message, $private_key, $modulus, $keylength)
{
    $number = Rsa::binary_to_number($message);
    $decrypted = Rsa::pow_mod($number, $private_key, $modulus);
    $result = Rsa::number_to_binary($decrypted, $keylength / 8);

    return Rsa::remove_PKCS1_padding($result, $keylength / 8);
}

function sign($message, $private_key, $modulus, $keylength)
{
    $padded = Rsa::add_PKCS1_padding($message, false, $keylength / 8);
    $number = Rsa::binary_to_number($padded);
    $signed = Rsa::pow_mod($number, $private_key, $modulus);
    $result = Rsa::number_to_binary($signed, $keylength / 8);

    return $result;
}

function verify($message, $public_key, $modulus, $keylength)
{
    return decrypt($message, $public_key, $modulus, $keylength);
}

/*
* Some constants
*/



/*
* The actual implementation.
* Requires BCMath support in PHP (compile with --enable-bcmath)
*/

//--
// Calculate (p ^ q) mod r 
//
// We need some trickery to [2]:
// (a) Avoid calculating (p ^ q) before (p ^ q) mod r, because for typical RSA
// applications, (p ^ q) is going to be _WAY_ too large.
// (I mean, __WAY__ too large - won't fit in your computer's memory.)
// (b) Still be reasonably efficient.
//
// We assume p, q and r are all positive, and that r is non-zero.
//
// Note that the more simple algorithm of multiplying $p by itself $q times, and
// applying "mod $r" at every step is also valid, but is O($q), whereas this
// algorithm is O(log $q). Big difference.
//
// As far as I can see, the algorithm I use is optimal; there is no redundancy
// in the calculation of the partial results. 
//--
function pow_mod($p, $q, $r)
{
    // Extract powers of 2 from $q
$factors = array();
    $div = $q;
    $power_of_two = 0;
    while(bccomp($div, "0") == BCCOMP_LARGER)
    {
        $rem = bcmod($div, 2);
        $div = bcdiv($div, 2);
    
        if($rem) array_push($factors, $power_of_two);
        $power_of_two++;
    }

    // Calculate partial results for each factor, using each partial result as a
    // starting point for the next. This depends of the factors of two being
    // generated in increasing order.
$partial_results = array();
    $part_res = $p;
    $idx = 0;
    foreach($factors as $factor)
    {
        while($idx < $factor)
        {
            $part_res = bcpow($part_res, "2");
            $part_res = bcmod($part_res, $r);

            $idx++;
        }
        
        array_pus($partial_results, $part_res);
    }

    // Calculate final result
$result = "1";
    foreach($partial_results as $part_res)
    {
        $result = bcmul($result, $part_res);
        $result = bcmod($result, $r);
    }

    return $result;
}

//--
// Function to add padding to a decrypted string
// We need to know if this is a private or a public key operation [4]
//--
function add_PKCS1_padding($data, $isPublicKey, $blocksize)
{
    $pad_length = $blocksize - 3 - strlen($data);

    if($isPublicKey)
    {
        $block_type = "\x02";
    
        $padding = "";
        for($i = 0; $i < $pad_length; $i++)
        {
            $rnd = mt_rand(1, 255);
            $padding .= chr($rnd);
        }
    }
    else
    {
        $block_type = "\x01";
        $padding = str_repeat("\xFF", $pad_length);
    }
    
    return "\x00" . $block_type . $padding . "\x00" . $data;
}

//--
// Remove padding from a decrypted string
// See [4] for more details.
//--
function remove_PKCS1_padding($data, $blocksize)
{
    assert(strlen($data) == $blocksize);
    $data = substr($data, 1);

    // We cannot deal with block type 0
if($data{0} == '\0')
        die("Block type 0 not implemented.");

    // Then the block type must be 1 or 2 
assert(($data{0} == "\x01") || ($data{0} == "\x02"));

    // Remove the padding
$offset = strpos($data, "\0", 1);
    return substr($data, $offset + 1);
}

//--
// Convert binary data to a decimal number
//--
function binary_to_number($data)
{
    $base = "256";
    $radix = "1";
    $result = "0";

    for($i = strlen($data) - 1; $i >= 0; $i--)
    {
        $digit = ord($data{$i});
        $part_res = bcmul($digit, $radix);
        $result = bcadd($result, $part_res);
        $radix = bcmul($radix, $base);
    }

    return $result;
}

//--
// Convert a number back into binary form
//--
function number_to_binary($number, $blocksize)
{
    $base = "256";
    $result = "";

    $div = $number;
    while($div > 0)
    {
        $mod = bcmod($div, $base);
        $div = bcdiv($div, $base);
        
        $result = chr($mod) . $result;
    }

    return str_pad($result, $blocksize, "\x00", STR_PAD_LEFT);
}


}
?>