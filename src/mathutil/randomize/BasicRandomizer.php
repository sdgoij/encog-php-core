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
namespace encog\mathutil\randomize;

use encog\mathutil\matrices\Matrix;
use encog\mathutil\randomize\generate\BasicGenerateRandom;
use encog\mathutil\randomize\generate\GenerateRandom;
use encog\ml\MLEncodable;
use encog\ml\MLMethod;
use encog\neural\networks\BasicNetwork;

/**
 * Provides basic functionality that most randomizers will need.
 */
abstract class BasicRandomizer implements Randomizer {
	public function __construct() {
		$this->random = new BasicGenerateRandom();
	}

	final public function getRandom(): GenerateRandom {
		return $this->random;
	}

	final public function setRandom(GenerateRandom $r) {
		$this->random = $r;
	}

	final public function nextDouble(): float {
		return $this->random->nextDouble();
	}

	final public function nextDoubleRange(float $min, float $max): float {
		return ($max-$min) * $this->random->nextDouble() + $min;
	}

	public function randomize(MLMethod $method) {
		if ($method instanceof BasicNetwork) {
			for ($i = 0; $i < $method->getLayerCount()-1; $i++) {
				$this->randomizeLayer($method, $i);
			}
		} else if ($method instanceof MLEncodable) {
			$encoded = [];
			$method->encodeToArray($encoded);
			$this->randomizeArray($encoded);
			$method->decodeFromArray($encoded);
		}
	}

	public function randomizeArray(array &$values, int $start = 0, ?int $size = null) {
		if ($size === null) $size = count($values);
		for ($i = $start; $i < $start+$size; $i++) {
			$values[$i] = $this->randomizeFloat($values[$i]);
		}
	}

	public function randomizeArray2D(array &$values) {
		for ($i = 0; $i < count($values); $i++) {
			for ($j = 0; $j < count($values[$i]); $j++) {
				$values[$i][$j] = $this->randomizeFloat($values[$i][$j]);
			}
		}
	}

	public function randomizeMatrix(Matrix $m) {
		for ($r = 0; $r < $m->getRows(); $r++) {
			for ($c = 0; $c < $m->getCols(); $c++) {
				$m->getData()[$r][$c] = $this->randomizeFloat($m->get($r, $c));
			}
		}
	}

	public function randomizeLayer(BasicNetwork $network, int $index) {
		$maxFromIndex = $network->getLayerTotalNeuronCount($index);
		$maxToIndex = $network->getLayerNeuronCount($index + 1);
		for ($from = 0; $from < $maxFromIndex; $from++) {
			for ($to = 0; $to < $maxToIndex; $to++) {
				$weight = $this->randomizeFloat($network->getWeight($index, $from, $to));
				$network->setWeight($index, $from, $to, $weight);
			}
		}
	}

	/** @var GenerateRandom */
	protected $random;
}
