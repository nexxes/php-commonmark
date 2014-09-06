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

use \nexxes\stmd\token\Token;
use \nexxes\stmd\token\Tokenizer;
use \nexxes\stmd\structure\Block;
use \nexxes\stmd\structure\Document;
use \nexxes\stmd\structure\Type AS Structs;

/**
 * Description of Parser
 *
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
class Parser {
	const HORIZONTAL_RULE = 1;
	const SETEXT_HEADER = 2;
	const PARAGRAPH = 3;
	
	const INLINE_CODE = 'Inline_Code';
	const LINEBREAK = 'Hard_Line_Break';
	
	private $parsers = [];
	
	/**
	 * Token generated at the last run
	 * @var array<\nexxes\stmd\token\Token>
	 */
	public $tokens = [];
	
	/**
	 * The priority of block parsers, later parsers have lower priority
	 * @var array<string>
	 */
	private $block_prio = [
		Structs::CONTAINER_BLOCKQUOTE,
		Structs::LEAF_HR,
		Structs::LEAF_ATX,
		Structs::LEAF_SETEXT,
		Structs::LEAF_INDENTED_CODE,
		Structs::LEAF_FENCED_CODE,
		Structs::LEAF_HTML,
		Structs::LEAF_PARAGRAPH,
	];
	
	
	
	
	public function __construct() {
		/*
		$this->parsers[self::HORIZONTAL_RULE] = new HorizontalRuleParser($this);
		$this->parsers[self::SETEXT_HEADER] = new SetextHeaderParser($this);
		$this->parsers[self::PARAGRAPH] = new ParagraphParser($this);
		$this->parsers[self::INLINE_CODE] = new InlineCodeParser($this);
		$this->parsers[self::LINEBREAK] = new HardLineBreakParser($this);
		 */

		$this->useParser(new parser\BlockquoteParser($this));
		$this->useParser(new parser\HorizontalRuleParser($this));
		$this->useParser(new parser\ATXHeaderParser($this));
		$this->useParser(new parser\SetextHeaderParser($this));
		$this->useParser(new parser\IndentedCodeParser($this));
		$this->useParser(new parser\FencedCodeParser($this));
		$this->useParser(new parser\HTMLBlockParser($this));
		$this->useParser(new parser\ParagraphParser($this));
	}
	
	/**
	 * Use the supplied parser to parse the structure indicated in the TYPE constant of the parsers class.
	 * @param \nexxes\stmd\parser\ParserInterface $parser
	 */
	public function useParser(parser\ParserInterface $parser) {
		$this->parsers[$parser::TYPE] = $parser;
	}
	
	public function parseString($string) {
		$string = $this->preprocess($string);
		
		$tokenizer = new Tokenizer($string);
		$tokens = $this->tokens = $tokenizer->run();
		$doc = new Document($this->tokens);
		
		while (count($tokens)) {
			$tokens = $this->parseBlock($doc, $tokens);
		}
		
		return (string)new Printer($doc);
	}
	
	public function parseInline(array &$tokens, $oneLineOnly = false) {
		$struct = [];
		
		while (\count($tokens)) {
			// You may want to read only one line with inline parsing
			if ($oneLineOnly && ($tokens[0]->type === Token::NEWLINE)) {
				\array_shift($tokens);
				break;
			}
			
			if ($this->parsers[self::INLINE_CODE]->canParse($tokens)) {
				$struct[] = $this->parsers[self::INLINE_CODE]->parse($tokens);
			}
			
			elseif ($this->parsers[self::LINEBREAK]->canParse($tokens)) {
				$struct[] = $this->parsers[self::LINEBREAK]->parse($tokens);
			}
			
			else {
				$struct[] = new struct\Literal(\array_shift($tokens));
			}
		}
		
		return $struct;
	}
	
	/**
	 * Translate tabs into spaces and normalize newlines
	 * 
	 * @param string $string
	 * @return string
	 * @link http://jgm.github.io/stmd/spec.html#preprocessing
	 */
	private function preprocess($string) {
		$string1 = \str_replace("\t", '    ', $string);
		$string2 = \str_replace("\r\n", "\n", $string1);
		$string3 = \str_replace("\r", "\n", $string2);
		return $string3;
	}
	
	/**
	 * Find the next token that is of the specified type.
	 * List is searched from $offset and is aborted if $count elements have been checked unsuccessfully.
	 * 
	 * @param array<Token> $tokens
	 * @param string $type
	 * @param int $offset
	 * @param int $max
	 * @return int
	 */
	public function nextToken(array &$tokens, $type, $offset = 0, $max = null) {
		$count = \count($tokens);
		
		// Reached list ending
		if ($offset >= $count) {
			return false;
		}
		
		// Limit search range
		if ($max !== null) {
			$count = \min($count, $offset+$max);
		}
		
		// Search for the type
		for($i=$offset; $i<$count; ++$i) {
			if ($tokens[$i]->type === $type) {
				return $i;
			}
		}
		
		return false;
	}
	
	
	/**
	 * Check if someone wants to interrupt a paragraph
	 * 
	 * @param \nexxes\stmd\structure\Block $context
	 * @param array<\nexxes\stmd\token\Token> $tokens
	 * @return boolean
	 */
	public function canInterrupt(Block $context, array &$tokens) {
		if (!\count($tokens)) {
			return false;
		}
		
		// Try to parse blocks according to priority array
		foreach ($this->block_prio AS $blockType) {
			if ($this->parsers[$blockType]->canInterrupt($context, $tokens)) {
				return $blockType;
			}
		}
		
		return false;
	}
	
	
	/**
	 * Returns the block type that can be parsed from this data.
	 * If no type is returned, parse as Paragraph.
	 * 
	 * @param \nexxes\stmd\structure\Block $context
	 * @param array<\nexxes\stmd\token\Token> $tokens
	 * @return string
	 */
	public function canParseBlock(Block $context, array &$tokens) {
		if (!\count($tokens)) {
			return false;
		}
		
		// Try to parse blocks according to priority array
		foreach ($this->block_prio AS $blockType) {
			if ($this->parsers[$blockType]->canParse($context, $tokens)) {
				return $blockType;
			}
		}
		
		return Structs::LEAF_PARAGRAPH;
	}
	
	
	/**
	 * Execute the parser of type $type. If $type is empty, find the matching parser.
	 * Returns the remaining tokens
	 * 
	 * @param \nexxes\stmd\structure\Block $context
	 * @param array<\nexxes\stmd\token\Token> $tokens
	 * @param string $type
	 * @return array<\nexxes\stmd\token\Token>
	 */
	public function parseBlock(Block $context, array $tokens, $type = null) {
		if ($type === null) {
			$type = $this->canParseBlock($context, $tokens);
		}
		
		if ($type === false) {
			return $tokens;
		}
		
		return $this->parsers[$type]->parse($context, $tokens);
	}
	
	/**
	 * Remove newlines and spaces from the beginning and the end of the string
	 * @param array $tokens
	 */
	public function trim(array $tokens) {
		// Trim left
		while (\count($tokens) && \in_array($tokens[0]->type, [Token::WHITESPACE, Token::NEWLINE, Token::BLANKLINE, ])) {
			\array_shift($tokens);
		}
		
		// Trim right
		while (\count($tokens) && \in_array($tokens[\count($tokens)-1]->type, [Token::WHITESPACE, Token::NEWLINE])) {
			\array_pop($tokens);
		}
		
		return $tokens;
	}
	
	/**
	 * Returns if the current line is a blank line.
	 * Asumes that current position is at the beginning of a line.
	 * Returns the number of tokens in this line (1 or 2) or false if not a blank line
	 * 
	 * @param array<\nexxes\stmd\token\Token> $tokens
	 * @param int $pos
	 * @return int|boolean
	 */
	public function isBlankLine(array $tokens, $pos = 0) {
		// Next char is a newline
		if (!isset($tokens[$pos])) { return false; }
		if ($tokens[$pos]->type === Token::NEWLINE) { return 1; }
		
		// Next char is whitespace followed by newline
		if (!isset($tokens[$pos+1])) { return false; }
		if ($tokens[$pos]->type !== Token::WHITESPACE) { return false; }
		if ($tokens[$pos+1]->type === Token::NEWLINE) { return 2; }
		
		return false;
	}
	
	/**
	 * Remove leading and trailing blank lines
	 * @param array<\nexxes\stmd\token\Token> $tokens
	 * @return array<\nexxes\stmd\token\Token>
	 */
	public function killBlankLines(array $tokens) {
		$found = 0;
		
		// Find leading blank lines
		$start = 0;
		while (false !== ($skip = $this->isBlankLine($tokens, $start))) {
			$start += $skip;
			$found++;
		}
		
		// Find trailing blank lines
		$end = \count($tokens)-1;
		while (($start < $end) && ($tokens[$end]->type === Token::NEWLINE)) {
			$fix = ($tokens[$end-1]->type === Token::WHITESPACE ? 1 : 0);
			
			if (isset($tokens[$end-(1+$fix)]) && ($tokens[$end-(1+$fix)]->type === Token::NEWLINE)) {
				$end -= (1+$fix);
				$found++;
			} else {
				break;
			}
		}
		
		if ($found) {
			return \array_slice($tokens, $start, ($end+1) - $start);
		} else {
			return $tokens;
		}
	}
	
	/**
	 * Check if the element at the supplied position exists, is whitespace and not longer than 3 spaces.
	 * 
	 * @param array<\nexxes\stmd\token\Token> $tokens
	 * @param int $pos
	 * @return boolean
	 */
	public function isIndentation(array $tokens, $pos) {
		// Element does not exist
		if (!isset($tokens[$pos])) { return false; }
		
		// Not whitespace
		if ($tokens[$pos]->type !== Token::WHITESPACE) { return false; }
		
		// Check length
		return ($tokens[$pos]->length <= 3);
	}
}
