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
 * @coversDefaultClass \nexxes\tokenizer\Tokenizer
 */
class TokenizerTest extends \PHPUnit_Framework_TestCase {
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
		
		$reflectionObject = new \ReflectionObject($tokenizer);
		$exec = $reflectionObject->getMethod($method);
		$exec->setAccessible(true);
		
		if ($reference) {
			return $exec->invokeArgs($tokenizer, [&$text]);
		} else {
			return $exec->invoke($tokenizer, $text);
		}
	}
	
	/**
	 * Call one of the private tokenizeXXX methods of the Tokenizer class
	 * 
	 * @param string $method Name of the private method to call
	 * @param string $text The text to supply as first (and only) argument
	 */
	private function callTokenizer($method, $text) {
		$tokenizer = new Tokenizer($text);
		
		$reflectionObject = new \ReflectionObject($tokenizer);
		$exec = $reflectionObject->getMethod($method);
		$exec->setAccessible(true);
		$exec->invoke($tokenizer);
		return $tokenizer->getTokens();
	}
	
	/**
	 * Tests token types that consist of a single char that may be repeated multiple times
	 * @test 
	 */
	public function testRepeatedSingleCharTokens() {
		$tests = [
			[ 'tokenizeBacktick', '```', Token::BACKTICK ],
			[ 'tokenizeEquals', '=========', Token::EQUALS ],
			[ 'tokenizeHash', '###', Token::HASH ],
			[ 'tokenizeMinus', '-----', Token::MINUS ],
			[ 'tokenizeStar', '*****', Token::STAR ],
			[ 'tokenizeTilde', '~~', Token::TILDE ],
			[ 'tokenizeUnderscore', '_', Token::UNDERSCORE ],
		];
		
		foreach ($tests AS $test) {
			list($method, $text, $tokenType) = $test;
			$tokens = $this->callTokenizer($method, $text);
			
			$this->assertCount(1, $tokens, 'Token missing, method: ' . $method);
			$this->assertEquals($tokenType, $tokens[0]->type);
			$this->assertEquals(\strlen($text), $tokens[0]->length);
			$this->assertEquals($text, $tokens[0]->raw);
		}
	}
	
	/**
	 * @test
	 * @covers ::tokenizeWhitespace
	 * @covers ::readWhitespace
	 * @covers ::postProcess
	 */
	public function testNewline() {
		$text = "\n\n\r\n\r";
		$tokenizer = new Tokenizer($text);
		$tokens = $tokenizer->run();
		
		$this->assertCount(4, $tokens);
		$this->assertEquals(Token::NEWLINE, $tokens[0]->type);
		$this->assertEquals(Token::NEWLINE, $tokens[1]->type);
		$this->assertEquals(Token::NEWLINE, $tokens[2]->type);
		$this->assertEquals(Token::NEWLINE, $tokens[3]->type);
	}
	
	/**
	 * Test tokens that match only a single character
	 * @test
	 */
	public function testSingleCharTokens() {
		$text = ':([<\'"' . "\r\n\n\r" . '"\'>])';
		$tokenizer = new Tokenizer($text);
		$tokens = $tokenizer->run();
		
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
	
	/**
	 * @test
	 * @covers ::tokenizeHTMLComment
	 * @covers ::tokenizeMultilineRawData
	 */
	public function testHTMLCommentToken() {
		$text = "<!-- Foo\nBar\r\nBlubb -->";
		$tokens = $this->callTokenizer('tokenizeHTMLComment', $text);
		
		$this->assertCount(1, $tokens);
		$this->assertEquals(Token::HTML_COMMENT, $tokens[0]->type);
	}
	
	/**
	 * Try to read whitespace
	 * @test
	 * @covers ::tokenizeWhitespace
	 * @covers ::readWhitespace
	 */
	public function testWhitespaceToken() {
		$text = ' ';
		$tokens = $this->callTokenizer('tokenizeWhitespace', $text);
		$this->assertCount(1, $tokens);
		$this->assertEquals(Token::WHITESPACE, $tokens[0]->type);
		$this->assertEquals(\strlen($text), $tokens[0]->length);
		
		$text = '      ';
		$tokens = $this->callTokenizer('tokenizeWhitespace', $text);
		$this->assertCount(1, $tokens);
		$this->assertEquals(Token::WHITESPACE, $tokens[0]->type);
		$this->assertEquals(\strlen($text), $tokens[0]->length);
		
		$text = 'xxx';
		$tokens = $this->callTokenizer('tokenizeWhitespace', $text);
		$this->assertCount(0, $tokens);
		
		$text = "\t  \t ";
		$tokens = $this->callTokenizer('tokenizeWhitespace', $text);
		$this->assertCount(1, $tokens);
		$this->assertEquals(Token::WHITESPACE, $tokens[0]->type);
		$this->assertEquals(\strlen($text), $tokens[0]->length);
	}
	
	/**
	 * Try to tokenize an HTML attribute
	 * @test
	 * @covers ::tokenizeAttribute
	 * @covers ::tokenizeAttributeValue
	 * @covers ::readAttributeName
	 */
	public function testAttributeToken() {
		$text = '   attrName';
		$tokens = $this->callTokenizer('tokenizeAttribute', $text);
		
		$this->assertCount(2, $tokens);
		$this->assertEquals(Token::WHITESPACE, $tokens[0]->type);
		$this->assertEquals(Token::ATTRIBUTE_NAME, $tokens[1]->type);
		$this->assertEquals('attrName', $tokens[1]->raw);
		
		$text = '   attrName = attrValue foo';
		$tokens = $this->callTokenizer('tokenizeAttribute', $text);
		
		$this->assertCount(6, $tokens);
		$this->assertEquals(Token::WHITESPACE, $tokens[0]->type);
		$this->assertEquals(Token::ATTRIBUTE_NAME, $tokens[1]->type);
		$this->assertEquals('attrName', $tokens[1]->raw);
		$this->assertEquals(Token::WHITESPACE, $tokens[2]->type);
		$this->assertEquals(Token::EQUALS, $tokens[3]->type);
		$this->assertEquals(Token::WHITESPACE, $tokens[4]->type);
		$this->assertEquals(Token::ATTRIBUTE_VALUE, $tokens[5]->type);
		$this->assertEquals('attrValue', $tokens[5]->raw);
		$this->assertEquals(AttributeValueToken::UNQUOTED, $tokens[5]->quoting);
		
		$text = '   attrName= "attrValue"';
		$tokens = $this->callTokenizer('tokenizeAttribute', $text);
		
		$this->assertCount(5, $tokens);
		$this->assertEquals(Token::WHITESPACE, $tokens[0]->type);
		$this->assertEquals(Token::ATTRIBUTE_NAME, $tokens[1]->type);
		$this->assertEquals('attrName', $tokens[1]->raw);
		$this->assertEquals(Token::EQUALS, $tokens[2]->type);
		$this->assertEquals(Token::WHITESPACE, $tokens[3]->type);
		$this->assertEquals(Token::ATTRIBUTE_VALUE, $tokens[4]->type);
		$this->assertEquals('attrValue', $tokens[4]->value);
		$this->assertEquals(AttributeValueToken::DOUBLE_QUOTED, $tokens[4]->quoting);
		
		$text = '   attrName=\'attr"#`Value\'';
		$tokens = $this->callTokenizer('tokenizeAttribute', $text);
		
		$this->assertCount(4, $tokens);
		$this->assertEquals(Token::WHITESPACE, $tokens[0]->type);
		$this->assertEquals(Token::ATTRIBUTE_NAME, $tokens[1]->type);
		$this->assertEquals('attrName', $tokens[1]->raw);
		$this->assertEquals(Token::EQUALS, $tokens[2]->type);
		$this->assertEquals(Token::ATTRIBUTE_VALUE, $tokens[3]->type);
		$this->assertEquals('attr"#`Value', $tokens[3]->value);
		$this->assertEquals(AttributeValueToken::SINGLE_QUOTED, $tokens[3]->quoting);
	}
	
	/**
	 * Try to tokenize an unclosed attribute value
	 * @test
	 * @covers ::tokenizeAttributeValue
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Can not find delimiter
	 */
	public function testUnclosedAttributeValue() {
		$text = ' attrName="attrValue';
		$this->callTokenizer('tokenizeAttribute', $text);
	}
	
	/**
	 * Try to tokenize empty attribute value
	 * @test
	 * @covers ::tokenizeAttributeValue
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Can not find attribute value
	 */
	public function testEmptyAttributeValue() {
		$text = ' attrName=  `<foo>`';
		$this->callTokenizer('tokenizeAttribute', $text);
	}
	
	/**
	 * @test
	 * @covers ::tokenizeOpenTag
	 * @covers ::tokenizeTagName
	 * @covers ::readTagName
	 * @covers ::tokenizeAttribute
	 * @covers ::tokenizeAttributeValue
	 * @covers ::readAttributeName
	 */
	public function testOpenTag() {
		$text = '<name>';
		$tokens = $this->callTokenizer('tokenizeOpenTag', $text);
		
		$this->assertCount(3, $tokens);
		$this->assertEquals(Token::ANGLE_BRACKET_LEFT, $tokens[0]->type);
		$this->assertEquals(Token::TAGNAME, $tokens[1]->type);
		$this->assertEquals('name', $tokens[1]->raw);
		$this->assertEquals(Token::ANGLE_BRACKET_RIGHT, $tokens[2]->type);
		
		/* Namespaced tag names are not (yet) supported by the specification as of 2014-09-05 (YYYY-MM-DD)
		$text = '<ns:name />';
		$tokens = $this->callTokenizer('tokenizeOpenTag', $text);
		
		$this->assertCount(3, $tokens);
		$this->assertEquals(Token::ANGLE_BRACKET_LEFT, $tokens[0]->type);
		$this->assertEquals(Token::TAGNAME, $tokens[1]->type);
		$this->assertEquals('ns:name', $tokens[1]->raw);
		$this->assertEquals(Token::WHITESPACE, $tokens[2]->type);
		$this->assertEquals(Token::SLASH, $tokens[3]->type);
		$this->assertEquals(Token::ANGLE_BRACKET_RIGHT, $tokens[4]->type);
		 */
		
		$text = '<tagname attr1=foo attr2="bar" />';
		$tokens = $this->callTokenizer('tokenizeOpenTag', $text);
		
		$this->assertCount(13, $tokens);
		
		$this->assertEquals(Token::ANGLE_BRACKET_LEFT, $tokens[0]->type);
		
		$this->assertEquals(Token::TAGNAME, $tokens[1]->type);
		$this->assertEquals('tagname', $tokens[1]->raw);
		
		$this->assertEquals(Token::WHITESPACE, $tokens[2]->type);
		
		$this->assertEquals(Token::ATTRIBUTE_NAME, $tokens[3]->type);
		$this->assertEquals('attr1', $tokens[3]->raw);
		
		$this->assertEquals(Token::EQUALS, $tokens[4]->type);
		
		$this->assertEquals(Token::ATTRIBUTE_VALUE, $tokens[5]->type);
		$this->assertEquals('foo', $tokens[5]->raw);
		$this->assertEquals(AttributeValueToken::UNQUOTED, $tokens[5]->quoting);
		
		$this->assertEquals(Token::WHITESPACE, $tokens[6]->type);
		
		$this->assertEquals(Token::ATTRIBUTE_NAME, $tokens[7]->type);
		$this->assertEquals('attr2', $tokens[7]->raw);
		
		$this->assertEquals(Token::EQUALS, $tokens[8]->type);
		
		$this->assertEquals(Token::ATTRIBUTE_VALUE, $tokens[9]->type);
		$this->assertEquals('bar', $tokens[9]->value);
		$this->assertEquals(AttributeValueToken::DOUBLE_QUOTED, $tokens[9]->quoting);
		
		$this->assertEquals(Token::WHITESPACE, $tokens[10]->type);
		
		$this->assertEquals(Token::SLASH, $tokens[11]->type);
		
		$this->assertEquals(Token::ANGLE_BRACKET_RIGHT, $tokens[12]->type);
	}
	
	/**
	 * @test
	 * @covers ::tokenizeClosingTag
	 * @covers ::tokenizeTagName
	 * @covers ::readTagName
	 */
	public function testClosingTag() {
		$text = '</name >';
		$tokens = $this->callTokenizer('tokenizeClosingTag', $text);
		
		$this->assertCount(5, $tokens);
		
		$this->assertEquals(Token::ANGLE_BRACKET_LEFT, $tokens[0]->type);
		
		$this->assertEquals(Token::SLASH, $tokens[1]->type);
		
		$this->assertEquals(Token::TAGNAME, $tokens[2]->type);
		$this->assertEquals('name', $tokens[2]->raw);
		
		$this->assertEquals(Token::WHITESPACE, $tokens[3]->type);
		
		$this->assertEquals(Token::ANGLE_BRACKET_RIGHT, $tokens[4]->type);
	}
}
