<?php

/*
 * The MIT License
 *
 * Copyright 2014 Dennis Birkholz <dennis.birkholz@nexxes.net>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace nexxes\stmd;

/**
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
class TestReader {
	/**
	 * Path name to the specification
	 * @var string
	 */
	private $specification;
	
	/**
	 * Lines of the specification file
	 * @var array<string>
	 */
	private $lines = [];
	
	/**
	 * Contains the line number of the start indicator for each example
	 * @var array<int>
	 */
	private $examples = [];
	
	
	
	
	public function __construct($specification) {
		if (!\file_exists($specification)) {
			throw new \InvalidArgumentException('Can not open specification file "' . $specification . '"');
		}
		$this->specification = $specification;
	}
	
	private function init() {
		// Initialize only once
		if (\count($this->examples)) { return; }
		
		$this->lines = \file($this->specification, FILE_IGNORE_NEW_LINES);
		
		$start = false;
		$middle = false;
		
		for ($i=0; $i<\count($this->lines); ++$i) {
			// Found text marker
			if ($this->lines[$i] === '.') {
				// Found end marker
				if ($middle) {
					$middle = false;
					$start = false;
				}
				
				// Found middle marker
				elseif ($start) {
					$middle = true;
				}
				
				// Found start marker
				else {
					$this->examples[] = $i;
					$start = true;
				}
			}
		}
	}
	
	/**
	 * Get the data for an examplem example starts with 1
	 * @param type $example
	 */
	public function getExample($example) {
		$this->init();
		
		$in = '';
		$out = '';
		
		$start = false;
		$middle = false;
		
		for($i=$this->examples[$example-1]; $i<\count($this->lines); ++$i) {
			if ($this->lines[$i] === '.') {
				if ($middle) {
					break;
				} elseif ($start) {
					$middle = true;
				} else {
					$start = true;
				}
			}
			
			else {
				if ($middle) {
					$out .= (\strlen($out) ? PHP_EOL : '') . $this->lines[$i];
				} elseif ($start) {
					$in .= (\strlen($in) ? PHP_EOL : '') . $this->lines[$i];
				}
			}
		}
		
		return ['in' => $in, 'out' => $out];
	}
}
