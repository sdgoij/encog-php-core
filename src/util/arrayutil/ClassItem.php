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
namespace encog\util\arrayutil;

/**
 * A class item.
 */
class ClassItem {
	public function __construct(string $name, int $index) {
		$this->name = $name;
		$this->index = $index;
	}

	public function __toString() {
		return sprintf("[ClassItem name=%s, index=%d]", $this->name, $this->index);
	}

	public function getName(): string {
		return $this->name;
	}

	public function setName(string $name) {
		$this->name = $name;
	}

	public function getIndex(): int {
		return $this->index;
	}

	public function setIndex(int $index) {
		$this->index = $index;
	}

	/** @var string */
	private $name;
	/** @var int */
	private $index;
}
