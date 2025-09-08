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
namespace encog\ml\data;

use encog\util\kmeans\CentroidFactory;
use SplFixedArray;

/**
 * Defines an array of data. This is an array of double values that could be
 * used either for input data, actual output data or ideal output data.
 */
interface MLData extends CentroidFactory {
	public function add(int $index, float $value);
	public function clear();
	public function clone(): MLData;
	public function getData(): SplFixedArray;
	public function getDataAt(int $index): float;
	public function setData(SplFixedArray $values);
	public function setDataAt(int $index, float $value);
	public function size(): int;
}
