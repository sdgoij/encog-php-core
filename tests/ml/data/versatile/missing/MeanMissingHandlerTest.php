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
namespace encog\test\ml\data\versatile\missing;

use encog\EncogError;
use encog\ml\data\versatile\columns\ColumnDefinition;
use encog\ml\data\versatile\columns\ColumnType;
use encog\ml\data\versatile\missing\MeanMissingHandler;
use PHPUnit\Framework\TestCase;

class MeanMissingHandlerTest extends TestCase {
	public function testProcessString() {
		$this->expectExceptionMessage(
			"The mean missing handler only accepts continuous numeric values.");
		$this->expectException(EncogError::class);
		(new MeanMissingHandler())->processString(
			new ColumnDefinition("A", new ColumnType(ColumnType::ignore)));
	}

	public function testProcessDouble() {
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::continuous));
		$column->setMean(0.8);

		$this->assertEquals(0.8, (new MeanMissingHandler())->processDouble($column));
	}
}
