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
namespace encog\plugin;

/**
 * The base plugin for Encog.
 */
interface EncogPluginBase {
	const TYPE_LOGGING = 1;
	const TYPE_SERVICE = 0;

	public function getPluginType(): int;
	public function getPluginServiceType(): int;
	public function getPluginName(): string;
	public function getPluginDescription(): string;
}
