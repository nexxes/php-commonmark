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

namespace nexxes\cm;

use \nexxes\cm\structure\Document;
use \nexxes\tokenizer\Tokenizer;

/**
 * Base for tests that use examples from the specification
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
abstract class SpecificationTest extends \PHPUnit_Framework_TestCase {
	private $exampleReader;
	
	public function setUp() {
		$this->exampleReader = new \nexxes\cm\TestReader(__DIR__ . '/../spec/spec.txt');
	}
	
	public function runExample($exampleNo) {
		//echo "Running Example #$exampleNo:\n";
		
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
}
