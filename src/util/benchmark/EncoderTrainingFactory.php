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
namespace encog\util\benchmark;
use encog\ml\data\basic\BasicMLDataSet;
use encog\ml\data\MLDataSet;

/**
 * This benchmark implements a Fahlman Encoder. Though probably not invented by Scott
 * Fahlman, such encoders were used in many of his papers, particularly:
 *   An Empirical Study of Learning Speed in Backpropagation Networks (Fahlman,1988)
 *
 * It provides a very simple way of evaluating classification neural networks.
 *   Basically, the input and output neurons are the same in count. However,
 *   there is a smaller number of hidden neurons. This forces the neural
 *   network to learn to encode the patterns from the input neurons to a
 *   smaller vector size, only to be expanded again to the outputs.
 *
 * The training data is exactly the size of the input/output neuron count.
 * Each training element will have a single column set to 1 and all other
 * columns set to zero. You can also perform in "complement mode", where
 * the opposite is true. In "complement mode" all columns are set to 1,
 * except for one column that is 0. The data produced in "complement mode"
 * is more difficult to train.
 *
 * Fahlman used this simple training data to benchmark neural networks when
 * he introduced the Quickprop algorithm in the above paper.
 */
class EncoderTrainingFactory {
	public static function generateTraining(int $inputs, bool $complement, float $inputMin = 0.0,
			$inputMax = 1.0, float $outputMin = null, float $outputMax = null): MLDataSet {
		$outputMin = $outputMin ?? $inputMin;
		$outputMax = $outputMax ?? $inputMax;
		$input = array_fill(0, $inputs, array_fill(0, $inputs, 0.0));
		$ideal = array_fill(0, $inputs, array_fill(0, $inputs, 0.0));
		for ($i = 0; $i < $inputs; $i++) {
			for ($j = 0; $j < $inputs; $j++) {
				if ($complement) {
					$input[$i][$j] = $i == $j ? $inputMax : $inputMin;
					$ideal[$i][$j] = $i == $j ? $outputMax : $outputMin;
				} else {
					$input[$i][$j] = $i == $j ? $inputMax : $inputMin;
					$ideal[$i][$j] = $i == $j ? $inputMax : $inputMin;
				}
			}
		}
		return new BasicMLDataSet($input, $ideal);
	}
}
