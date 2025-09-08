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

/**
 * A sequence set is a collection of data sets. Where each individual data set
 * is one "unbroken sequence" within the sequence set. This allows individual
 * observations to occur individually, indicating a break between them.
 *
 * The sequence set, itself, is a data set, so it can be used with any Encog
 * trainer. However, not all trainers are aware of sequence sets. Further, some
 * machine learning methods are unaffected by them. Sequence sets are typically
 * used with Hidden Markov Models (HMM)'s.
 */
interface MLSequenceSet extends MLDataSet {
	public function startNewSequence();
	public function getSequenceCount(): int;
	public function getSequence(int $index): MLDataSet;
	public function getSequences(): array;
	public function addDataSet(MLDataSet $data);
}
