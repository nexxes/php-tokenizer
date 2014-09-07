<?php

/*
 * This file is part of the nexxes/tokenizer package.
 * It is licenced under the terms of the LGPL v3 or later.
 * 
 * Copyright 2014 Dennis Birkholz <dennis.birkholz@nexxes.net>.
 * 
 * More information can be found in the LICENSE file.
 */

namespace nexxes\tokenizer;

/**
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
class Token {
	const WHITESPACE = 1;
	const NEWLINE = 2;
	const TEXT = 3;
	const BLANKLINE = 4;
	const ESCAPED = 5;
	
	const MINUS = 100;
	const EQUALS = 101;
	const HASH = 102;
	const STAR = 103;
	const TILDE = 104;
	const UNDERSCORE = 105;
	const BACKTICK = 106;
	const SINGLE_QUOTE = 107;
	const DOUBLE_QUOTE = 108;
	const COLON = 109;
	const SLASH = 110;
	const BACKSLASH = 111;
	const BANG = 112;
	const QUESTION = 113;
	
	const PARENTHESIS_LEFT = 200;
	const PARENTHESIS_RIGHT = 201;
	const SQUARE_BRACKET_LEFT = 202;
	const SQUARE_BRACKET_RIGHT = 203;
	const CURLY_BRACKET_LEFT = 204;
	const CURLY_BRACKET_RIGHT = 205;
	const ANGLE_BRACKET_LEFT = 206;
	const ANGLE_BRACKET_RIGHT = 207;
	
	const HTML = 300;
	const HTML_COMMENT = 301;
	const CDATA = 302;
	const PROCESSING_INSTRUCTIONS = 303;
	const DECLARATION = 304;
	const TAGNAME = 305;
	const ATTRIBUTE_NAME = 306;
	const ATTRIBUTE_VALUE = 307;
	
	/**
	 * The actual type of the token
	 * @var mixed
	 */
	public $type;
	
	/**
	 * The line in the original token stream
	 * @var int
	 */
	public $line;
	
	/**
	 * The start position in the line the token appeared
	 * @var int
	 */
	public $pos;
	
	/**
	 * The raw content of the token
	 * @var string 
	 */
	public $raw;
	
	/**
	 * Length of the token
	 * @var int 
	 */
	public $length;
	
	public function __construct($type, $line, $pos, $raw) {
		$this->type = $type;
		$this->line = $line;
		$this->pos = $pos;
		$this->raw = $raw;
		$this->length = \strlen($this->raw);
	}
}
