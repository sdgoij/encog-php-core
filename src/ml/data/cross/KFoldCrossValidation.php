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
namespace encog\ml\data\cross;

use encog\mathutil\randomize\generate\BasicGenerateRandom;
use encog\mathutil\randomize\generate\GenerateRandom;
use encog\ml\data\versatile\MatrixMLDataSet;

class KFoldCrossValidation {
	/** @var MatrixMLDataSet */
	private $dataset;
	/** @var int */
	private $K;
	/** @var DataFold[] */
	private $folds = [];
	/** @var GenerateRandom */
	private $random;

	public function __construct(MatrixMLDataSet $dataset, int $K) {
		$this->random = new BasicGenerateRandom();
		$this->dataset = $dataset;
		$this->K = $K;
	}

	public function __clone() {
		$this->dataset = clone $this->dataset;
		$this->random = clone $this->random;
	}

	public function process(bool $shuffle) {
		$first = $this->buildFirstList($this->dataset->size());
		if ($shuffle) $this->shuffleList($first);
		$folds = $this->allocateFolds();
		$this->populateFolds($folds, $first);
		$this->buildSets($folds);
	}

	public function getRandom(): GenerateRandom {
		return $this->random;
	}

	public function setRandom(GenerateRandom $random) {
		$this->random = $random;
	}

	public function getDataSet(): MatrixMLDataSet {
		return $this->dataset;
	}

	public function getK(): int {
		return $this->K;
	}

	public function getFolds(): array {
		return $this->folds;
	}

	private function buildFirstList(int $length): array {
		$result = range(0, $length-1);
		if ($this->dataset) {
			for ($i = 0; $i < $length; $i++) {
				$result[$i] = $this->dataset->getMask()[$i] ?? 0;
			}
		}
		return $result;
	}

	private function shuffleList(array &$list) {
		for ($i = count($list)-1; $i >= 0; $i--) {
			$n = $this->random->nextInt($i);
			$temp = $list[$n];
			$list[$i] = $list[$n];
			$list[$n] = $temp;
		}
	}

	private function allocateFolds(): array {
		$size = $this->dataset->size();
		$p = $size / $this->K;
		$f = $size - $p * ($this->K-1);
		$folds[] = array_fill(0, $f, 0);
		for ($i = 1; $i < $this->K; $i++) {
			$folds[] = array_fill(0, $p, 0);
		}
		return $folds;
	}

	private function populateFolds(array &$folds, array $first) {
		foreach ($folds as $k => &$fold) {
			for ($i = 0; $i < count($fold); $i++) {
				$fold[$i] = $first[$k+$i];
			}
		}
	}

	private function buildSets(array $folds) {
		$this->folds = [];
		for ($i = 0; $i < $this->K; $i++) {
			$trainingSize = $validationSize = 0;
			for ($j = 0; $j < count($folds); $j++) {
				if ($i == $j) {
					$validationSize += count($folds[$j]);
				} else {
					$trainingSize += count($folds[$j]);
				}
			}
			$trainingMask = array_fill(0, $trainingSize, 0.0);
			$validationMask = array_fill(0, $validationSize, 0.0);
			$trainingIndex = 0;
			for ($j = 0; $j < count($folds); $j++) {
				$fold = $folds[$j];
				$size = count($fold);
				if ($i != $j) {
					self::copy($fold, 0, $trainingMask, $trainingIndex, $size);
					$trainingIndex += $size;
				} else {
					self::copy($fold, 0, $trainingMask, 0, $size);
				}
			}
			$this->folds[] = new DataFold(
				MatrixMLDataSet::createFromMatrixMLDataSet($this->dataset, $trainingMask),
				MatrixMLDataSet::createFromMatrixMLDataSet($this->dataset, $validationMask)
			);
		}
	}

	private static function copy(array $source, int $start, array &$dest, int $offset, int $length) {
		for ($i = 0; $i < $length; $i++) $dest[$offset+$i] = $source[$start+$i];
	}
}
