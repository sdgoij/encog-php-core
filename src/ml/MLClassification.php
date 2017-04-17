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
namespace encog\ml;

use encog\ml\data\MLData;

/**
 * This interface defines a MLMethod that is used for classification.
 * Classification defines the output to be a class. A MLMethod that uses
 * classification is attempting to use the input to place items into
 * classes. It is assumed that an item will only be in one single class.
 * If an item can be in multiple classes, one option is to create additional
 * classes that represent the compound classes.
 */
interface MLClassification extends MLInputOutput {
	public function classify(MLData $input): int;
}
