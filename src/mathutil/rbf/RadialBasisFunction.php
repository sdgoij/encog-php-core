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
namespace encog\mathutil\rbf;

/**
 * A multi-dimension RBF.
 */
interface RadialBasisFunction {
	public function calculate(array $values): float;
	public function getCenter(int $dimension): float;
	public function getDimensions(): int;
	public function getPeak(): float;
	public function setPeak(float $peak);
	public function getWidth(): float;
	public function setWidth(float $width);
	public function &getCenters(): array;
	public function setCenters(array $centers);
}
