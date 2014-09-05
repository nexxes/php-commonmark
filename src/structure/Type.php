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
 * Containing the structure type constants
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
interface Type {
	/**
	 * The base element containing every other structural elements
	 */
	const ROOT = 'Document_Root'; 
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#block-quotes
	 */
	const CONTAINER_BLOCKQUOTE = 'Container_Block_Blockquote';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#lists
	 */
	const CONTAINER_LIST = 'Container_Block_List';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#horizontal-rules
	 */
	const LEAF_HR = 'Leaf_Block_Horizontal_Rule';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#atx-headers
	 */
	const LEAF_ATX = 'Leaf_Block_ATX_Header';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#setext-headers
	 */
	const LEAF_SETEXT = 'Leaf_Block_Setext_Header';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#indented-code-blocks
	 */
	const LEAF_INDENTED_CODE = 'Leaf_Block_Indented_Code_Block';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#fenced-code-blocks
	 */
	const LEAF_FENCED_CODE = 'Leaf_Block_Fenced_Code_Block';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#html-blocks
	 */
	const LEAF_HTML = 'Leaf_Block_HTML_Block';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#link-reference-definitions
	 */
	const LEAF_LINK_DEF = 'Leaf_Block_Link_Reference_Definition';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#paragraphs
	 */
	const LEAF_PARAGRAPH = 'Leaf_Block_Paragraph';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#blank-lines
	 */
	const LEAF_BLANK = 'Leaf_Block_Blank_Lines';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#backslash-escapes
	 */
	const INLINE_ESCAPED = 'Inline_Backslash_Escaped';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#entities
	 */
	const INLINE_ENTITY = 'Inline_Entity';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#code-span
	 */
	const INLINE_CODESPAN = 'Inline_Code_Span';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#emphasis-and-strong-emphasis
	 */
	const INLINE_EMPHASIS = 'Inline_Emphasis_and_Strong_Emphasis';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#links
	 */
	const INLINE_LINK = 'Inline_Link';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#images
	 */
	const INLINE_IMAGE = 'Inline_Image';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#autolinks
	 */
	const INLINE_AUTOLINK = 'Inline_Autolink';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#raw-html
	 */
	const INLINE_RAW_HTML = 'Inline_Raw_HTML';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#hard-line-breaks
	 */
	const INLINE_HARD_BREAK = 'Inline_Hard_Line_Break';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#soft-line-breaks
	 */
	const INLINE_SOFT_BREAK = 'Inline_Soft_Line_Break';
	
	/**
	 * @link http://jgm.github.io/stmd/spec.html#strings
	 */
	const INLINE_STRING = 'Inline_String';
}
