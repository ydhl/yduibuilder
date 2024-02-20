<?php

/**
 * Classes, which help reading streams of data from files.
 * Based on the classes from Danilo Segan <danilo@kvota.net>
 *
 * @version $Id: class.POMO_StringReader.php 259 2010-02-21 14:03:48Z liizii $
 * @package pomo
 * @subpackage streams
 */

/**
 * Provides file-like methods for manipulating a string instead
 * of a physical file.
 */
class POMO_StringReader {

    protected $_pos;
    protected $_str;

    public function __construct($str = '') {
        $this->_str = $str;
        $this->_pos = 0;
        $this->is_overloaded = ((ini_get("mbstring.func_overload") & 2) != 0) && function_exists('mb_substr');
    }

    function _substr($string, $start, $length) {
        if ($this->is_overloaded) {
            return mb_substr($string, $start, $length, 'ascii');
        } else {
            return substr($string, $start, $length);
        }
    }

    function _strlen($string) {
        if ($this->is_overloaded) {
            return mb_strlen($string, 'ascii');
        } else {
            return strlen($string);
        }
    }

    function read($bytes) {
        $data = $this->_substr($this->_str, $this->_pos, $bytes);
        $this->_pos += $bytes;
        if ($this->_strlen($this->_str) < $this->_pos)
            $this->_pos = $this->_strlen($this->_str);
        return $data;
    }

    function seekto($pos) {
        $this->_pos = $pos;
        if ($this->_strlen($this->_str) < $this->_pos)
            $this->_pos = $this->_strlen($this->_str);
        return $this->_pos;
    }

    function pos() {
        return $this->_pos;
    }

    function length() {
        return $this->_strlen($this->_str);
    }

}

?>
