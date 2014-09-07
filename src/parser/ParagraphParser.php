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
 * @link http://jgm.github.io/stmd/spec.html#paragraphs
 */
class ParagraphParser implements ParserInterface {
	const TYPE = Type::LEAF_PARAGRAPH;
	
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
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function canParse(Block $context, array $tokens) {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function parse(Block $parent, array $tokens) {
		$my_tokens = [];
		
		while (null !== ($token = \array_shift($tokens))) {
			if ($token->type === Token::NEWLINE) {
				// Last token was newline, so check if someone wants to interrupt the paragraph
				if ($this->mainParser->canInterrupt($parent, $tokens)) {
					break;
				}
				
				// Check if a blank line follows
				if ($this->mainParser->isBlankLine($tokens)) {
					break;
				}
				
				// Remove leading whitespace in next line
				while (isset($tokens[0]) && ($tokens[0]->type === Token::WHITESPACE)) {
					\array_shift($tokens);
				}
			}
			
			// Read tokens
			$my_tokens[] = $token;
		}
		
		// Remove leading and trailing whitespace
		$my_tokens = $this->mainParser->trim($my_tokens);
		
		// Store struct
		$parent[] = new Block(Type::LEAF_PARAGRAPH, $parent, $my_tokens);
		
		return $tokens;
	}
}
