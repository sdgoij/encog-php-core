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
namespace encog\neural\som\training\basic\neighborhood;

use encog\mathutil\rbf\RadialBasisFunction;
use encog\mathutil\rbf\RBFType;

/**
 * A neighborhood function based on an RBF function.
 */
class RBF1d implements Func {
	public static function fromType(RBFType $type): RBF1d {
		$rbf = RBF::fromType($type, 1);
		$rbf->setWidth(1.0);
		return new static($rbf);
	}

	public function __construct(RadialBasisFunction $rbf) {
		$this->rbf = $rbf;
	}

	public function calculate(int $current, int $best): float {
		return $this->rbf->calculate([$current-$best]);
	}

	public function getRadius(): float {
		return $this->rbf->getWidth();
	}

	public function setRadius(float $radius) {
		$this->rbf->setWidth($radius);
	}

	/** @var RadialBasisFunction */
	private $rbf;
}
