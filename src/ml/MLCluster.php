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

use encog\ml\data\MLData;
use encog\ml\data\MLDataSet;

/**
 * Defines a cluster. Usually used with the MLClustering method to break input
 * into clusters.
 */
interface MLCluster {
	public function add(MLData $pair);
	public function createDataSet(): MLDataSet;
	public function get(int $pos): MLData;
	public function getData(): array;
	public function remove(MLData $data);
	public function size(): int;
}
