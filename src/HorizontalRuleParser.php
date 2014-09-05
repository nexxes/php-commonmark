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

use \nexxes\stmd\token\CharToken;
use \nexxes\stmd\token\Token;

/**
 * HorizontalRuleParser
 *
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
class HorizontalRuleParser implements ParserInterface {
	private $mainParser;
	
	/**
	 * {@inheritdoc}
	 */
	public function __construct($mainParser) {
		$this->mainParser = $mainParser;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function canParse(array &$tokens) {
		if (!($tokens[0] instanceof CharToken)) {
			return false;
		}
		
		$type = $tokens[0]->type;
		$counter = 0;
		
		for ($i=0; $i<\count($tokens); $i++) {
			// Everything up to newline is ok, enough chars found?
			if ($tokens[$i]->type === Token::NEWLINE) {
				return ($counter >= 3);
			}
			
			// Whitespace in between is ok
			if ($tokens[$i]->type === Token::WHITESPACE) {
				continue;
			}
			
			// Found correct char
			if ($tokens[$i]->type === $type) {
				$counter += $tokens[$i]->length;
			} else {
				return false;
			}
		}
		
		return false;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function parse(array &$tokens) {
		// Remove everything up to newline
		while ($tokens[0]->type !== Token::NEWLINE) {
			\array_shift($tokens);
		}
		
		// And the newline
		\array_shift($tokens);
		
		return new struct\HorizontalRule();
	}
}
