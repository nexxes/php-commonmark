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

use \nexxes\cm\token\Token;
use \nexxes\cm\structure\Block;
use \nexxes\cm\structure\Type;

/**
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 * @link http://jgm.github.io/stmd/spec.html#html-blocks
 */
class HTMLBlockParser implements ParserInterface {
	const TYPE = Type::LEAF_HTML;
	
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
		$pos = ($this->mainParser->isIndentation($tokens, 0) ? 1 : 0);
		
		if (isset($tokens[$pos]) && ($tokens[$pos]->type !== Token::ANGLE_BRACKET_LEFT)) {
			return false;
		}
		
		if ($this->isComment($tokens, $pos)
			|| $this->isCData($tokens, $pos)
			|| $this->isOpeningTag($tokens, $pos)
			|| $this->isClosingTag($tokens, $pos)
			|| $this->isProcessing($tokens, $pos)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function parse(Block $parent, array $tokens) {
		$pos = $start = 0;
		
		if ($this->mainParser->isIndentation($tokens, $pos)) { ++$pos; ++$start; }
		
		while (false !== ($endl = $this->mainParser->nextToken($tokens, Token::NEWLINE, $pos+1))) {
			$pos = $endl;
			
			// check for newline
			if (!isset($tokens[$pos+1])) { continue; }
			
			// No newline or whitespace+newline
			if (($tokens[$pos+1]->type !== Token::NEWLINE)
				&& (!isset($tokens[$pos+2])
				|| ($tokens[$pos+1]->type !== Token::WHITESPACE)
				|| ($tokens[$pos+2]->type !== Token::NEWLINE))) {
				continue;
			}
			
			$my_tokens = \array_slice($tokens, 0, $pos+1);
			$tokens = \array_slice($tokens, $pos+1);
			
			// Remove blank and newline
			if ($tokens[0]->type === Token::WHITESPACE) { \array_shift($tokens); }
			\array_shift($tokens);
			
			break;
		}
		
		if (!isset($my_tokens)) {
			$my_tokens = $tokens;
			$tokens = [];
		}
		
		$parent[] = $html = new Block(Type::LEAF_HTML, $parent, $my_tokens);
		$this->parseInline($html);
		return $tokens;
	}
	
	private function parseInline(Block $html) {
		$tokens = $html->getTokens();
		if ($tokens[\count($tokens)-1]->type === Token::NEWLINE) { \array_pop($tokens); }
		
		foreach ($tokens AS $token) {
			$html->inline .= $token->raw;
		}
	}
	
	private function isTagName(Token $token) {
		$tags = [
			'p', 'br', 'h3', 'ol', 'col', 'body', 'aside', 'thead', 'button', 'object', 'article', 'colgroup', 'blockquote',
			     'dd', 'h4', 'td', 'div', 'form', 'embed', 'video', 'canvas', 'output', 'caption', 'fieldset', 'figcaption',
			     'dl', 'h5', 'th', 'map',         'style',          'figure', 'script', 'section', 'progress',
			     'dt', 'h6', 'tr', 'pre',         'table',          'footer',                      'textarea',
			     'h1', 'hr', 'ul',                'tbody',          'header',
			     'h2', 'li',                      'tfoot',          'hgroup',
		];
		
		if ($token->type !== Token::TEXT) {
			return false;
		}
		
		return \in_array(\strtolower($token->raw), $tags);
	}
	
	/**
	 * Try to match opening tag: <tagname
	 * @param array<\nexxes\cm\token\Token> $tokens
	 * @param int $pos
	 * @return boolean
	 */
	private function isOpeningTag(array &$tokens, $pos) {
		return (isset($tokens[$pos+1])
			&& ($tokens[$pos]->type === Token::ANGLE_BRACKET_LEFT)
			&& $this->isTagName($tokens[$pos+1])
		);
	}
	
	/**
	 * Try to match clsoing tag: </tagname>
	 * @param array<\nexxes\cm\token\Token> $tokens
	 * @param int $pos
	 * @return boolean
	 */
	private function isClosingTag(array &$tokens, $pos) {
		return (isset($tokens[$pos+3])
			&& ($tokens[$pos]->type === Token::ANGLE_BRACKET_LEFT)
			&& ($tokens[$pos+1]->type === Token::SLASH)
			&& $this->isTagName($tokens[$pos+2])
			&& ($tokens[$pos+3]->type === Token::ANGLE_BRACKET_RIGHT)
		);
	}
	
	/**
	 * Try to match comment from current position: <!-- ... -->
	 * @param array<\nexxes\cm\token\Token> $tokens
	 * @param int $pos
	 * @return boolean
	 */
	private function isComment(array &$tokens, $pos) {
		// Opening tag
		if (!isset($tokens[$pos+2])
			|| ($tokens[$pos]->type !== Token::ANGLE_BRACKET_LEFT)
			|| ($tokens[$pos+1]->type !== Token::BANG)
			|| ($tokens[$pos+2]->type !== Token::MINUS)
			|| ($tokens[$pos+2]->length !== 2)) { return false; }
		
		// Closing tag
		if ((false === ($found = $this->mainParser->nextToken($tokens, Token::MINUS, $pos+3)))
			|| ($tokens[$found]->length !== 2)
			|| (!isset($tokens[$found+1]))
			|| ($tokens[$found+1]->type !== Token::ANGLE_BRACKET_RIGHT)) { return false; }
		
		return true;
	}
	
	/**
	 * CData: <![CDATA[  ]]>
	 * @param array<\nexxes\cm\token\Token> $tokens
	 * @param int $pos
	 * @return boolean
	 */
	private function isCData(array &$tokens, $pos) {
		// Opening tag
		if (!isset($tokens[$pos+4])
			|| ($tokens[$pos]->type !== Token::ANGLE_BRACKET_LEFT)
			|| ($tokens[$pos+1]->type !== Token::BANG)
			|| ($tokens[$pos+2]->type !== Token::SQUARE_BRACKET_LEFT)
			|| ($tokens[$pos+3]->type !== Token::TEXT)
			|| ($tokens[$pos+3]->raw !== 'CDATA')
			|| ($tokens[$pos+4]->type !== Token::SQUARE_BRACKET_LEFT)) {
			return false;
		}
		
		// Closing tag
		while (false !== ($found = $this->mainParser->nextToken($tokens, Token::SQUARE_BRACKET_RIGHT, $pos+5))) {
			if (isset($tokens[$found+2])
				&& ($tokens[$found+1]->type === Token::SQUARE_BRACKET_RIGHT)
				&& ($tokens[$found+2]->type === Token::ANGLE_BRACKET_RIGHT)) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 * Processing instructions: <? ?>
	 * @param array<\nexxes\cm\token\Token> $tokens
	 * @param int $pos
	 * @return boolean
	 * @link http://jgm.github.io/stmd/spec.html#processing-instruction
	 */
	private function isProcessing(array &$tokens, $pos) {
		// Opening tag
		if (!isset($tokens[$pos+1])
			|| ($tokens[$pos]->type !== Token::ANGLE_BRACKET_LEFT)
			|| ($tokens[$pos+1]->type !== Token::QUESTION)) {
			return false;
		}
		
		// Closing tag
		while (false !== ($found = $this->mainParser->nextToken($tokens, Token::QUESTION, $pos+2))) {
			if (isset($tokens[$found+1])
				&& ($tokens[$found+1]->type === Token::ANGLE_BRACKET_RIGHT)) {
				return true;
			}
		}

		return false;
	}
}
