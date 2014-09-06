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

namespace nexxes\stmd\parser;

use \nexxes\stmd\token\Token;
use nexxes\stmd\structure\Block;
use nexxes\stmd\structure\Type;

/**
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 * @link http://jgm.github.io/stmd/spec.html#atx-headers
 */
class ATXHeaderParser implements ParserInterface {
	const TYPE = Type::LEAF_ATX;
	
	/**
	 * @var \nexxes\stmd\Parser
	 */
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
	public function canInterrupt(array $tokens) {
		return $this->canParse($tokens);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function canParse(array $tokens) {
		print_r($tokens);
		
		// Find a hash in one of the first two tokens
		if (false === ($pos = $this->mainParser->nextToken($tokens, Token::HASH, 0, 2))) {
			return false;
		}
		
		// First token is too many whitespace
		if (($pos > 0) && (($tokens[0]->type !== Token::WHITESPACE) || ($tokens[0]->length > 3))) {
			return false;
		}
		
		// Can have from # to ######
		if ($tokens[$pos]->length > 6) {
			return false;
		}
		
		// Header must be empty or separated with a space
		if (!isset($tokens[$pos+1]) || !\in_array($tokens[$pos+1]->type, [Token::WHITESPACE, Token::NEWLINE])) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function parse(Block $parent, array $tokens) {
		// Find the end of the line
		if (false === ($pos = $this->mainParser->nextToken($tokens, Token::NEWLINE))) {
			$my_tokens = $tokens;
			$tokens = [];
		} else {
			$my_tokens = \array_slice($tokens, 0, $pos+1);
			$tokens = \array_slice($tokens, $pos+1);
		}
		
		$parent[] = $header = new Block(Type::LEAF_ATX, $parent, $my_tokens);
		$this->parseInline($header);
		
		return $tokens;
	}
	
	public function parseInline(Block $header) {
		// Strip whitespace
		$tokens = $this->mainParser->trim($header->getTokens());
		
		// Get the level of the header and remove indicator
		$header->meta['level'] = $tokens[0]->length;
		\array_shift($tokens);
		
		// Remove header closing
		while (\count($tokens) && ($tokens[\count($tokens)-1]->type === Token::HASH)) {
			\array_pop($tokens);
		}
		
		// Remove whitespace around inline data
		$tokens = $this->mainParser->trim($tokens);
		
		// Combine string
		foreach ($tokens AS $token) {
			$header->inline .= $token->raw;
		}
	}
}
