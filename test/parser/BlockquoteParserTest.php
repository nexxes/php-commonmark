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
class BlockquoteParserTest extends \nexxes\stmd\SpecificationTest {
	/**
	 * @test
	 * @covers \nexxes\stmd\parser\BlockquoteParser
	 */
	public function testBlockquoteParser() {
		for ($test=128; $test <= 151; ++$test) {
			if ($test == 132) { continue; } // Blockquote continuation / LAZYNESS
			if ($test == 133) { continue; } // Blockquote continuation / LAZYNESS
			if ($test == 135) { continue; } // Requires lists
			if ($test == 137) { continue; } // Requires inline code
			if ($test == 146) { continue; } // Blockquote continuation / LAZYNESS
			if ($test == 147) { continue; } // Blockquote continuation / LAZYNESS
			if ($test == 148) { continue; } // Blockquote continuation / LAZYNESS
			if ($test == 149) { continue; } // Blockquote continuation / LAZYNESS
			if ($test == 150) { continue; } // Blockquote continuation / LAZYNESS
			
			$this->runExample($test);
		}
	}
}
