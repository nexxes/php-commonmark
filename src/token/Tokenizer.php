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
	private $line = 1;
	
	/**
	 * Current char position in $line
	 * @var int
	 */
	private $column = 1;
	
	/**
	 * List of generated tokens
	 * @var array<Token>
	 */
	private $tokens = [];
	
	
	
	
	public function __construct($data) {
		$this->raw = $data;
		$this->length = \strlen($data);
	}
	
	/**
	 * Get the list of tokens created in the last run
	 * @return array<Token>
	 */
	public function getTokens() {
		return $this->tokens;
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
		$this->column = 1;
		
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
	
	/**
	 * Try to tokenize whitespace. If $space is supplied, do not try to read previously gathered space but use $space instead
	 * @param string $space
	 */
	private function tokenizeWhitespace($space = null) {
		if ($space === null) {
			$space = $this->readWhitespace($this->pos);
		}
		
		if ($space === false) {
			return false;
		}
		
		$this->tokens[] = new Token(Token::WHITESPACE, $this->line, $this->column, $space);
		$this->column += \strlen($space);
		$this->pos += \strlen($space);
		
		return true;
	}
	
	/**
	 * Try to read white space from the supplied position
	 * White space is only space and tab here.
	 * Returns false if no white space was available
	 * 
	 * @param int $pos
	 * @return string
	 */
	private function readWhitespace($pos) {
		$space = '';
		
		while (($pos < $this->length) && (($this->raw[$pos] === "\t") || ($this->raw[$pos] === ' '))) {
			$space .= $this->raw[$pos];
			$pos++;
		}
		
		if ($space === '') {
			return false;
		} else {
			return $space;
		}
	}
	
	/**
	 * Try to read an HTML comment from the string to tokenize
	 * @return boolean
	 * @link http://jgm.github.io/stmd/spec.html#html-comment
	 */
	private function tokenizeHTMLComment() {
		return $this->tokenizeMultilineRawData(Token::HTML_COMMENT, '<!--', '-->', '--');
	}
	
	/**
	 * Read a processing instruction
	 * @return boolean
	 * @link http://jgm.github.io/stmd/spec.html#processing-instruction
	 */
	private function tokenizeProcessingInstruction() {
		return $this->tokenizeMultilineRawData(Token::PROCESSING_INSTRUCTIONS, '<?', '?>');
	}
	
	/**
	 * Read a CDATA section
	 * @return boolean
	 * @link http://jgm.github.io/stmd/spec.html#cdata-section
	 */
	private function tokenizeCData() {
		return $this->tokenizeMultilineRawData(Token::CDATA, '<![CDATA[', ']]>');
	}
	
	/**
	 * Try to parse raw data like HTML comments, processing data, CDATA, etc
	 * 
	 * @param string $startDelimiter String this block starts with
	 * @param string $endDelimiter String this block ends with
	 * @param string $doNotContain String that must noch exists within, defaults to endDelimiter
	 * @return boolean
	 */
	private function tokenizeMultilineRawData($tokenType, $startDelimiter, $endDelimiter, $doNotContain = null) {
		// Test if start delimiter follows
		if (\substr($this->raw, $this->pos, \strlen($startDelimiter)) !== $startDelimiter) {
			return false;
		}
		
		// Try to find end delimiter
		if (false === ($endpos = \strpos($this->raw, $endDelimiter, $this->pos+\strlen($startDelimiter)))) {
			throw new \RuntimeException('Can not find end delimiter ' . $endDelimiter . ' for start delimiter ' . $startDelimiter . ' in line ' . $this->line . ', character ' . $this->pos);
		}
		
		// Search for $doNotContain element
		if (($doNotContain !== null) && (false !== ($failpos = \strpos($this->raw, $doNotContain, $this->pos + \strlen($startDelimiter)))) && ($failpos < $endpos)) {
			throw new \RuntimeException('Found illegal string sequence ' . $doNotContain . ' for start delimiter ' . $startDelimiter . ' in line ' . $this->line . ', character ' . $this->pos);
		}
		
		$tokenLength = $endpos - $this->pos + \strlen($endDelimiter);
		$tokenText = \substr($this->raw, $this->pos, $tokenLength);
		$this->tokens[] = new Token($tokenType, $this->line, $this->column, $tokenText);
		
		$this->line += $this->countLinebreaks($tokenText);
		$this->column = $this->lastLineLength($tokenText)+1;
		$this->pos += $tokenLength;
		
		return true;
	}
	
	/**
	 * Read an attribute from the input.
	 * An attribute is preceeded by whitespace and has an optional value.
	 * 
	 * @return boolean
	 * @link http://jgm.github.io/stmd/spec.html#attribute
	 */
	public function tokenizeAttribute() {
		// Attribute is separated by space
		if (false === ($space = $this->readWhitespace($this->pos))) {
			return false;
		}
		
		// Try to read tag name
		if (false === ($name = $this->readAttributeName($this->pos + \strlen($space)))) {
			return false;
		}
		
		$this->tokenizeWhitespace($space);
		
		$this->tokens[] = new Token(Token::ATTRIBUTE_NAME, $this->line, $this->column, $name);
		$this->column += \strlen($name);
		$this->pos += \strlen($name);
		
		// Read attributes
		$this->tokenizeAttributeValue();
	}
	
	/**
	 * Read an attribute value from the input.
	 * 
	 * An attribute value can be unquoted or quoted bei either single or double quotes
	 * 
	 * @return boolean
	 * @link http://jgm.github.io/stmd/spec.html#attribute-value
	 */
	private function tokenizeAttributeValue() {
		$pos = $this->pos;
		
		// Attribute name and = may be separated by space
		if (false !== ($space1 = $this->readWhitespace($pos))) {
			$pos += \strlen($space1);
		}
		
		// Can not read =
		if (($pos >= $this->length) || ($this->raw[$pos] !== '=')) {
			return false;
		}
		$pos++;
		
		// = and attribute value may be separated by space
		if (false !== ($space2 = $this->readWhitespace($pos))) {
			$pos += \strlen($space2);
		}
		
		// Try to read quoted string
		if (($pos < $this->length) && (($this->raw[$pos] === '\'') || ($this->raw[$pos] === '"'))) {
			$delimiter = $this->raw[$pos];
			
			if (false === ($endpos = \strpos($this->raw, $delimiter, $pos+1))) {
				throw new \RuntimeException('Can not find delimiter ' . $delimiter . ' for attribute value starting in line ' . $this->line . ' on position ' . $this->pos);
			}
			
			$type = ($delimiter === '"' ? AttributeValueToken::DOUBLE_QUOTED : AttributeValueToken::SINGLE_QUOTED);
		}
		
		// Read unquoted string
		else {
			$type = AttributeValueToken::UNQUOTED;
			$delimiter = '';
			
			$endpos = $pos;
			while (($endpos < $this->length) && (false === \strpos('\'"=<>`', $this->raw[$endpos]))) {
				$endpos++;
			}
			
			if ($pos === $endpos) {
				throw new \RuntimeException('Can not find attribute value starting in line ' . $this->line . ' on position ' . $this->pos);
			}
		}
		
		$this->tokenizeWhitespace($space1);
		$this->tokenizeEquals();
		$this->tokenizeWhitespace($space2);
		
		$raw = \substr($this->raw, $this->pos + \strlen($delimiter), $endpos - $this->pos - \strlen($delimiter));
		$this->tokens[] = new AttributeValueToken($this->line, $this->pos, $raw, $type);
		$this->pos += \strlen($raw) + (2*\strlen($delimiter));
		$this->column += \strlen($raw) + (2*\strlen($delimiter));
		return true;
	}
	
	/**
	 * Try to read an attribute name
	 * @param int $pos
	 * @return string
	 */
	private function readAttributeName($pos) {
		// Try to read attribute name
		if (($pos >= $this->length) || (false === \stripos('abcdefghijklmnopqrstuvwxyz_:', $this->raw[$pos]))) {
			return false;
		}
		
		$name = $this->raw[$pos];
		$pos++;
		
		while (($pos < $this->length) && (false !== \stripos('abcdefghijklmnopqrstuvwxyz0123456789_.:-', $this->raw[$pos]))) {
			$name .= $this->raw[$pos];
			$pos++;
		}
		
		return $name;
	}
	
	/**
	 * Count the number of line breaks in a string
	 * @param string $string
	 * @return int
	 */
	private function countLinebreaks(&$string) {
		// Calculate number of lines 
		$rn = \substr_count($string, "\r\n");
		$r = \substr_count($string, "\r") - $rn;
		$n = \substr_count($string, "\n") - $rn;
		
		return $rn + $r + $n;
	}
	
	/**
	 * Calculate the number of bytes in the last line of the supplied string.
	 * @param string $string
	 * @return int
	 */
	private function lastLineLength(&$string) {
		$r = \strrpos($string, "\r");
		$n = \strrpos($string, "\n");
		
		if (($r === false) && ($n === false)) {
			return \strlen($string);
		} else {
			return \strlen($string) - (\max($r, $n)+1);
		}
	}
}
