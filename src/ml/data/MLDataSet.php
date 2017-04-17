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
namespace encog\ml\data;

use IteratorAggregate;

/**
 * An interface designed to abstract classes that store machine learning data.
 * This interface is designed to provide EngineDataSet objects. These can be
 * used to train machine learning methods using both supervised and unsupervised
 * training.
 *
 * Some implementations of this interface are memory based. That is they store
 * the entire contents of the dataset in memory.
 *
 * Other implementations of this interface are not memory based. These
 * implementations read in data as it is needed. This allows very large datasets
 * to be used. Typically the add methods are not supported on non-memory based
 * datasets.
 */
interface MLDataSet extends IteratorAggregate {
	public function getIdealSize(): int;
	public function getInputSize(): int;
	public function isSupervised(): bool;
	public function getRecordCount(): int;
	public function getRecord(int $index, MLDataPair $pair);
	public function openAdditional(): MLDataSet;
	public function add(MLData $input, MLData $ideal = null);
	public function addPair(MLDataPair $pair);
	public function close();
	public function size(): int;
	public function get(int $index): MLDataPair;
}
