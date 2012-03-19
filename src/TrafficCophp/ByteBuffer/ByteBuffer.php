<?php

namespace TrafficCophp\ByteBuffer;

use TrafficCophp\ByteBuffer\Struct;
use TrafficCophp\ByteBuffer\LengthMap;

/**
 * ByteBuffer
 */
class ByteBuffer {

	const DEFAULT_FORMAT = 'x';
	const RESERVED = '__RESERVED__';
	const BLANK = '__BLANK__';

	/**
	 * @var \SplFixedArray
	 */
	protected $structs;

	/**
	 * @var LengthMap
	 */
	protected $lengthMap;

	public function __construct($length) {
		$this->lengthMap = new LengthMap();
		$this->structs = new \SplFixedArray($length);
		$this->initializeStructs($length);
	}

	protected function initializeStructs($length) {
		for ($i = 0; $i < $length; $i++) {
			$this->structs[$i] = self::BLANK;
		}
	}

	protected function insert($format, $value, $offset, $length) {
		$this->structs[$offset] = new Struct($format, $value, $length);
		for ($i = 1; $i < $length; $i++) {
			$this->structs[$offset + $i] = self::RESERVED;
		}
	}

	protected function checkForOverSize($excpected_max, $actual) {
		if ($actual > $excpected_max) {
			throw new \InvalidArgumentException(sprintf('%d exceeded limit of %d', $actual, $excpected_max));
		}
	}

	public function __toString() {
		$buf = '';
		for ($i = 0; $i < count($this->structs); $i++) {
			if ($this->structs[$i] instanceof Struct) {
				$struct = $this->structs[$i];
				$buf .= pack($struct->getFormat(), $struct->getValue());
			} else if ($this->structs[$i] === self::RESERVED) {
				// do nothing atm
			} else if ($this->structs[$i] === self::BLANK) {
				$buf .= pack(self::DEFAULT_FORMAT);
			}
		}
		return $buf;
	}

	public function length() {
		return count($this->structs);
	}

	public function write($string, $offset) {
		$length = strlen($string);
		$this->insert('a' . $length, $string, $offset, $length);
	}

	public function writeInt8($value, $offset) {
		$format = 'c';
		$this->checkForOverSize(0xff, $value);
		$this->insert($format, $value, $offset, $this->lengthMap->getLengthFor($format));
	}

	public function writeInt16BE($value, $offset) {
		$format = 'n';
		$this->checkForOverSize(0xffff, $value);
		$this->insert($format, $value, $offset, $this->lengthMap->getLengthFor($format));
	}

	public function writeInt16LE($value, $offset) {
		$format = 'v';
		$this->checkForOverSize(0xffff, $value);
		$this->insert($format, $value, $offset, $this->lengthMap->getLengthFor($format));
	}

	public function writeInt32BE($value, $offset) {
		$format = 'N';
		$this->checkForOverSize(0xffffffff, $value);
		$this->insert($format, $value, $offset, $this->lengthMap->getLengthFor($format));
	}

	public function writeInt32LE($value, $offset) {
		$format = 'V';
		$this->checkForOverSize(0xffffffff, $value);
		$this->insert($format, $value, $offset, $this->lengthMap->getLengthFor($format));
	}

}