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
namespace encog\ml\data\basic;

use encog\EncogError;
use encog\ml\data\MLData;
use encog\ml\data\MLDataPair;
use encog\util\kmeans\Centroid;
use SplFixedArray;

/**
 * A basic implementation of the MLDataPair interface. This implementation
 * simply holds and input and ideal MLData object.
 *
 * For supervised training both input and ideal should be specified.
 *
 * For unsupervised training the input property should be valid, however the
 * ideal property should contain null.
 */
class BasicMLDataPair implements MLDataPair {
	public function __construct(MLData $input, MLData $ideal = null) {
		$this->input = $input;
		$this->ideal = $ideal;
	}

	public function __toString() {
		return sprintf("[%s:Input:%s,Ideal:%s,Significance:%f]",
				__CLASS__,
				$this->getInput(),
				$this->getIdeal(),
				$this->getSignificance()
		);
	}

	public static function createPair(int $input, int $ideal): MLDataPair {
		return new static(new BasicMLData(array_fill(0, $input, 0.0)), $ideal > 0
				? new BasicMLData(array_fill(0, $ideal, 0.0)) : null);
	}

	public function createCentroid(): Centroid {
		if (!$this->input instanceof BasicMLData) {
			throw new EncogError("The input data type of " . get_class($this->input) . " must be BasicMLData.");
		}
		return new BasicMLDataPairCentroid($this);
	}

	public function getIdealArray(): array {
		if ($this->ideal) {
			return $this->ideal->getData()->toArray();
		}
		return [];
	}

	public function getInputArray(): array {
		return $this->input->getData()->toArray();
	}

	public function setIdealArray(array $values) {
		$values = SplFixedArray::fromArray($values);
		if (!$this->ideal) {
			$this->ideal = new BasicMLData($values);
		} else {
			$this->ideal->setData($values);
		}
	}

	public function setInputArray(array $values) {
		$this->input->setData(SplFixedArray::fromArray($values));
	}

	public function isSupervised(): bool {
		return $this->ideal !== null;
	}

	public function getIdeal(): MLData {
		return $this->ideal ?? new BasicMLData([]);
	}

	public function getInput(): MLData {
		return $this->input;
	}

	public function getSignificance(): float {
		return $this->significance;
	}

	public function setSignificance(float $value) {
		$this->significance = $value;
	}

	/** @var float */
	private $significance = 1.0;

	/** @var MLData */
	private $input;

	/** @var MLData */
	private $ideal;
}
