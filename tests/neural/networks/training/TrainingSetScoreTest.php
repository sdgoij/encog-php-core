<?php
declare(strict_types=1);
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
namespace encog\test\neural\networks\training;

use encog\ml\data\basic\BasicMLDataSet;
use encog\ml\MLMethod;
use encog\neural\networks\BasicNetwork;
use encog\neural\networks\layers\BasicLayer;
use encog\neural\networks\training\TrainingError;
use encog\neural\networks\training\TrainingSetScore;
use encog\test\neural\networks\XORUtil;
use PHPUnit\Framework\TestCase;

class TrainingSetScoreTest extends TestCase {
	public function testCalculateScore() {
		$network = new BasicNetwork();
		$network->addLayer(BasicLayer::create(2));
		$network->addLayer(BasicLayer::create(3));
		$network->addLayer(BasicLayer::create(1));
		$network->getStructure()->finalizeStructure();

		$this->assertSame(0.25, (new TrainingSetScore(XORUtil::createDataSet()))->calculateScore($network));

		$this->expectExceptionMessage("The method must support regression (MLRegression)");
		$this->expectException(TrainingError::class);
		(new TrainingSetScore(new BasicMLDataSet()))->calculateScore(new class implements MLMethod {});
	}

	public function testShouldMinimize() {
		$this->assertTrue((new TrainingSetScore(new BasicMLDataSet()))->shouldMinimize());
	}

	public function testRequireSingleThreaded() {
		$this->assertFalse((new TrainingSetScore(new BasicMLDataSet()))->requireSingleThreaded());
	}
}
