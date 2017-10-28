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
namespace encog\test\ml\data\temporal;

use encog\ml\data\temporal\TemporalDataType;
use PHPUnit\Framework\TestCase;

class TemporalDataTypeTest extends TestCase {
	public function testDefaultTypes() {
		$this->assertEquals(new TemporalDataType(TemporalDataType::RAW), TemporalDataType::$RAW);
		$this->assertEquals(new TemporalDataType(TemporalDataType::PERCENT_CHANGE), TemporalDataType::$PERCENT_CHANGE);
		$this->assertEquals(new TemporalDataType(TemporalDataType::DELTA_CHANGE), TemporalDataType::$DELTA_CHANGE);
	}
}
