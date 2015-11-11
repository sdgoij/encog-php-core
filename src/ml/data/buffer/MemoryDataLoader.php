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
namespace encog\ml\data\buffer;

use encog\ml\data\basic\BasicMLData;
use encog\ml\data\basic\BasicMLDataPair;
use encog\ml\data\basic\BasicMLDataSet;
use encog\ml\data\buffer\codec\DataSetCODEC;
use encog\ml\data\MLDataSet;
use encog\NullStatusReportable;
use encog\StatusReportable;

/**
 * This class is used, together with a CODEC, load training data from some
 * external file into an Encog memory-based training set.
 */
class MemoryDataLoader {
	/** @var DataSetCODEC */
	private $codec;
	/** @var StatusReportable */
	private $status;
	/** @var MLDataSet */
	private $result;

	public function __construct(DataSetCODEC $codec) {
		$this->status = new NullStatusReportable();
		$this->codec = $codec;
	}

	public function import(): MLDataSet {
		$this->status->report(0, 0, "Importing to memory");
		if (!$this->result) {
			$this->result = new BasicMLDataSet([]);
		}
		$this->codec->prepareRead();
		$input = $ideal = [];
		$significance = 0.0;
		$record = 0;
		while ($this->codec->read($input, $ideal, $significance)) {
			$a = new BasicMLData($input);
			$b = $this->codec->getIdealSize() > 0
				? new BasicMLData($ideal) : null;
			$pair = new BasicMLDataPair($a, $b);
			$pair->setSignificance($significance);
			$this->result->addPair($pair);

			if (++$record % 10000) {
				$this->status->report(0, 0, "Importing...");
			}
		}
		$this->status->report(0, 0, "Done importing to memory");
		$this->codec->close();
		return $this->result;
	}

	public function getCodec(): DataSetCODEC {
		return $this->codec;
	}

	public function setCodec(DataSetCODEC $codec) {
		$this->codec = $codec;
	}

	public function getStatus(): StatusReportable {
		return $this->status;
	}

	public function setStatus(StatusReportable $status) {
		$this->status = $status;
	}

	public function getResult() {
		return $this->result;
	}

	public function setResult(MLDataSet $result) {
		$this->result = $result;
	}
}
