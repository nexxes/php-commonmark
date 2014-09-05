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

use \nexxes\stmd\struct\InlineCode;
use \nexxes\stmd\token\Token;

/**
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
class InlineCodeParser implements ParserInterface {
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
		if (!isset($tokens[0]) || ($tokens[0]->type !== Token::BACKTICK)) {
			return false;
		}
		
		for ($i=1; $i<\count($tokens); $i++) {
			if ($tokens[$i]->type === Token::BACKTICK) {
				return true;
			}
			
			if ($tokens[$i]->type === Token::NEWLINE) {
				return false;
			}
		}
		
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function parse(array &$tokens) {
		// Remove first backtick
		\array_shift($tokens);
		
		$my_tokens = [];
		
		while ($tokens[0]->type !== Token::BACKTICK) {
			$my_tokens = \array_shift($tokens);
		}
		
		// Remove second backtick
		\array_shift($tokens);
		
		return new InlineCode($this->mainParser->inlineParse($my_tokens));
	}

}
