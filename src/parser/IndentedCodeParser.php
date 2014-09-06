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
 * @link http://jgm.github.io/stmd/spec.html#indented-code-blocks
 */
class IndentedCodeParser implements ParserInterface {
	const TYPE = Type::LEAF_INDENTED_CODE;
	
	/**
	 * @var \nexxes\stmd\Parser
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
	public function canInterrupt(array $tokens) {
		return false;
	}
	
	/**
	 * {@inheritdocs}
	 */
	public function canParse(array $tokens) {
		$pos = 0;
		while (false !== ($skip = $this->mainParser->isBlankLine($tokens, $pos))) {
			$pos += $skip;
		}
		
		return (isset($tokens[$pos]) && ($tokens[$pos]->type === Token::WHITESPACE) && ($tokens[$pos]->length >= 4));
	}

	/**
	 * {@inheritdocs}
	 */
	public function parse(Block $parent, array $tokens) {
		$my_tokens = [];
		$pos = -1;
		
		while ($pos+1 < \count($tokens)) {
			// Read blank lines
			if (false !== ($skip = $this->mainParser->isBlankLine($tokens, $pos+1))) {
				$pos += $skip;
				continue;
			}
			
			// Line is starting with wrong char or to short whitespace
			if (($tokens[$pos+1]->type !== Token::WHITESPACE) || ($tokens[$pos+1]->length < 4)) {
				break;
			}
			
			if (false === ($pos = $this->mainParser->nextToken($tokens, Token::NEWLINE, $pos+1))) {
				$pos = \count($tokens);
			}
		}
		
		$my_tokens = \array_slice($tokens, 0, $pos+1);
		$tokens = \array_slice($tokens, $pos+1);
		
		$parent[] = $code = new Block(Type::LEAF_INDENTED_CODE, $parent, $my_tokens);
		$this->parseInline($code);
		return $tokens;
	}
	
	public function parseInline(Block $code) {
		$tokens = $code->getTokens();
		
		// Find leading blank lines
		$start = 0;
		while (false !== ($skip = $this->mainParser->isBlankLine($tokens, $start))) { $start += $skip; }
		
		// Find trailing blank lines
		$end = \count($tokens)-1;
		while (($start < $end) && ($tokens[$end]->type === Token::NEWLINE)) {
			$fix = ($tokens[$end-1]->type === Token::WHITESPACE ? 1 : 0);
			
			if (isset($tokens[$end-(1+$fix)]) && ($tokens[$end-(1+$fix)]->type === Token::NEWLINE)) {
				$end -= (1+$fix);
			} else {
				break;
			}
		}
		
		$tokens = \array_slice($tokens, $start, ($end+1) - $start);
				
		if (\count($tokens) && ($tokens[\count($tokens)-1]->type === Token::NEWLINE)) {
			\array_pop($tokens);
		}
		
		$first = true;
		foreach ($tokens AS $token) {
			// Strip 4 spaces from beginning of the line
			if ($first && ($token->type === Token::WHITESPACE)) {
				$first = false;
				$code->inline .= \substr($token->raw, 4);
			}
			
			else {
				// Next is beginning of a line
				$first = ($token->type === Token::NEWLINE);
				$code->inline .= $token->raw;
			}
		}
	}
}
