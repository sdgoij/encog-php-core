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

/**
 * Defines a Machine Learning Method that supports regression.  Regression
 * takes an input and produces numeric output.  Function approximation
 * uses regression.  Contrast this to classification, which uses the input
 * to assign a class.
 */
interface MLRegression extends MLInputOutput {
	public function compute(MLData $input): MLData;
}
