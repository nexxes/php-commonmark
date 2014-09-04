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
class Tokenizer {
	/**
	 * @param string $string
	 * @return array<Token>
	 */
	public function run($string) {
		$globalpos = 0;
		$length = \strlen($string);
		$line = 0;
		$pos = 0;
		
		$tokens = [];
		
		while ($globalpos < $length) {
			$raw = '';
			
			// Read return newline
			if (($string[$globalpos] === "\r") && isset($string[$globalpos+1]) && ($string[$globalpos+1] === "\n")) {
				$tokens[] = new NewlineToken($line, $pos, "\r\n");
				$line++;
				$pos = 0;
				$globalpos += 2;
			}
			
			// Read newline
			elseif (($string[$globalpos] === "\r") || ($string[$globalpos] === "\n")) {
				$tokens[] = new NewlineToken($line, $pos, $string[$globalpos]);
				$line++;
				$pos = 0;
				$globalpos++;
			}
			
			elseif ($string[$globalpos] === '\\') {
				$tokens[] = new LiteralToken($line, $pos, '\\' . $string[$globalpos+1]);
				$pos += 2;
				$globalpos += 2;
			}
			
			// Read chars
			elseif (\in_array($string[$globalpos], ['_', '-', '#', '*', '=', ])) {
				$char = $string[$globalpos];
				
				while (isset($string[$globalpos]) && ($string[$globalpos] === $char)) {
					$raw .= $char;
					$globalpos++;
				}
				
				if ($char === '_') {
					$tokens[] = new UnderscoreToken($line, $pos, $raw);
				} elseif ($char === '-') {
					$tokens[] = new DashToken($line, $pos, $raw);
				} elseif ($char === '#') {
					$tokens[] = new HashToken($line, $pos, $raw);
				} elseif ($char === '*') {
					$tokens[] = new StarToken($line, $pos, $raw);
				} elseif ($char === '=') {
					$tokens[] = new EqualsToken($line, $pos, $raw);
				}
				
				$pos += \strlen($raw);
			}
			
			else {
				$pos++;
				$globalpos++;
			}
		}
		
		return $tokens;
	}
}
