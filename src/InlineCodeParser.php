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

namespace nexxes\cm;

use \nexxes\cm\struct\Container;
use \nexxes\cm\struct\InlineCode;
use \nexxes\cm\struct\Literal;
use \nexxes\cm\token\Token;

/**
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 * @link http://jgm.github.io/stmd/spec.html#code-span
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
		$start = null;
		
		for ($i=0; $i<\count($tokens); $i++) {
			// Found a backtick with not more than three backticks in total
			if (($tokens[$i]->type === Token::BACKTICK) && ($tokens[$i]->length <= 3)) {
				// First backtick encounter
				if ($start === null) {
					$start = $tokens[$i];
				}
				
				// Found matching closing backtick
				elseif ($start->length === $tokens[$i]->length) {
					return true;
				}
			}
		}
		
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function parse(array &$tokens) {
		// Remove tokens we do note quote
		$tokens_before = [];
		while ($tokens[0]->type !== Token::BACKTICK) {
			$tokens_before[] = \array_shift($tokens);
		}
		
		// First backtick
		$start = \array_shift($tokens);
		
		// Take everything up to the second delimiter
		$my_tokens = [];
		while (($tokens[0]->type !== Token::BACKTICK) || ($tokens[0]->length !== $start->length)) {
			$token = \array_shift($tokens);
			
			// Replace newlines by spaces
			if ($token->type === Token::NEWLINE) {
				$token->type = Token::WHITESPACE;
			}
			
			// Trim all whitespace
			if ($token->type === Token::WHITESPACE) {
				$token->raw = ' ';
				$token->length = 1;
				
				// Skip whitespace if previous token is already whitespace
				if (isset($my_tokens[\count($my_tokens)-1]) && ($my_tokens[\count($my_tokens)-1]->type === Token::WHITESPACE)) {
					continue;
				}
			}
			
			// Store tokens
			$my_tokens[] = $token;
		}
		
		// Remove closing backtick
		\array_shift($tokens);
		
		// Remove leading and trailing whitespace
		if (isset($my_tokens[0]) && ($my_tokens[0]->type === Token::WHITESPACE)) {
			\array_shift($my_tokens);
		}
		if (isset($my_tokens[\count($my_tokens)-1]) && ($my_tokens[\count($my_tokens)-1]->type === Token::WHITESPACE)) {
			\array_pop($my_tokens);
		}
		
		return new Container($this->mainParser->parseInline($tokens_before), new InlineCode(new Literal($my_tokens)));
	}

}
