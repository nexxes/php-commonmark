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
	 * Data to tokenize
	 * @var string
	 */
	private $raw;
	
	/**
	 * String length of $raw
	 * @var int
	 */
	private $length;
	
	/**
	 * Char position in $raw
	 * @var type 
	 */
	private $pos = 0;
	
	/**
	 * Line position in the current tokenizing process
	 * @var int
	 */
	private $line = 1;
	
	/**
	 * Current char position in $line
	 * @var int
	 */
	private $column = 1;
	
	/**
	 * List of generated tokens
	 * @var array<Token>
	 */
	private $tokens = [];
	
	
	
	
	public function __construct($data) {
		// Tidy newlines
		$data1 = \str_replace("\r\n", "\n", $data);
		$data2 = \str_replace("\r", "\n", $data1);
		
		$this->raw = $data2;
		$this->length = \strlen($this->raw);
	}
	
	/**
	 * Get the list of tokens created in the last run
	 * @return array<Token>
	 */
	public function getTokens() {
		return $this->tokens;
	}
	
	/**
	 * @param array<\nexxes\stmd\token\Token> $tokens
	 */
	public function postProcess(array $tokens) {
		$count = \count($tokens);
		
		for ($i=0; $i<$count; $i++) {
			// Allow to unset elements
			if (!isset($tokens[$i])) { continue; }
			
			// Current token
			$token = $tokens[$i];
			
			// Replace tabs with 4 spaces
			if ($token->type === Token::WHITESPACE) {
				$token->raw = \str_replace("\t", '    ', $token->raw);
				$token->length = \strlen($token->raw);
				continue;
			}
			
			// Try to find blank lines
			/*if (($token->type === Token::NEWLINE) || ($token->type === Token::BLANKLINE)) {
				// If next token is also a newline, it is actually a blank line
				if (isset($tokens[$i+1])
					&& ($tokens[$i+1]->type === Token::NEWLINE)) {
					$tokens[$i+1] = new Token(Token::BLANKLINE, $tokens[$i+1]->line, 1, "\n");
				}
				
				// Check if next token is whitespace followed by newline => blankline
				elseif (isset($tokens[$i+1])
					&& isset($tokens[$i+2])
					&& ($tokens[$i+1]->type === Token::WHITESPACE)
					&& ($tokens[$i+2]->type === Token::NEWLINE)) {
					$tokens[$i+2] = new Token(Token::BLANKLINE, $tokens[$i+1]->line, 1, "\n");
					unset($tokens[$i+1]);
				}
				
				continue;
			}*/
		}
		
		return \array_values($tokens);
	}
	
	public function run() {
		$tokenizers = [
			//'tokenizeProcessingInstruction',
			//'tokenizeHTMLComment',
			//'tokenizeCData',
			//  'tokenizeDeclaration',
			//'tokenizeClosingTag',
			//'tokenizeOpenTag',
			
			'tokenizeNewline',
			
			'tokenizeBackslashEscapes',
			
			// Tokenizer methods that read a single char
			'tokenizeBackslash',
			'tokenizeColon',
			'tokenizeSlash',
			'tokenizeBang',
			'tokenizeQuestionMark',
			'tokenizeSingleQuote',
			'tokenizeDoubleQuote',
			'tokenizeLeftSquareBracket',
			'tokenizeRightSquareBracket',
			'tokenizeLeftParenthesis',
			'tokenizeRightParenthesis',
			'tokenizeLeftAngularBracket',
			'tokenizeRightAngularBracket',
			
			// Tokenizer methods with multiple chars of the same kind
			'tokenizeBacktick',
			'tokenizeEquals',
			'tokenizeHash',
			'tokenizeMinus',
			'tokenizeStar',
			'tokenizeTilde',
			'tokenizeUnderscore',
			
			'tokenizeWhitespace',
		];
		
		// Used to determine if texttoken is not last token any more
		$lastCount = 0;
		
		while ($this->pos < $this->length) {
			foreach ($tokenizers AS $tokenizer) {
				if ($this->{$tokenizer}()) {
					continue 2;
				}
			}
			
			if (!isset($textToken) || (\count($this->tokens) > $lastCount)) {
				$textToken = new Token(Token::TEXT, $this->line, $this->column, "");
				$this->tokens[] = $textToken;
				$lastCount = \count($this->tokens);
			}
			
			$textToken->raw .= $this->raw[$this->pos];
			$textToken->length++;
			
			$this->pos++;
			$this->column++;
		}
		
		return $this->postProcess($this->tokens);
	}
	
	private function tokenizeNewline() {
		$found = '';
		
		if (($this->pos < $this->length) && ($this->raw[$this->pos] === "\r")) {
			$found .= "\r";
			$this->pos++;
		}
		
		if (($this->pos < $this->length) && ($this->raw[$this->pos] === "\n")) {
			$found .= "\n";
			$this->pos++;
		}
		
		if ($found === '') {
			return false;
		}
		
		$this->tokens[] = new Token(Token::NEWLINE, $this->line, $this->column, $found);
		$this->line++;
		$this->column = 1;
		
		return true;
	}
	
	private function tokenizeBackslash() {
		return $this->tokenizeChar('\\', Token::BACKSLASH);
	}
	
	private function tokenizeColon() {
		return $this->tokenizeChar(':', Token::COLON);
	}
	
	private function tokenizeBang() {
		return $this->tokenizeChar('!', Token::BANG);
	}
	
	private function tokenizeQuestionMark() {
		return $this->tokenizeChar('?', Token::QUESTION);
	}
	
	private function tokenizeSlash() {
		return $this->tokenizeChar('/', Token::SLASH);
	}
	
	private function tokenizeSingleQuote() {
		return $this->tokenizeChar('\'', Token::SINGLE_QUOTE);
	}
	
	private function tokenizeDoubleQuote() {
		return $this->tokenizeChar('"', Token::DOUBLE_QUOTE);
	}
	
	private function tokenizeLeftSquareBracket() {
		return $this->tokenizeChar('[', Token::SQUARE_BRACKET_LEFT);
	}
	
	private function tokenizeRightSquareBracket() {
		return $this->tokenizeChar(']', Token::SQUARE_BRACKET_RIGHT);
	}
	
	private function tokenizeLeftParenthesis() {
		return $this->tokenizeChar('(', Token::PARENTHESIS_LEFT);
	}
	
	private function tokenizeRightParenthesis() {
		return $this->tokenizeChar(')', Token::PARENTHESIS_RIGHT);
	}
	
	private function tokenizeLeftAngularBracket() {
		return $this->tokenizeChar('<', Token::ANGLE_BRACKET_LEFT);
	}
	
	private function tokenizeRightAngularBracket() {
		return $this->tokenizeChar('>', Token::ANGLE_BRACKET_RIGHT);
	}
	
	private function tokenizeChar($char, $tokenType) {
		if (!isset($this->raw[$this->pos]) || ($this->raw[$this->pos] !== $char)) {
			return false;
		}
		
		$this->tokens[] = new CharToken($tokenType, $this->line, $this->column, $char);
		$this->pos++;
		$this->column++;
		
		return true;
	}
	
	private function tokenizeBacktick() {
		return $this->tokenizeChars('`', Token::BACKTICK);
	}
	
	private function tokenizeEquals() {
		return $this->tokenizeChars('=', Token::EQUALS);
	}
	
	private function tokenizeHash() {
		return $this->tokenizeChars('#', Token::HASH);
	}
	
	private function tokenizeMinus() {
		return $this->tokenizeChars('-', Token::MINUS);
	}
	
	private function tokenizeStar() {
		return $this->tokenizeChars('*', Token::STAR);
	}
	
	private function tokenizeTilde() {
		return $this->tokenizeChars('~', Token::TILDE);
	}
	
	private function tokenizeUnderscore() {
		return $this->tokenizeChars('_', Token::UNDERSCORE);
	}
	
	private function tokenizeChars($char, $tokenType) {
		// Char not matching
		if (!isset($this->raw[$this->pos]) || ($this->raw[$this->pos] !== $char)) {
			return false;
		}
		
		$count = 0;
		$raw = '';
		
		do {
			$count++;
			$raw .= $char;
			$this->pos++;
		} while (($this->pos < $this->length) && ($this->raw[$this->pos] === $char));
		
		$this->tokens[] = new CharToken($tokenType, $this->line, $this->column, $raw);
		$this->column += $count;
		
		return true;
	}
	
	/**
	 * Try to tokenize whitespace. If $space is supplied, do not try to read previously gathered space but use $space instead
	 * @param string $space
	 */
	private function tokenizeWhitespace($space = null) {
		if ($space === null) {
			$space = $this->readWhitespace($this->pos);
		}
		
		if ($space === false) {
			return false;
		}
		
		$this->tokens[] = new Token(Token::WHITESPACE, $this->line, $this->column, $space);
		$this->column += \strlen($space);
		$this->pos += \strlen($space);
		
		return true;
	}
	
	/**
	 * Try to read white space from the supplied position
	 * White space is only space and tab here.
	 * Returns false if no white space was available
	 * 
	 * @param int $pos
	 * @return string
	 */
	private function readWhitespace($pos) {
		$space = '';
		
		while (($pos < $this->length) && (($this->raw[$pos] === "\t") || ($this->raw[$pos] === ' '))) {
			$space .= $this->raw[$pos];
			$pos++;
		}
		
		if ($space === '') {
			return false;
		} else {
			return $space;
		}
	}
	
	/**
	 * Try to read an HTML comment from the string to tokenize
	 * @return boolean
	 * @link http://jgm.github.io/stmd/spec.html#html-comment
	 */
	private function tokenizeHTMLComment() {
		return $this->tokenizeMultilineRawData(Token::HTML_COMMENT, '<!--', '-->', '--');
	}
	
	/**
	 * Read a processing instruction
	 * @return boolean
	 * @link http://jgm.github.io/stmd/spec.html#processing-instruction
	 */
	private function tokenizeProcessingInstruction() {
		return $this->tokenizeMultilineRawData(Token::PROCESSING_INSTRUCTIONS, '<?', '?>');
	}
	
	/**
	 * Read a CDATA section
	 * @return boolean
	 * @link http://jgm.github.io/stmd/spec.html#cdata-section
	 */
	private function tokenizeCData() {
		return $this->tokenizeMultilineRawData(Token::CDATA, '<![CDATA[', ']]>');
	}
	
	/**
	 * Try to parse raw data like HTML comments, processing data, CDATA, etc
	 * 
	 * @param string $startDelimiter String this block starts with
	 * @param string $endDelimiter String this block ends with
	 * @param string $doNotContain String that must noch exists within, defaults to endDelimiter
	 * @return boolean
	 */
	private function tokenizeMultilineRawData($tokenType, $startDelimiter, $endDelimiter, $doNotContain = null) {
		// Test if start delimiter follows
		if (\substr($this->raw, $this->pos, \strlen($startDelimiter)) !== $startDelimiter) {
			return false;
		}
		
		// Try to find end delimiter
		if (false === ($endpos = \strpos($this->raw, $endDelimiter, $this->pos+\strlen($startDelimiter)))) {
			throw new \RuntimeException('Can not find end delimiter ' . $endDelimiter . ' for start delimiter ' . $startDelimiter . ' in line ' . $this->line . ', character ' . $this->pos);
		}
		
		// Search for $doNotContain element
		if (($doNotContain !== null) && (false !== ($failpos = \strpos($this->raw, $doNotContain, $this->pos + \strlen($startDelimiter)))) && ($failpos < $endpos)) {
			throw new \RuntimeException('Found illegal string sequence ' . $doNotContain . ' for start delimiter ' . $startDelimiter . ' in line ' . $this->line . ', character ' . $this->pos);
		}
		
		$tokenLength = $endpos - $this->pos + \strlen($endDelimiter);
		$tokenText = \substr($this->raw, $this->pos, $tokenLength);
		$this->tokens[] = new Token($tokenType, $this->line, $this->column, $tokenText);
		
		$this->line += $this->countLinebreaks($tokenText);
		$this->column = $this->lastLineLength($tokenText)+1;
		$this->pos += $tokenLength;
		
		return true;
	}
	
	/**
	 * Read an attribute from the input.
	 * An attribute is preceeded by whitespace and has an optional value.
	 * 
	 * @return boolean
	 * @link http://jgm.github.io/stmd/spec.html#attribute
	 */
	public function tokenizeAttribute() {
		// Attribute is separated by space
		if (false === ($space = $this->readWhitespace($this->pos))) {
			return false;
		}
		
		// Try to read tag name
		if (false === ($name = $this->readAttributeName($this->pos + \strlen($space)))) {
			return false;
		}
		
		$this->tokenizeWhitespace($space);
		
		$this->tokens[] = new Token(Token::ATTRIBUTE_NAME, $this->line, $this->column, $name);
		$this->column += \strlen($name);
		$this->pos += \strlen($name);
		
		// Read attributes
		return $this->tokenizeAttributeValue();
	}
	
	/**
	 * Read an attribute value from the input.
	 * 
	 * An attribute value can be unquoted or quoted bei either single or double quotes
	 * 
	 * @return boolean
	 * @link http://jgm.github.io/stmd/spec.html#attribute-value
	 */
	private function tokenizeAttributeValue() {
		$pos = $this->pos;
		
		// Attribute name and = may be separated by space
		if (false !== ($space1 = $this->readWhitespace($pos))) {
			$pos += \strlen($space1);
		}
		
		// Can not read =
		if (($pos >= $this->length) || ($this->raw[$pos] !== '=')) {
			return false;
		}
		$pos++;
		
		// = and attribute value may be separated by space
		if (false !== ($space2 = $this->readWhitespace($pos))) {
			$pos += \strlen($space2);
		}
		
		// Try to read quoted string
		if (($pos < $this->length) && (($this->raw[$pos] === '\'') || ($this->raw[$pos] === '"'))) {
			$delimiter = $this->raw[$pos];
			
			if (false === ($endpos = \strpos($this->raw, $delimiter, $pos+1))) {
				throw new \RuntimeException('Can not find delimiter ' . $delimiter . ' for attribute value starting in line ' . $this->line . ' on position ' . $this->pos);
			}
			
			$type = ($delimiter === '"' ? AttributeValueToken::DOUBLE_QUOTED : AttributeValueToken::SINGLE_QUOTED);
		}
		
		// Read unquoted string
		else {
			$type = AttributeValueToken::UNQUOTED;
			$delimiter = '';
			
			$endpos = $pos;
			while (($endpos < $this->length) && (false === \strpos(' \'"=<>`', $this->raw[$endpos]))) {
				$endpos++;
			}
			
			if ($pos === $endpos) {
				throw new \RuntimeException('Can not find attribute value starting in line ' . $this->line . ' on position ' . $this->pos);
			}
		}
		
		$this->tokenizeWhitespace($space1);
		$this->tokenizeEquals();
		$this->tokenizeWhitespace($space2);
		
		$raw = \substr($this->raw, $this->pos + \strlen($delimiter), $endpos - $this->pos - \strlen($delimiter));
		$this->tokens[] = new AttributeValueToken($this->line, $this->pos, $raw, $type);
		$this->pos += \strlen($raw) + (2*\strlen($delimiter));
		$this->column += \strlen($raw) + (2*\strlen($delimiter));
		return true;
	}
	
	/**
	 * Try to read an attribute name
	 * @param int $pos
	 * @return string
	 */
	private function readAttributeName($pos) {
		// Try to read attribute name
		if (($pos >= $this->length) || (false === \stripos('abcdefghijklmnopqrstuvwxyz_:', $this->raw[$pos]))) {
			return false;
		}
		
		$name = $this->raw[$pos];
		$pos++;
		
		while (($pos < $this->length) && (false !== \stripos('abcdefghijklmnopqrstuvwxyz0123456789_.:-', $this->raw[$pos]))) {
			$name .= $this->raw[$pos];
			$pos++;
		}
		
		return $name;
	}
	
	/**
	 * Try to read an opening HTML tag
	 * @return boolean
	 * @link http://jgm.github.io/stmd/spec.html#open-tag
	 */
	private function tokenizeOpenTag() {
		$pos = $this->pos;
		
		// Get required angular bracket
		if ($this->raw[$pos] !== '<') {
			return false;
		} else {
			$pos++;
		}
		
		// Get alphanumberic tag name
		if (false === ($tagname = $this->readTagName($pos))) {
			return false;
		} else {
			$pos += \strlen($tagname);
		}
		
		// Require whitespace or closing tag
		if (($this->raw[$pos] !== ' ') && ($this->raw[$pos]) !== '>') {
			return false;
		}
		
		$this->tokenizeLeftAngularBracket();
		$this->tokenizeTagName($tagname);
		while ($this->tokenizeAttribute()); // Optional attributes
		$this->tokenizeWhitespace(); // Optional
		$this->tokenizeSlash(); // Optional / so no closing tag is required
		if (!$this->tokenizeRightAngularBracket()) {
			print_r($this->tokens);
			throw new \RuntimeException('No closing bracket for tag "' . $tagname . '" found in line ' . $this->line . ' on position ' . $this->column);
		}
		
		return true;
	}
	
	/**
	 * Try to read a closing HTML tag
	 * @return boolean
	 * @link http://jgm.github.io/stmd/spec.html#closing-tag
	 */
	private function tokenizeClosingTag() {
		// closing tag needs to start with <
		if ($this->raw[$this->pos] !== '<') {
			return false;
		}
		
		// followed by /
		if (!isset($this->raw[$this->pos+1]) || ($this->raw[$this->pos+1] !== '/')) {
			return false;
		}
		
		// followed by the tag name
		if (false === ($tagname = $this->readTagName($this->pos+2))) {
			return false;
		}
		
		$this->tokenizeLeftAngularBracket();
		$this->tokenizeSlash();
		$this->tokenizeTagName($tagname);
		$this->tokenizeWhitespace(); // Optional
		if (!$this->tokenizeRightAngularBracket()) {
			throw new \RuntimeException('No closing bracket for tag "' . $tagname . '" found in line ' . $this->line . ' on position ' . $this->column);
		}
		
		return true;
	}
	
	/**
	 * Try to tokenize a tag name.
	 * 
	 * If $name is supplied, don't try to read a tag name from the current position but assume $name can be tokenized.
	 * 
	 * @param string $name
	 * @return boolean
	 */
	private function tokenizeTagName($name = null) {
		if ($name === null) {
			$name = $this->readTagName($this->pos);
		}
		
		if ($name === false) {
			return false;
		}
		
		$this->tokens[] = new Token(Token::TAGNAME, $this->line, $this->column, $name);
		$this->column += \strlen($name);
		$this->pos += \strlen($name);
		
		return true;
	}
	
	/**
	 * Try to read a tag name or return false if not possible
	 * @return string
	 */
	private function readTagName($pos) {
		if (($pos >= $this->length) || !\ctype_alpha($this->raw[$pos])) {
			return false;
		}
		
		$tagname = '';
		
		do {
			$tagname .= $this->raw[$pos];
			$pos++;
		} while (($pos < $this->length) && (\ctype_alnum($this->raw[$pos])));
		
		return $tagname;
	}
	
	private function tokenizeURI() {
		if (false === ($schema = $this->readSchema($this->pos))) {
			return false;
		}
		
		$schema .= ':';
		
		$this->pos += \strlen($schema);
		
		while ($this->pos < $this->length) {
			if ((\ord($this->raw[$this->pos]) < 32) // Control characters
				|| ($this->raw[$this->pos] === ' ')
				|| ($this->raw[$this->pos] === '<')
				|| ($this->raw[$this->pos] === '>')) {
				break;
			}
			
			$schema .= $this->raw[$this->pos];
			$this->pos++;
		}
		
		$this->column += \strlen($schema);
	}
	
	/**
	 * Try to read a schema from the input.
	 * @return string
	 */
	private function readSchema($pos) {
		$schemes = [
			'coap',
			'doi',
			'aaa',
			'aaas',
			'about',
			'acap',
			'cap',
			'cid',
			'crid',
			'data',
			'dav',
			'dict',
			'dns',
			'file',
			'ftp',
			'geo',
			'go',
			'gopher',
			'h323',
			'http',
			'https',
			'iax',
			'icap',
			'im',
			'imap',
			'info',
			'ipp',
			'iris',
			'iris.beep',
			'iris.xpc',
			'iris.xpcs',
			'iris.lwz',
			'javascript',
			'ldap',
			'mailto',
			'mid',
			'msrp',
			'msrps',
			'mtqp',
			'mupdate',
			'news',
			'nfs',
			'ni',
			'nih',
			'nntp',
			'opaquelocktoken',
			'pop',
			'pres',
			'rtsp',
			'service',
			'session',
			'shttp',
			'sieve',
			'sip',
			'sips',
			'sms',
			'snmp',
			'soap.beep',
			'soap.beeps',
			'tag',
			'tel',
			'telnet',
			'tftp',
			'thismessage',
			'tn3270',
			'tip',
			'tv',
			'urn',
			'vemmi',
			'ws',
			'wss',
			'xcon',
			'xcon-userid',
			'xmlrpc.beep',
			'xmlrpc.beeps',
			'xmpp',
			'z39.50r',
			'z39.50s',
			'adiumxtra',
			'afp',
			'afs',
			'aim',
			'apt',
			'attachment',
			'aw',
			'beshare',
			'bitcoin',
			'bolo',
			'callto',
			'chrome',
			'chrome-extension',
			'com-eventbrite-attendee',
			'content',
			'cvs',
			'dlna-playsingle',
			'dlna-playcontainer',
			'dtn',
			'dvb',
			'ed2k',
			'facetime',
			'feed',
			'finger',
			'fish',
			'gg',
			'git',
			'gizmoproject',
			'gtalk',
			'hcp',
			'icon',
			'ipn',
			'irc',
			'irc6',
			'ircs',
			'itms',
			'jar',
			'jms',
			'keyparc',
			'lastfm',
			'ldaps',
			'magnet',
			'maps',
			'market',
			'message',
			'mms',
			'ms-help',
			'msnim',
			'mumble',
			'mvn',
			'notes',
			'oid',
			'palm',
			'paparazzi',
			'platform',
			'proxy',
			'psyc',
			'query',
			'res',
			'resource',
			'rmi',
			'rsync',
			'rtmp',
			'secondlife',
			'sftp',
			'sgn',
			'skype',
			'smb',
			'soldat',
			'spotify',
			'ssh',
			'steam',
			'svn',
			'teamspeak',
			'things',
			'udp',
			'unreal',
			'ut2004',
			'ventrilo',
			'view-source',
			'webcal',
			'wtai',
			'wyciwyg',
			'xfire',
			'xri',
			'ymsgr',
		];
		
		$name = '';
		
		while ($pos < $this->length) {
			// Could not get another part
			if (false === ($part = $this->readTagName($pos))) {
					return false;
			}
			
			$name .= $part;
			$pos += \strlen($part);
			
			// Next char is a colon, so check if schema is valid, otherwise no schema is readable
			if (isset($this->raw[$pos]) && ($this->raw[$pos] === ':')) {
				return (\in_array($name, $schemes) ? $name : false);
			}
			
			// Read dot or hyphen for constructed schemas
			if (isset($this->raw[$pos]) && (($this->raw[$pos] === '.') || ($this->raw[$pos] === '-'))) {
				$name .= $this->raw[$pos];
				$pos++;
			}
			
			// Seems to be nothing useful in next char, abort
			else {
				return false;
			}
		}
		
		return false;
	}
	
	/**
	 * Count the number of line breaks in a string
	 * @param string $string
	 * @return int
	 */
	private function countLinebreaks(&$string) {
		// Calculate number of lines 
		$rn = \substr_count($string, "\r\n");
		$r = \substr_count($string, "\r") - $rn;
		$n = \substr_count($string, "\n") - $rn;
		
		return $rn + $r + $n;
	}
	
	/**
	 * Calculate the number of bytes in the last line of the supplied string.
	 * @param string $string
	 * @return int
	 */
	private function lastLineLength(&$string) {
		$r = \strrpos($string, "\r");
		$n = \strrpos($string, "\n");
		
		if (($r === false) && ($n === false)) {
			return \strlen($string);
		} else {
			return \strlen($string) - (\max($r, $n)+1);
		}
	}
	
	private function tokenizeBackslashEscapes() {
		if ($this->raw[$this->pos] !== '\\') {
			return false;
		}
		
		if (!isset($this->raw[$this->pos+1]) || (false === \strpos('!"#$%&\'()*+,-./:;<=>?@[]^_`{|}~', $this->raw[$this->pos+1]))) {
			return false;
		}
		
		$this->tokens[] = new CharToken(Token::ESCAPED, $this->line, $this->column, $this->raw[$this->pos+1]);
		$this->pos += 2;
		$this->column += 2;
		
		return true;
	}
}
