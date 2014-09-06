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

/**
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
class HorizontalRuleParserTest extends \nexxes\stmd\SpecificationTest {
	/**
	 * @test
	 * @covers \nexxes\stmd\parser\HorizontalRuleParser
	 */
	public function testHorizontalRuleParser() {
		for ($test=4; $test <= 22; ++$test) {
			if ($test == 9) { continue; } // Requires code block
			if ($test == 17) { continue; } // Requires emphasis
			if ($test == 18) { continue; } // Requires lists
			if ($test == 20) { continue; } // Requires setext header
			if ($test == 21) { continue; } // Requires lists
			if ($test == 22) { continue; } // Requires lists
			
			$this->runExample($test);
		}
	}
}