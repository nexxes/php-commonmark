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
 * Description of Token
 *
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
class Token {
	const WHITESPACE = "Token_Whitespace";
	const NEWLINE = "Token_Newline";
	const TEXT = "Token_Text";
	const BLANKLINE = "Token_Blankline";
	const ESCAPED = 5;
	
	const MINUS = 100;
	const EQUALS = 101;
	const HASH = 102;
	const STAR = 103;
	const TILDE = 104;
	const UNDERSCORE = 105;
	const BACKTICK = 106;
	const SINGLE_QUOTE = 107;
	const DOUBLE_QUOTE = 108;
	const COLON = 109;
	const SLASH = 110;
	const BACKSLASH = 111;
	const BANG = 112;
	const QUESTION = 113;
	
	const PARENTHESIS_LEFT = 200;
	const PARENTHESIS_RIGHT = 201;
	const SQUARE_BRACKET_LEFT = 202;
	const SQUARE_BRACKET_RIGHT = 203;
	const CURLY_BRACKET_LEFT = 204;
	const CURLY_BRACKET_RIGHT = 205;
	const ANGLE_BRACKET_LEFT = 206;
	const ANGLE_BRACKET_RIGHT = 207;
	
	const HTML = 300;
	const HTML_COMMENT = 301;
	const CDATA = 302;
	const PROCESSING_INSTRUCTIONS = 303;
	const DECLARATION = 304;
	const TAGNAME = 305;
	const ATTRIBUTE_NAME = 306;
	const ATTRIBUTE_VALUE = 307;
	
	/**
	 * The actual type of the token
	 * @var mixed
	 */
	public $type;
	
	/**
	 * The line in the original token stream
	 * @var int
	 */
	public $line;
	
	/**
	 * The start position in the line the token appeared
	 * @var int
	 */
	public $pos;
	
	/**
	 * The raw content of the token
	 * @var string 
	 */
	public $raw;
	
	/**
	 * Length of the token
	 * @var int 
	 */
	public $length;
	
	public function __construct($type, $line, $pos, $raw) {
		$this->type = $type;
		$this->line = $line;
		$this->pos = $pos;
		$this->raw = $raw;
		$this->length = \strlen($this->raw);
	}
}
