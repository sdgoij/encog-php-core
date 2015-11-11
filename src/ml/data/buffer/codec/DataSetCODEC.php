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
namespace encog\ml\data\buffer\codec;

/**
 * A CODEC is used to encode and decode data. The DataSetCODEC is designed to
 * move data to/from the Encog binary training file format, used by
 * BufferedNeuralDataSet. CODECs are provided for such items as CSV files,
 * arrays and many other sources.
 */
interface DataSetCODEC {
	public function read(array &$input, array &$ideal, float &$significance): bool;
	public function write(array $input, array $ideal, float $significance);
	public function prepareWrite(int $records, int $input, int $ideal);
	public function prepareRead();
	public function getInputSize();
	public function getIdealSize();
	public function close();
}
