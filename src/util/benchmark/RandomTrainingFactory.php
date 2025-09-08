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
namespace encog\util\benchmark;

use encog\mathutil\randomize\generate\LinearCongruentialRandom;
use encog\ml\data\basic\BasicMLData;
use encog\ml\data\basic\BasicMLDataSet;
use encog\ml\data\MLDataSet;

/**
 * Class used to generate random training sets. This will always generate
 * the same number outputs, as it always uses the same seed values. This
 * allows for the consistent results needed by the benchmark.
 */
final class RandomTrainingFactory {
	public static function generate(int $seed, int $size, int $input, int $ideal, float $min, float $max): MLDataSet {
		$random = new LinearCongruentialRandom($seed);
		$result = new BasicMLDataSet();
		for ($i = 0; $i < $size; $i++) {
			$inputData = new BasicMLData($input);
			$idealData = new BasicMLData($ideal);
			for ($j = 0; $j < $input; $j++) {
				$inputData->setDataAt($j, $random->nextDouble($max, $min));
			}
			for ($j = 0; $j < $ideal; $j++) {
				$idealData->setDataAt($j, $random->nextDouble($max, $min));
			}
			$result->add($inputData, $idealData);
		}
		return $result;
	}

	public static function from(MLDataSet &$data, int $seed, int $size, float $min, float $max) {
		$data = self::generate($seed, $size, $data->getInputSize(), $data->getIdealSize(), $min, $max);
	}

	private function __construct() {
	}
}
