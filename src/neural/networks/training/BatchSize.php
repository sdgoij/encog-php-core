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
namespace encog\neural\networks\training;

/**
 * The batch size. Specify 1 for pure online training. Specify 0 for pure batch
 * training (complete training set in one batch). Otherwise specify the batch
 * size for batch training.
 */
interface BatchSize {
	public function getBatchSize(): int;
	public function setBatchSize(int $value);
}
