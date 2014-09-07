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
class AttributeValueToken extends Token {
	const SINGLE_QUOTED = 1;
	const DOUBLE_QUOTED = 2;
	const UNQUOTED = 3;
	
	public $quoting;
	
	public $value;
	
	public function __construct($line, $pos, $value, $quoting = self::UNQUOTED) {
		$this->value = $value;
		$quote = ($quoting === self::DOUBLE_QUOTED ? '"' : ($quoting === self::SINGLE_QUOTED ? '\'' : ''));
		parent::__construct(Token::ATTRIBUTE_VALUE, $line, $pos, $quote . $value . $quote);
		$this->quoting = $quoting;
	}
}
