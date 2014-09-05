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
use \nexxes\stmd\token\Tokenizer;

/**
 * Description of Parser
 *
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
class Parser {
	const HORIZONTAL_RULE = 1;
	const SETEXT_HEADER = 2;
	const PARAGRAPH = 3;
	
	const INLINE_CODE = 'Inline_Code';
	
	private $parsers = [];
	
	
	public function __construct() {
		$this->parsers[self::HORIZONTAL_RULE] = new HorizontalRuleParser($this);
		$this->parsers[self::SETEXT_HEADER] = new SetextHeaderParser($this);
		$this->parsers[self::PARAGRAPH] = new ParagraphParser($this);
		$this->parsers[self::INLINE_CODE] = new InlineCodeParser($this);
	}
	
	public function parseString($string) {
		//$preprocessed = $this->preprocess($string);
		//$stream = \explode("\n", $preprocessed);
		
		//return $this->parse($stream);
		
		$tokenizer = new Tokenizer($string);
		$tokens = $tokenizer->run();
		
		//print_r($tokens);
		
		return $this->parse($tokens);
	}
	
	public function parse(array &$tokens) {
		$struct = [];
		
		//print_r($tokens);
		
		while (\count($tokens)) {
			// Remove one to three spaces indent
			if (($tokens[0]->type === Token::WHITESPACE) && ($tokens[0]->length <= 3)) {
				\array_shift($tokens);
				continue;
			}
			
			if ($tokens[0]->type === Token::BLANKLINE) {
				\array_shift($tokens);
				continue;
			}
			
			if ($this->parsers[self::HORIZONTAL_RULE]->canParse($tokens)) {
				$struct[] = $this->parsers[self::HORIZONTAL_RULE]->parse($tokens);
			}
			
			elseif ($this->parsers[self::SETEXT_HEADER]->canParse($tokens)) {
				$struct[] = $this->parsers[self::SETEXT_HEADER]->parse($tokens);
			}
			
			elseif ($this->parsers[self::PARAGRAPH]->canParse($tokens)) {
				$struct[] = $this->parsers[self::PARAGRAPH]->parse($tokens);
			}
			
			// Avoid enless loops, eat unparsable stuff
			else {
				$struct[] = new struct\Literal(\array_shift($tokens));
			}
		}
		
		return $struct;
	}
	
	public function parseInline(array &$tokens) {
		$struct = [];
		
		while (\count($tokens)) {
			// Inline parsing should only read a single line
			if ($tokens[0]->type === Token::NEWLINE) {
				\array_shift($tokens);
				break;
			}
			
			if ($this->parsers[self::INLINE_CODE]->canParse($tokens)) {
				$struct[] = $this->parsers[self::INLINE_CODE]->parse($tokens);
			}
			
			else {
				$struct[] = new struct\Literal(\array_shift($tokens));
			}
		}
		
		return $struct;
	}
	
	/**
	 * Translate tabs into spaces and normalize newlines
	 * 
	 * @param string $string
	 * @return string
	 * @link http://jgm.github.io/stmd/spec.html#preprocessing
	 */
	private function preprocess($string) {
		$string1 = \str_replace("\t", '    ', $string);
		$string2 = \str_replace("\r\n", "\n", $string1);
		$string3 = \str_replace("\r", "\n", $string2);
		return $string3;
	}
	
	
	/**
	 * Check if someone wants to interrupt a paragraph
	 * @param array<\nexxes\stmd\token\Token> $tokens
	 * @return boolean
	 */
	public function canInterrupt(array &$tokens) {
		$this->removeIndentation($tokens);
		
		// Empty tokenlist
		if (!\count($tokens)) {
			return false;
		}
		
		if ($this->parsers[self::HORIZONTAL_RULE]->canParse($tokens)) {
			return true;
		}
		
		else {
			return false;
		}
	}
	
	
	/**
	 * Try to eat up to three spaces indentation.
	 * 
	 * @param array<\nexxes\stmd\token\Token> $tokens
	 * @return boolean
	 */
	public function removeIndentation(array &$tokens) {
		// Nothing to eat
		if (!\count($tokens)) {
			return false;
		}
		
		// Can only eat whitespace
		if ($tokens[0]->type !== Token::WHITESPACE) {
			return false;
		}
		
		// Eat only 3 spaces
		if ($tokens[0]->length > 3) {
			return false;
		}
		
		\array_shift($tokens);
		return true;
	}
}
