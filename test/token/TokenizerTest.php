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

namespace nexxes\stmd\token;

/**
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
class TokenizerTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @param string $text
	 * @return array<Token>
	 */
	private function tokenize($text) {
		$tokenizer = new Tokenizer($text);
		return $tokenizer->run();
	}
	
	/**
	 * @test 
	 */
	public function testDash() {
		$text = '----';
		$tokens = $this->tokenize($text);
		
		$this->assertCount(1, $tokens);
		$this->assertInstanceOf(DashToken::class, $tokens[0]);
		$this->assertEquals(\strlen($text), $tokens[0]->length);
		$this->assertEquals($text, $tokens[0]->raw);
	}
	
	/**
	 * @test 
	 */
	public function testHash() {
		$text = '###';
		$tokens = $this->tokenize($text);
		
		$this->assertCount(1, $tokens);
		$this->assertInstanceOf(HashToken::class, $tokens[0]);
		$this->assertEquals(\strlen($text), $tokens[0]->length);
		$this->assertEquals($text, $tokens[0]->raw);
	}
	
	/**
	 * @test 
	 */
	public function testStar() {
		$text = '*****';
		$tokens = $this->tokenize($text);
		
		$this->assertCount(1, $tokens);
		$this->assertInstanceOf(StarToken::class, $tokens[0]);
		$this->assertEquals(\strlen($text), $tokens[0]->length);
		$this->assertEquals($text, $tokens[0]->raw);
	}
	
	/**
	 * @test 
	 */
	public function testUnderscore() {
		$text = '__';
		$tokens = $this->tokenize($text);
		
		$this->assertCount(1, $tokens);
		$this->assertInstanceOf(UnderscoreToken::class, $tokens[0]);
		$this->assertEquals(\strlen($text), $tokens[0]->length);
		$this->assertEquals($text, $tokens[0]->raw);
	}
	
	/**
	 * @test 
	 */
	public function testEquals() {
		$text = '=========';
		$tokens = $this->tokenize($text);
		
		$this->assertCount(1, $tokens);
		$this->assertInstanceOf(EqualsToken::class, $tokens[0]);
		$this->assertEquals(\strlen($text), $tokens[0]->length);
		$this->assertEquals($text, $tokens[0]->raw);
	}
	
	/**
	 * @test 
	 */
	public function testNewline() {
		$text = "\n\n\r\n\r";
		$tokens = $this->tokenize($text);
		
		$this->assertCount(4, $tokens);
		$this->assertInstanceOf(NewlineToken::class, $tokens[0]);
		$this->assertInstanceOf(NewlineToken::class, $tokens[1]);
		$this->assertInstanceOf(NewlineToken::class, $tokens[2]);
		$this->assertInstanceOf(NewlineToken::class, $tokens[3]);
	}
}
