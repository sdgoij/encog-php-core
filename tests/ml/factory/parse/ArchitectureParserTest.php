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
namespace encog\test\ml\factory\parse;

use encog\EncogError;
use encog\ml\factory\parse\ArchitectureLayer;
use encog\ml\factory\parse\ArchitectureParser;
use encog\test\util\PrivateConstructorTest;
use PHPUnit\Framework\TestCase;

class ArchitectureParserTest extends TestCase {
	use PrivateConstructorTest;

	protected function getSubjectClassName(): string {
		return ArchitectureParser::class;
	}

	public function testParseLayers() {
		$this->assertEquals(["a", "b", "c"], ArchitectureParser::parseLayers("a->b->c"));
		$this->assertEquals(["a", "bc"], ArchitectureParser::parseLayers("a->bc"));
		$this->assertEquals(["abc"], ArchitectureParser::parseLayers("abc"));
		$this->assertEquals(["", ""], ArchitectureParser::parseLayers(" ->\t"));
		$layers = ArchitectureParser::parseLayers("?:B->TANH->3->LINEAR->?:B");
		$this->assertEquals(["?:B", "TANH", "3", "LINEAR", "?:B"], $layers);
	}

	public function testParseLayer() {
		$layerBias = new ArchitectureLayer();
		$layerBias->setCount(1);
		$layerBias->setUsedDefault(true);
		$layerBias->setBias(true);

		$layerTANH = new ArchitectureLayer();
		$layerTANH->setName("TANH");

		$layerParams = new ArchitectureLayer();
		$layerParams->setParams(["A" => '1', "B" => '2', "C" => '3']);
		$layerParams->setName("FUNC");

		$layer3 = new ArchitectureLayer();
		$layer3->setName("3");
		$layer3->setCount(3);

		$this->assertEquals($layerBias, ArchitectureParser::parseLayer("?:B", 1));
		$this->assertEquals($layerTANH, ArchitectureParser::parseLayer("TANH", 1));
		$this->assertEquals($layerParams, ArchitectureParser::parseLayer("FUNC(a=1,b=2,c=3)", 1));
		$this->assertCount(2, ArchitectureParser::parseLayer("x(a=1,b=2)", 1)->getParams());
		$this->assertEquals($layer3, ArchitectureParser::parseLayer("3", 1));
	}

	public function testParseLayerNegativeCount() {
		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Count cannot be less than zero.");
		ArchitectureParser::parseLayer("-1?", -1);
	}

	public function testParseLayerInvalidDefault() {
		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Default (?) in an invalid location.");
		ArchitectureParser::parseLayer("?", 1);
		ArchitectureParser::parseLayer("?", -1);
	}

	public function testParseLayerParams() {
		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Illegal parentheses.");
		ArchitectureParser::parseLayer("F()", 1);
		ArchitectureParser::parseLayer("F(", 1);
	}

	public function testParseParams() {
		$this->assertEquals(['A'=>'1', 'B'=>'x', 'C'=>'"'], ArchitectureParser::parseParams("a=1,b=\"x\",c=\"\"\""));
		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Missing equals(=) operator.");
		ArchitectureParser::parseParams("abc");
	}
}
