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
 * A CharToken represents a token that consists only of a single char that may be repeated several times.
 * The following invariant must always be true:
 * <code>
 * $this->raw = \str_repeat($this->char, $this->length);
 * </code>
 * 
 * @author Dennis Birkholz <dennis.birkholz@nexxes.net>
 */
class CharToken extends Token {
	public $char;
	
	public function __construct($type, $line, $pos, $raw) {
		parent::__construct($type, $line, $pos, $raw);
		$this->char = $raw[0];
	}
}
