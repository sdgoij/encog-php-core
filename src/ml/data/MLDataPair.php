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

/**
 * Training data is stored in two ways, depending on if the data is for
 * supervised, or unsupervised training.
 *
 * For unsupervised training just an input value is provided, and the ideal
 * output values are null.
 *
 * For supervised training both input and the expected ideal outputs are
 * provided.
 *
 * This interface abstracts classes that provide a holder for both of these two
 * data items.
 */
interface MLDataPair extends CentroidFactory {
	public function getIdealArray(): array;
	public function getInputArray(): array;
	public function setIdealArray(array $values);
	public function setInputArray(array $values);
	public function isSupervised(): bool;
	public function getIdeal(): MLData;
	public function getInput(): MLData;
	public function getSignificance(): float;
	public function setSignificance(float $value);
}
