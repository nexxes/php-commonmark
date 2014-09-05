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

use nexxes\stmd\token\Token;

/**
 * Description of ParagraphParser
 *
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
class ParagraphParser implements ParserInterface {
	/**
	 * @var Parser
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
	public function canParse(array &$tokens) {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function parse(array &$tokens) {
		$my_tokens = [];
		
		while (count($tokens)) {
			// Blankline terminates paragraph
			if ($tokens[0]->type === Token::BLANKLINE) {
				\array_shift($tokens);
				break;
			}
			
			// Read tokens
			$my_tokens[] = \array_shift($tokens);
			
			// Last token was newline, so check if someone wants to interrupt the paragraph
			if (($my_tokens[\count($my_tokens)-1]->type === Token::NEWLINE) && $this->mainParser->canInterrupt($tokens)) {
				break;
			}
		}
		
		// Remove trailing linebreak
		if ($my_tokens[\count($my_tokens)-1]->type === Token::NEWLINE) {
			\array_pop($my_tokens);
		}
		
		return new struct\Paragraph($this->mainParser->parseInline($my_tokens));
	}

}
