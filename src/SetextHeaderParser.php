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

use \nexxes\stmd\token\Token;

/**
 * Description of SetextHeaderParser
 *
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
class SetextHeaderParser implements ParserInterface {
	/**
	 * @var Parser
	 */
	private $mainParser;
	
	/**
	 * {@inheritdocs}
	 */
	public function __construct($mainParser) {
		$this->mainParser = $mainParser;
	}
	
	/**
	 * {@inheritdocs}
	 */
	public function canParse(array &$tokens) {
		$count = \count($tokens);
		
		for($i=0; $i<$count; $i++) {
			if ($tokens[$i]->type === Token::NEWLINE) {
				$i++; // Move to next line
				if ($i >= $count) { return false; } // Nothing left to parse ...
				
				// Check for indentation
				if ($tokens[$i]->type === Token::WHITESPACE) {
					// Not a simple indention, so fail
					if ($tokens[$i]->length > 3) {
						return false;
					}
					
					$i++; // Indention, check next
					if ($i >= $count) { return false; } // Nothing left to parse ...
				}
				
				// Next token is no underlining
				if (($tokens[$i]->type !== Token::EQUALS) && ($tokens[$i]->type !== Token::MINUS)) {
					return false;
				}
				
				$i++; // Current token is underlining, check next
				if ($i >= $count) { return true; } // Nothing left to parse, so we reached file ending
				
				// Trailing spaces allowed
				if ($tokens[$i]->type === Token::WHITESPACE) {
					$i++; // Current token is trailing white space, check that line is finished
					if ($i >= $count) { return true; } // Nothing left to parse, so we reached file ending
				}
				
				// Line must be finished, if some text follows, thats illegal!
				return ($tokens[$i]->type === Token::NEWLINE);
			}
		}
		
		return false;
	}

	/**
	 * {@inheritdocs}
	 */
	public function parse(array &$tokens) {
		$my_tokens = [];
		
		// Read complete first line
		while (\count($tokens) && ($tokens[0]->type !== Token::NEWLINE)) {
			$my_tokens[] = \array_shift($tokens);
		}
		
		\array_shift($tokens); // Remove newline
		$this->mainParser->removeIndentation($tokens);
		$type = $tokens[0]->char; // Which header is it? = or - ?
		
		// Remove second line
		do {
			$token = \array_shift($tokens);
		}	while (\count($tokens) && ($token->type !== Token::NEWLINE));
		
		return new struct\Header(($type === "=" ? 1 : 2), $this->mainParser->parseInline($my_tokens));
	}
}
