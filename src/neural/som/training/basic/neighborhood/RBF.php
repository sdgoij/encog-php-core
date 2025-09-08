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
namespace encog\neural\som\training\basic\neighborhood;

use encog\mathutil\rbf\GaussianFunction;
use encog\mathutil\rbf\InverseMultiquadricFunction;
use encog\mathutil\rbf\MexicanHatFunction;
use encog\mathutil\rbf\MultiquadricFunction;
use encog\mathutil\rbf\RadialBasisFunction;
use encog\mathutil\rbf\RBFType;
use encog\neural\NeuralNetworkError;

/**
 * Implements a multi-dimensional RBF neighborhood function.
 */
class RBF implements Func {
	public static function fromType(RBFType $type, int $size): RadialBasisFunction {
		if ($type == new RBFType(RBFType::Gaussian)) {
			return GaussianFunction::createZeroCentered($size);
		}
		if ($type == new RBFType(RBFType::InverseMultiquadric)) {
			return InverseMultiquadricFunction::createZeroCentered($size);
		}
		if ($type == new RBFType(RBFType::Multiquadric)) {
			return MultiquadricFunction::createZeroCentered($size);
		}
		if ($type == new RBFType(RBFType::MexicanHat)) {
			return MexicanHatFunction::createZeroCentered($size);
		}
		throw new NeuralNetworkError("Unknown RBF type.");
	}

	public static function create2D(RBFType $type, int $x, int $y): RBF {
		$rbf = new static($type, [$x, $y]);
		$rbf->getRBF()->getCenters()[0] = 0.0;
		$rbf->getRBF()->getCenters()[1] = 0.0;
		$rbf->setRadius(1.0);
		return $rbf;
	}

	public function __construct(RBFType $type, array $size) {
		$this->rbf = self::fromType($type, count($size));
		$this->size = $size;
		$this->calculateDisplacement();
	}

	public function calculate(int $current, int $best): float {
		$vector = array_fill(0, count($this->displacement), 0.0);
		$current = $this->translateCoordinates($current);
		$best = $this->translateCoordinates($best);
		for ($i = 0; $i < count($current); $i++) {
			$vector[$i] = $current[$i] - $best[$i];
		}
		return $this->rbf->calculate($vector);
	}

	public function getRadius(): float {
		return $this->rbf->getWidth();
	}

	public function setRadius(float $radius) {
		$this->rbf->setWidth($radius);
	}

	public function getRBF(): RadialBasisFunction {
		return $this->rbf;
	}

	private function calculateDisplacement() {
		$this->displacement = array_fill(0, count($this->size), 0);
		for ($i = 0; $i < count($this->size); $i++) {
			switch ($i) {
				case 0:
					$value = 0;
					break;
				case 1:
					$value = $this->size[0];
					break;
				default:
					$value = $this->displacement[$i-1] * $this->size[$i-1];
			}
			$this->displacement[$i] = $value;
		}
	}

	private function translateCoordinates(int $index): array {
		$result = array_fill(0, count($this->displacement), 0.0);
		$counter = $index;

		for ($i = count($this->displacement) - 1; $i >= 0; $i--) {
			$value = $this->displacement[$i] > 0 ? (int)($counter/$this->displacement[$i]) : $counter;
			$counter -= $this->displacement[$i] * $value;
			$result[$i] = $value;
		}
		return $result;
	}

	/** @var RadialBasisFunction */
	private $rbf;

	/** @var int[] */
	private $displacement = [];

	/** @var int[] */
	private $size = [];
}
