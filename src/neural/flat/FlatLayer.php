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
namespace encog\neural\flat;

use encog\Encog;
use encog\engine\network\activation\ActivationFunction;
use encog\engine\network\activation\ActivationLinear;

/**
 * Used to configure a flat layer. Flat layers are not kept by a Flat Network,
 * beyond setup.
 */
class FlatLayer {
	/** @var ActivationFunction */
	private $activation;

	/** @var float */
	private $biasActivation;

	/** @var float  */
	private $dropoutRate;

	/** @var int */
	private $count;

	/** @var FlatLayer */
	private $contextFedBy;

	public function __construct(?ActivationFunction $af = null, int $count = 0,
			float $biasActivation = 1.0, float $dropoutRate = 0.0) {
		$this->activation = $af ?? new ActivationLinear();
		$this->biasActivation = $biasActivation;
		$this->dropoutRate = $dropoutRate;
		$this->count = $count;
	}

	public function __toString() {
		return sprintf("[%s:count=%d,bias=%s%s]", __CLASS__,
			$this->getCount(),
			$this->biasActivationToString(),
			$this->contextToString()
		);
	}

	public function getActivation(): ActivationFunction {
		return $this->activation;
	}

	public function setActivation(ActivationFunction $af) {
		$this->activation = $af;
	}

	public function getBiasActivation(): float {
		return $this->biasActivation;
	}

	public function setBiasActivation(float $a) {
		$this->biasActivation = $a;
	}

	/**
	 * @return FlatLayer|null
	 */
	public function getContextFedBy() {
		return $this->contextFedBy;
	}

	public function setContextFedBy(FlatLayer $from) {
		$this->contextFedBy = $from;
	}

	public function getDropoutRate(): float {
		return $this->dropoutRate;
	}

	public function setDropoutRate(float $v) {
		$this->dropoutRate = $v;
	}

	public function getContextCount(): int {
		if ($this->contextFedBy) {
			return $this->contextFedBy->getCount();
		}
		return 0;
	}

	public function getCount(): int {
		return $this->count;
	}

	public function getTotalCount(): int {
		return $this->getCount() + $this->getContextCount() + ($this->hasBias() ? 1 : 0);
	}

	public function hasBias(): bool {
		return abs($this->biasActivation) > Encog::DEFAULT_DOUBLE_EQUAL;
	}

	private function contextToString(): string {
		if ($this->contextFedBy) {
			return sprintf(",contextFed=%s", $this->contextFedBy == $this ? "itself" : $this->contextFedBy);
		}
		return "";
	}

	private function biasActivationToString(): string {
		return $this->hasBias() ? sprintf("%f", $this->biasActivation) : "false";
	}
}
