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
namespace encog\test\util\logging;

use encog\Encog;
use encog\plugin\EncogPluginBase;
use encog\plugin\system\SystemLoggingPlugin;
use encog\util\logging\EncogLogging;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class EncogLoggingTest extends TestCase {
	public function testLogMessage() {
		/** @var SystemLoggingPlugin|MockObject $plugin */
		$plugin = $this->createMock(SystemLoggingPlugin::class);
		$plugin->expects($this->any())
			->method("getPluginServiceType")
			->willReturn(EncogPluginBase::TYPE_LOGGING);
		$plugin->expects($this->once())
			->method("log")
			->with(EncogLogging::LEVEL_DEBUG, "derp");

		Encog::getInstance()->registerPlugin($plugin);
		EncogLogging::log(EncogLogging::LEVEL_DEBUG, "derp");
	}

	public function testLogException() {
		/** @var SystemLoggingPlugin|MockObject $plugin */
		$plugin = $this->createMock(SystemLoggingPlugin::class);
		$plugin->expects($this->any())
			->method("getPluginServiceType")
			->willReturn(EncogPluginBase::TYPE_LOGGING);
		$plugin->expects($this->once())
			->method("logException");

		Encog::getInstance()->registerPlugin($plugin);
		EncogLogging::logException(new RuntimeException("derp"));
	}

	public function testGetCurrentLevel() {
		/** @var SystemLoggingPlugin|MockObject $plugin */
		$plugin = $this->createMock(SystemLoggingPlugin::class);
		$plugin->expects($this->any())
			->method("getPluginServiceType")
			->willReturn(EncogPluginBase::TYPE_LOGGING);
		$plugin->expects($this->once())
			->method("getLogLevel")
			->willReturn(EncogLogging::LEVEL_DEBUG);
		Encog::getInstance()->registerPlugin($plugin);
		$this->assertSame(0, EncogLogging::getCurrentLevel());
	}

	public function tearDown(): void {
		parent::tearDown();
		Encog::reset();
	}
}
