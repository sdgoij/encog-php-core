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
namespace encog;

use encog\mathutil\randomize\factory\BasicRandomFactory;
use encog\mathutil\randomize\factory\RandomFactory;
use encog\plugin\EncogPluginBase;
use encog\plugin\EncogPluginLogging1;
use encog\plugin\system\SystemActivationPlugin;
use encog\plugin\system\SystemLoggingPlugin;
use encog\plugin\system\SystemMethodsPlugin;

/**
 * Main Encog class, does little more than provide version information and a
 * centralized plugin registry.
 */
final class Encog {
	/**
	 * The default encoding used by Encog.
	 */
	const DEFAULT_ENCODING = "UTF-8";
	/**
	 * The current encog version, this should be read from the properties.
	 */
	const VERSION = "3.4.0";
	/**
	 * The current encog version, this should be read from the properties.
	 */
	const COPYRIGHT = "Copyright 2014 by Heaton Research, Inc.";

	/**
	 * The current encog version, this should be read from the properties.
	 */
	const LICENSE = "Open Source under the Apache License";

	/**
	 * The current encog file version, this should be read from the properties.
	 */
	const FILE_VERSION = "1";

	/**
	 * The default precision to use for compares.
	 */
	const DEFAULT_PRECISION = 10;

	/**
	 * The version of the Encog (PHAR) we are working with. Given in the form x.x.x
	 */
	const ENCOG_VERSION = "encog.version";

	/**
	 * The encog file version. This determines of an encog file can be read.
	 * This is simply an integer, that started with zero and is incremented each
	 * time the format of the encog data file changes.
	 */
	const ENCOG_FILE_VERSION = "encog.file.version";

	/**
	 * Default point at which two doubles are equal.
	 */
	const DEFAULT_DOUBLE_EQUAL = 0.0000000000001;

	/** @var Encog */
	private static $instance;

	/** @var RandomFactory */
	private $randomFactory;

	/** @var EncogPluginBase[] */
	private $plugins;

	/** @var EncogPluginLogging1 */
	private $loggingPlugin;

	/** @var array */
	private $properties = [];

	/** @var EncogShutdownTask[] */
	private $shutdownTasks = [];

	/**
	 * Encog constructor.
	 */
	private function __construct() {
		$this->randomFactory = new BasicRandomFactory();
		$this->registerPlugin(new SystemLoggingPlugin());
		$this->registerPlugin(new SystemActivationPlugin());
		$this->registerPlugin(new SystemMethodsPlugin());
		$this->properties = [
			self::ENCOG_FILE_VERSION => self::FILE_VERSION,
			self::ENCOG_VERSION => self::VERSION,
		];
	}

	public static function getInstance(): Encog {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function reset() {
		self::$instance = null;
		return self::getInstance();
	}

	public function getPlugins(): array {
		return $this->plugins;
	}

	public function registerPlugin(EncogPluginBase $plugin) {
		if ($plugin->getPluginServiceType() != EncogPluginBase::TYPE_SERVICE) {
			if ($plugin->getPluginServiceType() == EncogPluginBase::TYPE_LOGGING) {
				if ($this->loggingPlugin !== null) {
					$this->removePlugin($plugin);
				}
				$this->loggingPlugin = $plugin;
			}
		}
		$this->plugins[] = $plugin;
	}

	public function unregisterPlugin(EncogPluginBase $plugin) {
		if ($plugin === $this->loggingPlugin) {
			$this->loggingPlugin = new SystemLoggingPlugin();
		}
		$this->removePlugin($plugin);
	}

	private function removePlugin(EncogPluginBase $plugin) {
		foreach ($this->plugins as $key => $registered) {
			if ($plugin === $registered) {
				array_splice($this->plugins, $key, 1);
			}
		}
	}

	public function getLoggingPlugin(): EncogPluginLogging1 {
		return $this->loggingPlugin;
	}

	public function getRandomFactory(): RandomFactory {
		return $this->randomFactory;
	}

	public function setRandomFactory(RandomFactory $factory) {
		$this->randomFactory = $factory;
	}

	public function addShutdownTask(EncogShutdownTask $task) {
		$this->shutdownTasks[] = $task;
	}

	public function removeShutdownTask(EncogShutdownTask $task) {
		if (($index = array_search($task, $this->shutdownTasks)) !== false) {
			array_splice($this->shutdownTasks, $index, 1);
		}
	}

	public function shutdown() {
		while (count($this->shutdownTasks)) {
			foreach ($this->shutdownTasks as $task) {
				$this->removeShutdownTask($task);
				$task->performShutdownTask();
			}
		}
	}
}
