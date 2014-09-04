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

namespace nexxes\stmd\token;

/**
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
class Tokenizer {
	/**
	 * Data to tokenize
	 * @var string
	 */
	private $raw;
	
	/**
	 * String length of $raw
	 * @var int
	 */
	private $length;
	
	/**
	 * Char position in $raw
	 * @var type 
	 */
	private $pos = 0;
	
	/**
	 * Line position in the current tokenizing process
	 * @var int
	 */
	private $line = 0;
	
	/**
	 * Current char position in $line
	 * @var int
	 */
	private $column = 0;
	
	/**
	 * List of generated tokens
	 * @var array<Token>
	 */
	private $tokens = [];
	
	
	
	
	public function __construct($data) {
		$this->raw = $data;
		$this->length = \strlen($data);
	}
	
	public function run() {
		$tokenizers = [
			'tokenizeNewline',
			
			// Tokenizer methods that read a single char
			'tokenizeColon',
			'tokenizeSingleQuote',
			'tokenizeDoubleQuote',
			'tokenizeLeftSquareBracket',
			'tokenizeRightSquareBracket',
			'tokenizeLeftParenthesis',
			'tokenizeRightParenthesis',
			'tokenizeLeftAngularBracket',
			'tokenizeRightAngularBracket',
			
			// Tokenizer methods with multiple chars of the same kind
			'tokenizeBacktick',
			'tokenizeEquals',
			'tokenizeHash',
			'tokenizeMinus',
			'tokenizeStar',
			'tokenizeTilde',
			'tokenizeUnderscore',
		];
		
		
		while ($this->pos < $this->length) {
			foreach ($tokenizers AS $tokenizer) {
				if ($this->{$tokenizer}()) {
					continue 2;
				}
			}
			
			$this->pos++;
		}
		
		return $this->tokens;
	}
	
	private function tokenizeNewline() {
		$found = '';
		
		if (($this->pos < $this->length) && ($this->raw[$this->pos] === "\r")) {
			$found .= "\r";
			$this->pos++;
		}
		
		if (($this->pos < $this->length) && ($this->raw[$this->pos] === "\n")) {
			$found .= "\n";
			$this->pos++;
		}
		
		if ($found === '') {
			return false;
		}
		
		$this->tokens[] = new NewlineToken(Token::NEWLINE, $this->line, $this->column, $found);
		$this->line++;
		$this->column = 0;
		
		return true;
	}
	
	private function tokenizeColon() {
		return $this->tokenizeChar(':', Token::COLON);
	}
	
	private function tokenizeSingleQuote() {
		return $this->tokenizeChar('\'', Token::SINGLE_QUOTE);
	}
	
	private function tokenizeDoubleQuote() {
		return $this->tokenizeChar('"', Token::DOUBLE_QUOTE);
	}
	
	private function tokenizeLeftSquareBracket() {
		return $this->tokenizeChar('[', Token::SQUARE_BRACKET_LEFT);
	}
	
	private function tokenizeRightSquareBracket() {
		return $this->tokenizeChar(']', Token::SQUARE_BRACKET_RIGHT);
	}
	
	private function tokenizeLeftParenthesis() {
		return $this->tokenizeChar('(', Token::PARENTHESIS_LEFT);
	}
	
	private function tokenizeRightParenthesis() {
		return $this->tokenizeChar(')', Token::PARENTHESIS_RIGHT);
	}
	
	private function tokenizeLeftAngularBracket() {
		return $this->tokenizeChar('<', Token::ANGLE_BRACKET_LEFT);
	}
	
	private function tokenizeRightAngularBracket() {
		return $this->tokenizeChar('>', Token::ANGLE_BRACKET_RIGHT);
	}
	
	private function tokenizeChar($char, $tokenType) {
		if (!isset($this->raw[$this->pos]) || ($this->raw[$this->pos] !== $char)) {
			return false;
		}
		
		$this->tokens[] = new CharToken($tokenType, $this->line, $this->column, $char);
		$this->pos++;
		$this->column++;
		
		return true;
	}
	
	private function tokenizeBacktick() {
		return $this->tokenizeChars('`', Token::BACKTICK);
	}
	
	private function tokenizeEquals() {
		return $this->tokenizeChars('=', Token::EQUALS);
	}
	
	private function tokenizeHash() {
		return $this->tokenizeChars('#', Token::HASH);
	}
	
	private function tokenizeMinus() {
		return $this->tokenizeChars('-', Token::MINUS);
	}
	
	private function tokenizeStar() {
		return $this->tokenizeChars('*', Token::STAR);
	}
	
	private function tokenizeTilde() {
		return $this->tokenizeChars('~', Token::TILDE);
	}
	
	private function tokenizeUnderscore() {
		return $this->tokenizeChars('_', Token::UNDERSCORE);
	}
	
	private function tokenizeChars($char, $tokenType) {
		// Char not matching
		if (!isset($this->raw[$this->pos]) || ($this->raw[$this->pos] !== $char)) {
			return false;
		}
		
		$count = 0;
		$raw = '';
		
		do {
			$count++;
			$raw .= $char;
			$this->pos++;
		} while (($this->pos < $this->length) && ($this->raw[$this->pos] === $char));
		
		$this->tokens[] = new CharToken($tokenType, $this->line, $this->column, $raw);
		$this->column += $count;
		
		return true;
	}
	
	
	
}
