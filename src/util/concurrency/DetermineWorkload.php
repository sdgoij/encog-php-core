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
namespace encog\util\concurrency;

use encog\EncogError;
use encog\mathutil\IntRange;
use encog\util\Runtime;

/**
 * Used by several Encog training methods to break up a workload. Can also be
 * used to determine the number of threads to use. If zero threads are
 * specified, Encog will query the processor count and determine the best number
 * of threads to use.
 */
class DetermineWorkload {
	public function __construct(int $workload, int $threads = 0) {
		if ($workload < 1) {
			throw new EncogError("Workload too small.");
		}
		$this->threads = min($threads, $workload);
		$this->workload = $workload;

		if (!$this->threads) {
			$processors = Runtime::getAvailableProcessors();
			$workloadPerThread = $this->workload / $processors;
			if ($workloadPerThread < 100) {
				$processors = max(1, (int)$this->workload/100);
			}
			$this->threads = $processors;
		}
	}

	/** @return IntRange[] */
	public function calculateWorkers(): array {
		$workloadPerThread = $this->workload / $this->threads;
		$workers = [];
		for ($i = 0; $i < $this->threads; $i++) {
			$low = $i * $workloadPerThread;
			$high = $i < $this->threads-1
				? ($i + 1) * $workloadPerThread - 1
				: $this->workload -1;
			$workers[] = new IntRange($high, $low);
		}
		return $workers;
	}

	public function getThreadsCount(): int {
		return $this->threads;
	}

	private $threads;
	private $workload;
}
