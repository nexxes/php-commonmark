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
use nexxes\stmd\structure\Document;
use nexxes\stmd\structure\Type;

/**
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 * @link http://jgm.github.io/stmd/spec.html#indented-code-blocks
 */
class FencedCodeParser implements ParserInterface {
	const TYPE = Type::LEAF_FENCED_CODE;
	
	/**
	 * @var \nexxes\stmd\Parser
	 */
	private $mainParser;
	
	/**
	 * The fence token found in the last run
	 * @var \nexxes\stmd\token\Token
	 */
	private $fence;
	
	/**
	 * Number of tokens to include
	 * @var int
	 */
	private $tokens;
	
	
	
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
		return $this->canParse($context, $tokens);
	}
	
	/**
	 * {@inheritdocs}
	 */
	public function canParse(Block $context, array $tokens) {
		echo "Calling canParse, tokens: " . \count($tokens) . "\n";
		$pos = 0;
		
		// Check indentation
		if ($this->mainParser->isIndentation($tokens, $pos)) {
			$pos++;
		}
		
		// Code delimiter missing
		if (!isset($tokens[$pos]) || !\in_array($tokens[$pos]->type, [ Token::BACKTICK, Token::TILDE, ])) {
			return false;
		}
		
		// The fencing object found
		$this->fence = $tokens[$pos];
		if ($this->fence->length < 3) {
			return false;
		}
		
		// Check no spaces in delimiter
		for ($i=$pos+1; $i<\count($tokens); ++$i) {
			if ($tokens[$i]->type === Token::NEWLINE) {
				$pos = $i;
				break;
			}
			
			if ($tokens[$i]->type === $this->fence->type) {
				return false;
			}
		}
		
		// Find a closing line
		while (false !== ($endl = $this->mainParser->nextToken($tokens, Token::NEWLINE, $pos+1))) {
			echo "Checking newline for closing delimiter (pos: $endl) ...\n";
			
			if (false !== ($add = $this->isClosing($tokens, $endl, $this->fence))) {
				$this->tokens = $endl + $add;
				
				echo "READING tokens: " . $this->tokens . "\n";
				return true;
			} else {
				$pos = $endl;
			}
		}
		
		// We can parse up to the end of the block
		$this->tokens = \count($tokens);
		echo "READING tokens: " . $this->tokens . "\n";
		return true;
	}

	/**
	 * {@inheritdocs}
	 */
	public function parse(Block $parent, array $tokens) {
		$my_tokens = \array_slice($tokens, 0, $this->tokens);
		
		$parent[] = $code = new Block(Type::LEAF_FENCED_CODE, $parent, $my_tokens);
		$this->parseInline($code);
		
		return \array_slice($tokens, $this->tokens);
	}
	
	public function parseInline(Block $code) {
		$tokens = $code->getTokens();
		
		// Space chars to strip from each line
		$indent = ($tokens[0]->type === Token::WHITESPACE ? $tokens[0]->length : 0);
		
		// TODO: read info string
		$infostr = ($indent > 0 ? 2 : 1); // Start with possible whitespace after fence
		if (isset($tokens[$infostr]) && ($tokens[$infostr]->type === Token::WHITESPACE)) { $infostr++; } // Move if whitespace after fence exists
		if (isset($tokens[$infostr]) && ($tokens[$infostr]->type !== Token::NEWLINE)) {
			$code->meta['infostring'] = $tokens[$infostr]->raw;
		}
		
		// Skip first line
		$pos = $this->mainParser->nextToken($tokens, Token::NEWLINE);
		$nl = true; // Indicates whether the previous line was newline
		
		// Walk all tokens
		for($i=$pos+1; $i<\count($tokens); ++$i) {
			// Found closing marker
			if ((($i === $pos+1) || ($tokens[$i]->type === Token::NEWLINE)) && $this->isClosing($tokens, $i, $this->fence)) { break; }
			
			// Strip indentation
			if ($nl && ($tokens[$i]->type === Token::WHITESPACE)) {
				$code->inline .= \str_repeat(' ', \max(0, $tokens[$i]->length - $indent));
			}
			
			else {
				$code->inline .= $tokens[$i]->raw;
			}
			
			$nl = ($tokens[$i]->type === Token::NEWLINE);
		}
	}
	
	private function isFinalToken(Block $parent, Token $token) {
		$tokens = $parent->getRoot()->getTokens();
		return ($tokens[\count($tokens)-1] === $token);
	}
	
	
	/**
	 * 
	 * @param array<\nexxes\stmd\token\Token> $tokens
	 * @param type $pos
	 * @param \nexxes\stmd\token\Token $fence
	 * @return boolean
	 */
	private function isClosing(array &$tokens, $pos, Token $fence) {
		$count = 0;
		
		// Allow to start at a newline
		if ($tokens[$pos]->type === Token::NEWLINE) {
			echo "Found a newline\n";
			$count++;
		}
		
		// Indentation of end marker
		if ($this->mainParser->isIndentation($tokens, $pos+$count)) {
			echo "Found indentation\n";
			$count++;
		}

		// We need the end fence here
		if (!isset($tokens[$pos+$count]) || ($tokens[$pos+$count]->type !== $fence->type)) {
			return false;
		}
		
		echo "Found delimiter\n";
		// Verify size matches
		if ($tokens[$pos+$count]->length >= $fence->length) {
			echo " ... has correct size\n";
			$count++;
		} else {
			return false;
		}

		// Space before the line ending
		if (isset($tokens[$pos+$count]) && ($tokens[$pos+$count]->type === Token::WHITESPACE)) {
			echo "found whitespace\n";
			$count++;
		}

		// Token list is empty
		if (!isset($tokens[$pos+$count])) {
			echo "Empty tokenlist\n";
			return $count;
		}
		
		// Found final newline
		if ($tokens[$pos+$count]->type === Token::NEWLINE) {
			echo "Found another newline\n";
			return $count+1;
		}
		
		return false;
	}
}
