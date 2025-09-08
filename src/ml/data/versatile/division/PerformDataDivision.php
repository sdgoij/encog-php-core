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
namespace encog\ml\data\versatile\division;

use encog\mathutil\randomize\generate\GenerateRandom;
use encog\ml\data\versatile\MatrixMLDataSet;

/**
 * Perform a data division.
 */
class PerformDataDivision {
	/** @var bool */
	private $shuffle;
	/** @var GenerateRandom */
	private $random;

	public function __construct(bool $shuffle, GenerateRandom $random) {
		$this->shuffle = $shuffle;
		$this->random = $random;
	}

	public function isShuffle(): bool {
		return $this->shuffle;
	}

	public function getRandom(): GenerateRandom {
		return $this->random;
	}

	public function perform(array &$divisions, MatrixMLDataSet $data, int $input, int $ideal) {
		$this->generateCounts($divisions, $data->size());
		$this->generateMasks($divisions);
		if ($this->shuffle) {
			$this->performShuffle($divisions, $data->size());
		}
		$this->createDividedDatasets($divisions, $data, $input, $ideal);
	}

	private function generateCounts(array &$divisions, int $total) {
		$count = 0;
		/** @var DataDivision $division */
		foreach ($divisions as $division) {
			$division->setCount((int)($division->getPercent()*$total));
			$count += $division->getCount();
		}
		$remaining = $total - $count;
		$numDiv = count($divisions);
		while ($remaining-- > 0) {
			$division = $divisions[$this->random->nextInt($numDiv-1)];
			$division->setCount($division->getCount()+1);
		}
	}

	private function generateMasks(array &$divisions) {
		$index = 0;
		/** @var DataDivision $division */
		foreach ($divisions as $division) {
			for ($i = 0; $i < $division->getCount(); $i++) {
				$division->setMaskIndex($i, $index++);
			}
		}
	}

	private function createDividedDatasets(array &$divisions, MatrixMLDataSet $parent, int $input, int $ideal) {
		/** @var DataDivision $division */
		foreach ($divisions as $division) {
			$ds = MatrixMLDataSet::createFromArray($parent->getData(), $input, $ideal, $division->getMask());
			$ds->setLagWindowSize($parent->getLagWindowSize());
			$ds->setLeadWindowSize($parent->getLeadWindowSize());
			$division->setDataSet($ds);
		}
	}

	private function performShuffle(array &$divisions, int $total) {
		while ($total-- > 0) $this->virtualSwap($divisions, $total, $this->random->nextInt($total));
	}

	private function virtualSwap(array &$divisions, int $a, int $b) {
		$divA = $divB = null;
		$offsetA = $offsetB = 0;
		$baseIndex = 0;

		/** @var DataDivision $division */
		foreach ($divisions as $division) {
			$baseIndex += $division->getCount();
			if (!$divA && $a < $baseIndex) {
				$offsetA = $a - ($baseIndex - $division->getCount());
				$divA = $division;
			}
			if (!$divB && $b < $baseIndex) {
				$offsetB = $b - ($baseIndex - $division->getCount());
				$divB = $division;
			}
		}

		$temp = $divA->getMask()[$offsetA];
		$divA->setMaskIndex($offsetA, $divB->getMask()[$offsetB]);
		$divB->setMaskIndex($offsetB, $temp);
	}
}
