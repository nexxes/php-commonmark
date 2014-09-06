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
	 * The priority of block parsers, later parsers have lower priority
	 * @var array<string>
	 */
	private $block_prio = [
		Structs::CONTAINER_BLOCKQUOTE,
		Structs::LEAF_ATX,
		Structs::LEAF_SETEXT,
		Structs::LEAF_HR,
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
		//$preprocessed = $this->preprocess($string);
		//$stream = \explode("\n", $preprocessed);
		
		//return $this->parse($stream);
		
		$tokenizer = new Tokenizer($string);
		$tokens = $tokenizer->run();
		
		//print_r($tokens);
		
		return $this->parse($tokens);
	}
	
	public function parse(array &$tokens) {
		$struct = [];
		
		//print_r($tokens);
		
		while (\count($tokens)) {
			// Remove one to three spaces indent
			if (($tokens[0]->type === Token::WHITESPACE) && ($tokens[0]->length <= 3)) {
				\array_shift($tokens);
				continue;
			}
			
			if ($tokens[0]->type === Token::BLANKLINE) {
				\array_shift($tokens);
				continue;
			}
			
			if ($this->parsers[self::HORIZONTAL_RULE]->canParse($tokens)) {
				$struct[] = $this->parsers[self::HORIZONTAL_RULE]->parse($tokens);
			}
			
			elseif ($this->parsers[self::SETEXT_HEADER]->canParse($tokens)) {
				$struct[] = $this->parsers[self::SETEXT_HEADER]->parse($tokens);
			}
			
			elseif ($this->parsers[self::PARAGRAPH]->canParse($tokens)) {
				$struct[] = $this->parsers[self::PARAGRAPH]->parse($tokens);
			}
			
			// Avoid enless loops, eat unparsable stuff
			else {
				$struct[] = new struct\Literal(\array_shift($tokens));
			}
		}
		
		return $struct;
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
	 * Try to eat up to three spaces indentation.
	 * 
	 * @param array<\nexxes\stmd\token\Token> $tokens
	 * @return boolean
	 */
	public function removeIndentation(array &$tokens) {
		// Nothing to eat
		if (!\count($tokens)) {
			return false;
		}
		
		// Can only eat whitespace
		if ($tokens[0]->type !== Token::WHITESPACE) {
			return false;
		}
		
		// Eat only 3 spaces
		if ($tokens[0]->length > 3) {
			return false;
		}
		
		\array_shift($tokens);
		return true;
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
	 * @param array<\nexxes\stmd\token\Token> $tokens
	 * @return boolean
	 */
	public function canInterrupt(array &$tokens) {
		if (!\count($tokens)) {
			return false;
		}
		
		// Try to parse blocks according to priority array
		foreach ($this->block_prio AS $blockType) {
			if ($this->parsers[$blockType]->canInterrupt($tokens)) {
				return $blockType;
			}
		}
		
		return false;
	}
	
	
	/**
	 * Returns the block type that can be parsed from this data.
	 * If no type is returned, parse as Paragraph.
	 * 
	 * @return string
	 */
	public function canParseBlock(array &$tokens) {
		if (!\count($tokens)) {
			return false;
		}
		
		// Try to parse blocks according to priority array
		foreach ($this->block_prio AS $blockType) {
			if ($this->parsers[$blockType]->canParse($tokens)) {
				return $blockType;
			}
		}
		
		return Structs::LEAF_PARAGRAPH;
	}
	
	
	/**
	 * Execute the parser of type $type. If $type is empty, find the matching parser.
	 * Returns the remaining tokens
	 * 
	 * @param \nexxes\stmd\structure\Block $parent
	 * @param array<\nexxes\stmd\token\Token> $tokens
	 * @param string $type
	 * @return array<\nexxes\stmd\token\Token>
	 */
	public function parseBlock(Block $parent, array $tokens, $type = null) {
		if ($type === null) {
			$type = $this->canParseBlock($tokens);
		}
		
		if ($type === false) {
			return $tokens;
		}
		
		return $this->parsers[$type]->parse($parent, $tokens);
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
}
