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

use \nexxes\stmd\structure\Block;
use \nexxes\stmd\structure\Document;
use \nexxes\stmd\structure\StructureInterface;
use \nexxes\stmd\structure\Type;

/**
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
class Printer {
	/**
	 * The document to print
	 * @var \nexxes\stmd\structure\Document
	 */
	private $doc;
	
	public function __construct(Document $md) {
		$this->doc = $md;
	}
	
	public function __toString() {
		return $this->printElements($this->doc);
	}
	
	protected function doPrint(StructureInterface $elem) {
		switch ($elem->getType()) {
			case Type::CONTAINER_BLOCKQUOTE:
				return $this->printBlockquote($elem);
			case Type::LEAF_HR:
				return $this->printHorizontalRule($elem);
			case Type::LEAF_ATX:
			case Type::LEAF_SETEXT:
				return $this->printHeaders($elem);
			case Type::LEAF_INDENTED_CODE:
				return $this->printCode($elem);
			case Type::LEAF_PARAGRAPH:
				return $this->printParagraph($elem);
			
			case Type::INLINE_SOFT_BREAK:
				return $this->printInlineSoftBreak($elem);
		}
	}
	
	protected function printBlockquote(Block $elem) {
		return '<blockquote>' . PHP_EOL . $this->printElements($elem) . (\count($elem) ? PHP_EOL : '') . '</blockquote>';
	}
	
	protected function printHorizontalRule(Block $elem) {
		return '<hr />';
	}
	
	protected function printHeaders(Block $elem) {
		return '<h' . $elem->meta['level'] . '>' . $elem->inline . '</h' . $elem->meta['level'] . '>';
	}
	
	protected function printCode(Block $elem) {
		return '<pre><code>' . \htmlspecialchars($elem->inline) . PHP_EOL . '</code></pre>';
	}
	
	protected function printParagraph(Block $paragraph) {
		$r = '';
		
		foreach ($paragraph->getTokens() AS $token) {
			$r .= $token->raw;
		}
		
		return '<p>' . $r . '</p>';
	}
	
	protected function printInlineSoftBreak(Block $elem) {
		return "\n";
	}
	
	/**
	 * Simple helper function to loop over contained elements
	 * @param Block $block
	 * @return string
	 */
	protected function printElements(Block $block) {
		$r = '';
		foreach ($block AS $i => $element) {
			$r .= ($i>0 ? PHP_EOL : '') . $this->doPrint($element);
		}
		return $r;
	}
}
