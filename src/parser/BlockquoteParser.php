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
 * @link http://jgm.github.io/stmd/spec.html#block-quotes
 */
class BlockquoteParser implements ParserInterface {
	const TYPE = Type::CONTAINER_BLOCKQUOTE;
	
	/**
	 * @var \nexxes\cm\Parser
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
	public function canInterrupt(Block $context, array $tokens) {
		return $this->canParse($context, $tokens);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function canParse(Block $context, array $tokens) {
		// Try to find a starting delimiter
		if (false === ($quote = $this->mainParser->nextToken($tokens, Token::ANGLE_BRACKET_RIGHT, 0, 2))) {
			return false;
		}
		
		// First token is a quote
		if ($quote === 0) {
			return true;
		}
		
		// Second token is the quote, first must be whitespace with at max 3 spaces
		return (($tokens[0]->type === Token::WHITESPACE) && ($tokens[0]->length <= 3));
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function parse(Block $parent, array $tokens) {
		$my_tokens = [];
		
		while (\count($tokens)) {
			// No line quote for the next line
			if (false === $this->mainParser->nextToken($tokens, Token::ANGLE_BRACKET_RIGHT, 0, 2)) {
				break;
			}
			
			// Blank line always terminates blockquote
			if (false !== ($shift = $this->mainParser->isBlankLine($tokens))) {
				for ($i=0;$i<$shift; ++$i) { \array_shift($tokens); }
				break;
			}
			
			// Shift space of
			if ($tokens[0]->type === Token::WHITESPACE) {
				if ($tokens[0]->length <= 3) {
					\array_shift($tokens);
				} else {
					break;
				}
			}
			
			$marker = \array_shift($tokens);
			
			// Found multiple start markers, remove the first so the remaining can be parsed again
			if ($marker->length > 1) {
				$marker = clone $marker;
				$marker->pos++;
				$marker->length--;
				$marker->raw = \subtr($marker->raw, 1);
				$my_tokens[] = $marker;
			}
			
			// White space after the marker, remove a space
			elseif (isset($tokens[0]) && ($tokens[0]->type === Token::WHITESPACE)) {
				$whitespace = \array_shift($tokens);
				
				// Keep remaining spaces
				if ($whitespace->length > 1) {
					$whitespace = clone $whitespace;
					$whitespace->pos++;
					$whitespace->length--;
					$whitespace->raw = \strstr($whitespace->raw, 1);
					$my_tokens[] = $whitespace;
				}
			}
			
			// Copy a line
			while (null !== ($token = \array_shift($tokens))) {
				$my_tokens[] = $token;
				
				if ($token->type === Token::NEWLINE) {
					break;
				}
			}
		}
		
		// Eat empty lines
		$my_tokens = $this->mainParser->killBlankLines($my_tokens);
		
		$parent[] = $blockquote = new Block(Type::CONTAINER_BLOCKQUOTE, $parent, $my_tokens);
		while (\count($my_tokens)) {
			$my_tokens = $this->mainParser->parseBlock($blockquote, $my_tokens);
		}
		
		return $tokens;
	}
}
