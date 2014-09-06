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

namespace nexxes\stmd\structure;

/**
 * Description of Block
 *
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
class Block implements StructureInterface, \ArrayAccess, \IteratorAggregate, \Countable {
	/**
	 * Topmost structural element
	 * @var Document
	 */
	private $root;
	
	/**
	 * Structural element containing this element
	 * @var Block
	 */
	private $parent;
	
	/**
	 * List of tokens this elements is composed of
	 * @var array<\nexxes\stmd\token\Token>
	 */
	protected $tokens;
	
	/**
	 * List of elements within this block structure
	 * @var array<StructureInterface>
	 */
	protected $elements = [];
	
	/**
	 * The element type of this structual element, one of the constants definied in Type
	 * @var string
	 * @see Type
	 */
	protected $type;
	
	/**
	 * The inline data of this element.
	 * Can be a raw string or an object structure which can be casted to a string.
	 * @var type 
	 */
	public $inline = '';
	
	/**
	 * Storage for custom block information such as the header level, etc.
	 * @var array
	 */
	public $meta = [];
	
	
	
	
	/**
	 * 
	 * @param Block $parent
	 * @param array<\nexxes\stmd\token\Token> $tokens
	 */
	public function __construct($type, Block $parent, array $tokens) {
		$this->type = $type;
		$this->tokens = $tokens;
		$this->parent = $parent;
		$this->root = $parent->root;
	}
	
	/**
	 * {@inheritdoc}
	 * @implements StructureInterface
	 */
	public function getParent() {
		return $this->parent;
	}
	
	/**
	 * {@inheritdoc}
	 * @implements StructureInterface
	 */
	public function getRoot() {
		return $this->root;
	}
	
	/**
	 * {@inheritdoc}
	 * @implements StructureInterface
	 */
	public function getTokens() {
		return $this->tokens;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * Get the number of elements this block contains
	 * @return int
	 * @implements \Countable
	 */
	public function count() {
		return \count($this->elements);
	}
	
	/**
	 * Allow to iterate over the elements in this block
	 * @return \ArrayIterator
	 * @implements \IteratorAggregate
	 */
	public function getIterator() {
		return new \ArrayIterator($this->elements);
	}
	
	/**
	 * @param mixed $offset
	 * @return boolean
	 * @implements \ArrayAccess
	 */
	public function offsetExists($offset) {
		return isset($this->elements[$offset]);
	}
	
	/**
	 * @param mixed $offset
	 * @return StructureInterface
	 * @implements \ArrayAccess
	 */
	public function offsetGet($offset) {
		return $this->elements[$offset];
	}
	
	/**
	 * @param int $offset
	 * @param StructureInterface $value
	 * @return StructureInterface
	 * @implements \ArrayAccess
	 */
	public function offsetSet($offset, $value) {
		if (!($value instanceof StructureInterface)) {
			throw new \InvalidArgumentException(static::class . ' can only contain element that implement the ' . StructureInterface::class . ' interface!');
		}
		
		// Append elements
		if ($offset === null) {
			$this->elements[] = $value;
		}
		
		// Replace existing elements
		elseif (isset($this->elements[$offset])) {
			$this->elements[$offset] = $value;
		}
		
		// Append again
		elseif (\is_int($offset) && ($offset === \count($this->elements))) {
			$this->elements[] = $value;
		}
		
		// Prevent strings and holes in the array
		else {
			throw new \InvalidArgumentException('Invalid element index "' . $offset . '"!');
		}
		
		return $value;
	}
	
	/**
	 * Unsets an element.
	 * Afterwards the elements are re-indexed to prevent holes.
	 * 
	 * @param mixed $offset
	 * @implements \ArrayAccess
	 */
	public function offsetUnset($offset) {
		unset($this->elements[$offset]);
		$this->elements = \array_values($this->elements);
	}
}
