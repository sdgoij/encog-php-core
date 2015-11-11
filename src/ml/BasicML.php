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

/**
 * A class that provides basic property functionality for the MLProperties
 * interface.
 */
abstract class BasicML implements MLMethod, MLProperties {
	public function getProperties(): array {
		return $this->properties;
	}

	public function getPropertyDouble(string $name): float {
		return (float)$this->getProperty($name);
	}

	public function getPropertyLong(string $name): int {
		return (int)$this->getProperty($name);
	}

	public function getPropertyString(string $name): string {
		return (string)$this->getProperty($name);
	}

	public function getProperty(string $name) {
		return $this->properties[$name] ?? null;
	}

	public function setProperty(string $name, $value) {
		$this->properties[$name] = $value;
	}

	abstract public function updateProperties();

	/**
	 * @var array
	 */
	private $properties = [];
}
