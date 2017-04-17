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
namespace encog\ml\train\strategy;

use encog\ml\train\MLTrain;

/**
 * Training strategies can be added to training algorithms. Training
 * strategies allow different additional logic to be added to an existing
 * training algorithm. There are a number of different training strategies
 * that can perform various tasks, such as adjusting the learning rate or
 * momentum, or terminating training when improvement diminishes. Other
 * strategies are provided as well.
 */
interface Strategy {
	public function init(MLTrain $train);
	public function preIteration();
	public function postIteration();
}
