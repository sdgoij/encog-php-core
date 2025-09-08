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
namespace encog\ml\data\temporal;

use encog\engine\network\activation\ActivationFunction;
use encog\ml\data\temporal\TemporalDataType as Type;

class TemporalDataDescription {
	/** @var Type */
	private $type;
	/** @var ActivationFunction */
	private $activation;
	/** @var float */
	private $low;
	/** @var float */
	private $high;
	/** @var bool */
	private $input;
	/** @var bool */
	private $predict;
	/** @var int */
	private $index;

	public function __construct(
			Type $type,
			bool $input,
			bool $predict,
			float $low = 0.0,
			float $high = 0.0,
			?ActivationFunction $activation = null
	) {
		$this->type = $type;
		$this->input = $input;
		$this->predict = $predict;
		$this->low = $low;
		$this->high = $high;
		$this->activation = $activation;
		$this->index = 0;
	}

	public function getActivation(): ?ActivationFunction {
		return $this->activation;
	}

	public function getHigh(): float {
		return $this->high;
	}

	public function getLow(): float {
		return $this->low;
	}

	public function getIndex(): int {
		return $this->index;
	}

	public function setIndex(int $index) {
		$this->index = $index;
	}

	public function getType(): Type {
		return $this->type;
	}

	public function isInput(): bool {
		return $this->input;
	}

	public function isPredict(): bool {
		return $this->predict;
	}
}
