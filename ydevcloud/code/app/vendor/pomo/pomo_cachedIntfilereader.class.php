<?php
/**
 * Allows reading integers from a file.
 */
class POMO_CachedIntFileReader extends POMO_CachedFileReader {

	var $endian = 'little';

	/**
	 * Opens a file and caches it.
	 *
	 * @param $filename string name of the file to be opened
	 * @param $endian string endianness of the words in the file, allowed
	 * 	values are 'little' or 'big'. Default value is 'little'
	 */
	public function __construct($filename, $endian = 'little') {
		$this->endian = $endian;
		parent::__construct($filename);
	}

	/**
	 * Sets the endianness of the file.
	 *
	 * @param $endian string 'big' or 'little'
	 */
	function setEndian($endian) {
		$this->endian = $endian;
	}

	/**
	 * Reads a 32bit Integer from the Stream
	 *
	 * @return mixed The integer, corresponding to the next 32 bits from
	 * 	the stream of false if there are not enough bytes or on error
	 */
	function readint32() {
		$bytes = $this->read(4);
		if (4 != $this->_strlen($bytes))
			return false;
		$endian_letter = ('big' == $this->endian)? 'N' : 'V';
		$int = unpack($endian_letter, $bytes);
		return array_shift($int);
	}

	/**
	 * Reads an array of 32-bit Integers from the Stream
	 *
	 * @param integer count How many elements should be read
	 * @return mixed Array of integers or false if there isn't
	 * 	enough data or on error
	 */
	function readint32array($count) {
		$bytes = $this->read(4 * $count);
		if (4*$count != $this->_strlen($bytes))
			return false;
		$endian_letter = ('big' == $this->endian)? 'N' : 'V';
		return unpack($endian_letter.$count, $bytes);
	}
}
?>