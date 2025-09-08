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
 * Multi-dimensional Mexican Hat, or Ricker wavelet, function.
 *
 * It is usually only referred to as the "Mexican hat" in the Americas, due to
 * cultural association with the "sombrero". In technical nomenclature this
 * function is known as the Ricker wavelet, where it is frequently employed to
 * model seismic data.
 *
 * http://en.wikipedia.org/wiki/Mexican_Hat_Function
 */
class MexicanHatFunction extends BasicRBF {
	public function calculate(array $values): float {
		$width = $this->getWidth();
		$norm = 0.0;

		foreach ($this->getCenters() as $key => $center) {
			$norm += pow($values[$key]-$center, 2) / (2.0*$width*$width);
		}
		return $this->getPeak() * (1-$norm) * exp(-$norm/2);
	}
}
