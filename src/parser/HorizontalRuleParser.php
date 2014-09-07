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
 * @link http://jgm.github.io/stmd/spec.html#horizontal-rules
 */
class HorizontalRuleParser implements ParserInterface {
	const TYPE = Type::LEAF_HR;
	
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
		// First char must be whitespace (with a maximum of 3 spaces) or one of the line chars)
		if ($tokens[0]->type === Token::WHITESPACE) {
			if ($tokens[0]->length > 3) {
				return false;
			} else {
				\array_shift($tokens);
			}
		}
		
		if (!isset($tokens[0]) || !\in_array($tokens[0]->type, [Token::MINUS, Token::STAR, Token::UNDERSCORE, ])) {
			return false;
		}
		
		while (null !== ($token = \array_shift($tokens))) {
			if (!isset($usedChar)) {
				$usedChar = $token->type;
				$count = 0;
			}
			
			if ($token->type === $usedChar) {
				$count += $token->length;
			}
			
			// Ignore whitespace
			elseif ($token->type === Token::WHITESPACE) {
			}
			
			elseif ($token->type === Token::NEWLINE) {
				break;
			}
			
			else {
				return false;
			}
		}
		
		return ($count >= 3);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function parse(Block $parent, array $tokens) {
		$my_tokens = [];
		
		while (null !== ($token = \array_shift($tokens))) {
			if ($token->type === Token::NEWLINE) {
				break;
			}
			
			if ($token->type !== Token::WHITESPACE) {
				$my_tokens[] = $token;
			}
		}
		
		$parent[] = new Block(Type::LEAF_HR, $parent, $my_tokens);
		return $tokens;
	}
}
