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

namespace nexxes\cm\parser;

use \nexxes\cm\structure\Block;

/**
 *
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
interface ParserInterface {
	/**
	 * Inject the main parser for parsing of nested blocks
	 * @param type $mainParser
	 */
	function __construct($mainParser);
	
	/**
	 * Checks if this parser can interrupt a paragraph and can parse the next line of input
	 * 
	 * @param \nexxes\cm\structure\Block $context
	 * @param array<\nexxes\tokenizer\Token> $tokens
	 * @return bool
	 */
	function canInterrupt(Block $context, array $tokens);
	
	/**
	 * Checks if this parser can parse the next line of input
	 * 
	 * @param \nexxes\cm\structure\Block $context
	 * @param array<\nexxes\tokenizer\Token> $tokens
	 * @return bool
	 */
	function canParse(Block $context, array $tokens);
	
	/**
	 * Parse from the supplied token stream and create a new structure representation.
	 * This representation is appended to $parent.
	 * The remaining tokens are returned.
	 * 
	 * @param array<\nexxes\cm\structure\Block> $parent
	 * @param array<\nexxes\tokenizer\Token> $tokens
	 * @return array<\nexxes\tokenizer\Token>
	 */
	function parse(Block $parent, array $tokens);
}
