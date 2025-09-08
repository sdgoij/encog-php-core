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
namespace encog\mathutil\rbf;

/**
 * Basic radial basis function. Defines centers for each of the RBF's. All RBF's
 * have a common radius and peak.
 */
abstract class BasicRBF implements RadialBasisFunction {
	public static function createSingleDimensional(float $center, float $peak, float $width): RadialBasisFunction {
		$rbf = new static();
		$rbf->setCenters([$center]);
		$rbf->setPeak($peak);
		$rbf->setWidth($width);
		return $rbf;
	}

	public static function createMultiDimensional(float $peak, array $centers, float $width): RadialBasisFunction {
		$rbf = new static();
		$rbf->setCenters($centers);
		$rbf->setPeak($peak);
		$rbf->setWidth($width);
		return $rbf;
	}

	public static function createZeroCentered(int $dimensions): RadialBasisFunction {
		$rbf = new static();
		$rbf->setCenters(array_fill(0, $dimensions, 0.0));
		$rbf->setPeak(1.0);
		$rbf->setWidth(1.0);
		return $rbf;
	}

	public function &getCenters(): array {
		return $this->centers;
	}

	public function setCenters(array $centers) {
		$this->centers = $centers;
	}

	public function getCenter(int $dimension): float {
		return $this->centers[$dimension];
	}

	public function getDimensions(): int {
		return count($this->centers);
	}

	public function getPeak(): float {
		return $this->peak;
	}

	public function setPeak(float $peak) {
		$this->peak = $peak;
	}

	public function getWidth(): float {
		return $this->width;
	}

	public function setWidth(float $width) {
		$this->width = $width;
	}

	/** @var float[] */
	private $centers = [];

	/** @var float */
	private $peak = 0.0;

	/** @var float */
	private $width = 0.0;
}
