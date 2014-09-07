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

use \nexxes\tokenizer\Token;
use \nexxes\cm\structure\Block;
use \nexxes\cm\structure\Type;

/**
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 * @link http://jgm.github.io/stmd/spec.html#setext-headers
 */
class SetextHeaderParser implements ParserInterface {
	const TYPE = Type::LEAF_SETEXT;
	
	/**
	 * @var \nexxes\cm\Parser
	 */
	private $mainParser;
	
	/**
	 * {@inheritdocs}
	 */
	public function __construct($mainParser) {
		$this->mainParser = $mainParser;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function canInterrupt(Block $context, array $tokens) {
		return false;
	}
	
	/**
	 * {@inheritdocs}
	 */
	public function canParse(Block $context, array $tokens) {
		// Exceeds allowed indentation
		if (($tokens[0]->type === Token::WHITESPACE) && ($tokens[0]->length > 3)) {
			return false;
		}
		
		// The first newline
		if (false === ($pos = $this->mainParser->nextToken($tokens, Token::NEWLINE))) {
			return false;
		}
		
		// Empty line is denied
		if ($pos === 0) {
			return false;
		}
		
		// There must be something in the second line
		if (!isset($tokens[$pos+1])) {
			return false;
		} else {
			$pos++;
		}
		
		if ($tokens[$pos]->type === Token::WHITESPACE) {
			// Allowed indentation
			if ($tokens[$pos]->length > 3) {
				return false;
			} else {
				$pos++;
			}
		}
		
		// Now the marker must follow
		if (!isset($tokens[$pos]) || !\in_array($tokens[$pos]->type, [ Token::MINUS, Token::EQUALS, ])) {
			return false;
		} else {
			$pos++;
		}
		
		// There maybe whitespace
		while (isset($tokens[$pos]) && ($tokens[$pos]->type === Token::WHITESPACE)) {
			$pos++;
		}
		
		// Now follows a newline or EOF
		return (!isset($tokens[$pos]) || ($tokens[$pos]->type === Token::NEWLINE));
	}

	/**
	 * {@inheritdocs}
	 */
	public function parse(Block $parent, array $tokens) {
		$newline1 = $this->mainParser->nextToken($tokens, Token::NEWLINE);
		
		// Copy up until second newline
		if (false !== ($newline2 = $this->mainParser->nextToken($tokens, Token::NEWLINE, $newline1+1))) {
			$newline2++;
			
			// Eat blank lines
			while (false !== ($shift = $this->mainParser->isBlankLine($tokens, $newline2))) {
				$newline2 += $shift;
			}
			
			$my_tokens = \array_slice($tokens, 0, $newline2);
			$tokens = \array_slice($tokens, $newline2);
		} 
		
		// Copy all data, no second newline present
		else { 
			$my_tokens = $tokens;
			$tokens = [];
		}
		
		$parent[] = $header = new Block(Type::LEAF_SETEXT, $parent, $my_tokens);
		$this->parseInline($header);
		return $tokens;
	}
	
	public function parseInline(Block $header) {
		$tokens = $header->getTokens();
		$newline = $this->mainParser->nextToken($tokens, Token::NEWLINE);
		
		// Only a single token should remain in the second line after trimming: the underlining
		$secondline = $this->mainParser->trim(\array_slice($tokens, $newline+1));
		$header->meta['level'] = ($secondline[0]->char === '=' ? 1 : 2);
		
		$firstline = $this->mainParser->trim(\array_slice($tokens, 0, $newline));
		
		foreach ($firstline AS $token) {
			$header->inline .= $token->raw;
		}
	}
}
