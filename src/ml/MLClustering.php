<?php
/**
 * Copyright 2015-2016 Tim Jurcka <sdgoij@gmail.com>
 *
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
namespace encog\ml;

/**
 * A machine learning method that is used to break data into clusters. The
 * number of clusters is usually defined beforehand. This differs from
 * the MLClassification method in that the data is clustered as an entire
 * group. If additional data must be clustered later, the entire group
 * must be re-clustered.
 */
interface MLClustering extends MLMethod {
	public function iteration(int $count = 1);
	public function getClusters(): array;
	public function numClusters(): int;
}
