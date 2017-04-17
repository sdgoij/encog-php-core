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

use encog\mathutil\ComplexNumber;
use SplFixedArray;

/**
 * This class implements a data object that can hold complex numbers.  It
 * implements the interface MLData, so it can be used with nearly any Encog
 * machine learning method.  However, not all Encog machine learning methods
 * are designed to work with complex numbers.  A Encog machine learning method
 * that does not support complex numbers will only be dealing with the
 * real-number portion of the complex number.
 */
interface MLComplexData extends MLData {
	public function addComplex(int $index, ComplexNumber $value);
	public function getComplexData(): SplFixedArray;
	public function getComplexDataAt(int $index): ComplexNumber;
	public function setComplexData(SplFixedArray $data);
	public function setComplexDataAt(int $index, ComplexNumber $data);
}

