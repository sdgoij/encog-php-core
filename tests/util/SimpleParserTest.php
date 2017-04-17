<?php
/**
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace encog\test\util;

use encog\util\SimpleParser;
use PHPUnit_Framework_TestCase as TestCase;

class SimpleParserTest extends TestCase {
	public function testToString() {
		$parser = new SimpleParser("abc");
		$this->assertEquals("[Parser:abc]", (string)$parser);
		$parser->advance();
		$this->assertEquals("[Parser:bc]", (string)$parser);
	}

	public function testRemaining() {
		$parser = new SimpleParser("abc");
		for ($i = $parser->remaining(); $i >= 0; $i--) {
			$this->assertEquals($i, $parser->remaining());
			$parser->advance();
		}
	}

	public function testEatWhitespace() {
		$parser = new SimpleParser(" \t \t");
		$parser->eatWhiteSpace();

		$this->assertEquals(0, $parser->remaining());
	}

	public function testParseThroughComma() {
		$parser = new SimpleParser(",.");
		$this->assertTrue($parser->parseThroughComma());
		$this->assertFalse($parser->parseThroughComma());
	}

	public function testIsIdentifier() {
		$this->assertTrue((new SimpleParser("a"))->isIdentifier());
		$this->assertTrue((new SimpleParser("1"))->isIdentifier());
		$this->assertTrue((new SimpleParser("_"))->isIdentifier());

		$this->assertFalse((new SimpleParser(" "))->isIdentifier());
		$this->assertFalse((new SimpleParser("!"))->isIdentifier());
	}

	public function testIsWhitespace() {
		$this->assertTrue((new SimpleParser(" "))->isWhiteSpace());
		$this->assertTrue((new SimpleParser("\t"))->isWhiteSpace());
		$this->assertTrue((new SimpleParser("\n"))->isWhiteSpace());

		$this->assertFalse((new SimpleParser("a"))->isWhiteSpace());
		$this->assertFalse((new SimpleParser("5"))->isWhiteSpace());
	}

	public function testPeek() {
		$this->assertEquals("a", (new SimpleParser("a"))->peek());
	}

	public function testAdvance() {
		$parser = new SimpleParser("abc, def");

		do {
			$chars[] = $parser->peek();
			$parser->advance();
		} while (!$parser->eol());

		$this->assertEquals("abc, def", join("", $chars));
	}

	public function testAdvanceTo() {
		$parser = new SimpleParser("abc");
		$this->assertFalse($parser->eol());
		$parser->advanceTo(3);
		$this->assertTrue($parser->eol());
	}

	public function testReadChar() {
		$parser = new SimpleParser("abc, def.");

		do {
			$chars[] = $parser->readChar();
		} while (!$parser->eol());

		$this->assertEquals("abc, def.", join("", $chars));
	}

	public function testReadToWhitespace() {
		$parser = new SimpleParser("abc def\tghi");

		$this->assertEquals("abc", $parser->readToWhiteSpace());
		$parser->eatWhitespace();

		$this->assertEquals("def", $parser->readToWhiteSpace());
		$parser->eatWhiteSpace();

		$this->assertEquals("ghi", $parser->readToWhiteSpace());
	}

	public function testReadQuotedString() {
		$parser = new SimpleParser("\"abc\";");
		$this->assertEquals("abc", $parser->readQuotedString());
		$this->assertEquals(";", $parser->peek());
	}

	public function testReadToChars() {
		$parser = new SimpleParser("abc;def");
		$this->assertEquals("abc", $parser->readToChars(";"));
		$this->assertEquals(";", $parser->peek());
	}

	public function testLookAhead() {
		$parser = new SimpleParser("abc");
		$this->assertFalse($parser->lookAhead("ABC", false));
		$this->assertFalse($parser->lookAhead("DEF", true));
		$this->assertTrue($parser->lookAhead("ABC", true));
		$this->assertTrue($parser->lookAhead("abc"));
	}

	public function testMarkReset() {
		$parser = new SimpleParser("abc");
		$parser->mark();
		$parser->advance();
		$parser->reset();

		$this->assertEquals("a", $parser->peek());
	}

	public function testSkip() {
		$parser = new SimpleParser("abc");
		$parser->skip("ab");

		$this->assertEquals("c", $parser->peek());
	}
}
