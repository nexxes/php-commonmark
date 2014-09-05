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
 * @coversDefaultClass \nexxes\stmd\token\Tokenizer
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
	 * Little helper to call a private method in the tokenizer class and execute it with one parameter
	 * 
	 * @param string $method Name of the private method to call
	 * @param string $text The text to supply as first (and only) argument
	 * @param bool $reference Make parameter a reference
	 * @param bool $returnTokens Return token list instead of method return value
	 */
	private function callPrivate($method, $text, $reference = false) {
		$tokenizer = new Tokenizer("");
		
		$reflectionClass = new \ReflectionClass(Tokenizer::class);
		$exec = $reflectionClass->getMethod($method);
		$exec->setAccessible(true);
		
		if ($reference) {
			return $exec->invokeArgs($tokenizer, [&$text]);
		} else {
			return $exec->invoke($tokenizer, $text);
		}
	}
	
	/**
	 * Tests token types that consist of a single char that may be repeated multiple times
	 * @test 
	 */
	public function testRepeatedSingleCharTokens() {
		$tests = [
			[ '```', Token::BACKTICK ],
			[ '=========', Token::EQUALS ],
			[ '###', Token::HASH ],
			[ '-----', Token::MINUS ],
			[ '*****', Token::STAR ],
			[ '~~', Token::TILDE ],
			[ '_', Token::UNDERSCORE ],
		];
		
		foreach ($tests AS list($testString, $tokenType)) {
			$tokens = $this->tokenize($testString);
			
			$this->assertCount(1, $tokens);
			$this->assertEquals($tokenType, $tokens[0]->type);
			$this->assertEquals(\strlen($testString), $tokens[0]->length);
			$this->assertEquals($testString, $tokens[0]->raw);
		}
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
	
	/**
	 * Test tokens that match only a single character
	 * @test
	 */
	public function testSingleCharTokens() {
		$text = ':([<\'"' . "\r\n\n\r" . '"\'>])';
		$tokens = $this->tokenize($text);
		
		$this->assertCount(\strlen($text)-1, $tokens);
		
		$this->assertEquals(Token::COLON, $tokens[0]->type);
		$this->assertEquals(Token::PARENTHESIS_LEFT, $tokens[1]->type);
		$this->assertEquals(Token::SQUARE_BRACKET_LEFT, $tokens[2]->type);
		$this->assertEquals(Token::ANGLE_BRACKET_LEFT, $tokens[3]->type);
		$this->assertEquals(Token::SINGLE_QUOTE, $tokens[4]->type);
		$this->assertEquals(Token::DOUBLE_QUOTE, $tokens[5]->type);
		$this->assertEquals(Token::NEWLINE, $tokens[6]->type);
		$this->assertEquals(Token::NEWLINE, $tokens[7]->type);
		$this->assertEquals(Token::NEWLINE, $tokens[8]->type);
		$this->assertEquals(Token::DOUBLE_QUOTE, $tokens[9]->type);
		$this->assertEquals(Token::SINGLE_QUOTE, $tokens[10]->type);
		$this->assertEquals(Token::ANGLE_BRACKET_RIGHT, $tokens[11]->type);
		$this->assertEquals(Token::SQUARE_BRACKET_RIGHT, $tokens[12]->type);
		$this->assertEquals(Token::PARENTHESIS_RIGHT, $tokens[13]->type);
	}
	
	/**
	 * @test
	 * @covers ::countLinebreaks
	 */
	public function testCountLinebreaks() {
		$text1 = "Ein Test";
		$this->assertEquals(0, $this->callPrivate('countLinebreaks', $text1, true));
		
		$text2 = "Ein Test\nZwei Test";
		$this->assertEquals(1, $this->callPrivate('countLinebreaks', $text2, true));
		
		$text3 = "Ein Test\nZwei Test\r\nFoobar\rBaz\nTest";
		$this->assertEquals(4, $this->callPrivate('countLinebreaks', $text3, true));
		
		$text4 = "Ein Test\n";
		$this->assertEquals(1, $this->callPrivate('countLinebreaks', $text4, true));
	}
	
	/**
	 * @test
	 * @covers ::lastLineLength
	 */
	public function testLastLineLength() {
		$text1 = "Ein Test";
		$this->assertEquals(8, $this->callPrivate('lastLineLength', $text1, true));
		
		$text2 = "Ein Test\nZwei Test";
		$this->assertEquals(9, $this->callPrivate('lastLineLength', $text2, true));
		
		$text3 = "Ein Test\nZwei Test\r\nFoobar\rBaz\nTest";
		$this->assertEquals(4, $this->callPrivate('lastLineLength', $text3, true));
		
		$text4 = "Ein Test\n";
		$this->assertEquals(0, $this->callPrivate('lastLineLength', $text4, true));
	}
}
