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

use \nexxes\stmd\structure\Document;
use \nexxes\stmd\structure\Type;
use \nexxes\stmd\token\Tokenizer;

/**
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
class ParserTest extends \PHPUnit_Framework_TestCase {
	private $exampleReader;
	
	public function setUp() {
		$this->exampleReader = new \nexxes\stmd\TestReader(__DIR__ . '/../spec/spec.txt');
	}
	
	public function runExample($exampleNo) {
		$example = $this->exampleReader->getExample($exampleNo);
		
		$tokenizer = new Tokenizer($example['in']);
		$tokens = $tokenizer->run();
		
		$doc = new Document($tokens);
		$parser = new Parser();
		
		while (count($tokens)) {
			$tokens = $parser->parseBlock($doc, $tokens);
		}
		
		$output = (string)new Printer($doc);
		
		if ($example['out'] !== $output) {
			print_r($doc->getTokens());
		}
		$this->assertEquals($example['out'], $output, 'Example ' . $exampleNo . ' failed!');
	}
	
	/**
	 * @test
	 * @covers \nexxes\stmd\parser\ATXHeaderParser
	 */
	public function testATXHeaderParser() {
		for ($test=23; $test <= 39; ++$test) {
			if ($test == 27) { continue; } // Requires emphasis
			if ($test == 30) { continue; } // Requires code block
			
			$this->runExample($test);
		}
	}
	
	/**
	 * @test
	 * @covers \nexxes\stmd\parser\BlockquoteParser
	 */
	public function testBlockquoteParser() {
		$this->runExample(138);
		$this->runExample(141);
		$this->runExample(144);
		//$this->runExample(147);
	}
	
	/**
	 * @test
	 * @covers \nexxes\stmd\parser\HorizontalRuleParser
	 */
	public function testHorizontalRuleParser() {
		$this->runExample(4);
	}
}
