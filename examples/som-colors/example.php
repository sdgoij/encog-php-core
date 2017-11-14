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

use encog\mathutil\randomize\RangeRandomizer;
use encog\mathutil\rbf\RBFType;
use encog\ml\data\basic\BasicMLData;
use encog\neural\som\SOM;
use encog\neural\som\training\basic\BasicTrainSOM;
use encog\neural\som\training\basic\neighborhood\RBF;
use encog\util\Random;

require __DIR__ . "/../../vendor/autoload.php";

const Iterations = 1000;
const Samples = 12;

if (isset($_GET["es"])) {
	$network = new SOM(3, 50 * 50);
	$network->reset();

	$neighborhood = RBF::create2D(new RBFType(RBFType::Gaussian), 50, 50);
	$trainer = new BasicTrainSOM($network, 0.01, null, $neighborhood);
	$trainer->setAutoDecay(Iterations, 0.8, 0.003, 30, 5);

	$random = new Random();
	$samples = [];

	for ($i = 0; $i < Samples; $i++) {
		$samples[] = new BasicMLData([
			RangeRandomizer::randomFloat(-1.0, 1.0, $random),
			RangeRandomizer::randomFloat(-1.0, 1.0, $random),
			RangeRandomizer::randomFloat(-1.0, 1.0, $random),
		]);
	}

	header("Content-Type: text/event-stream");
	set_time_limit(0);

	for ($i = 0; $i < Iterations; $i++) {
		$index = RangeRandomizer::randomInt(0, count($samples) - 1, $random);
		$trainer->trainInputPattern($samples[$index]);
		$trainer->autoDecay();
		echo "data: ", json_encode($network->getWeights()->toPackedArray()), "\n\n";
		ob_flush();
		flush();
	}
	sleep(10);
	exit(0);
}
?>
<!DOCTYPE html>
<title>SOM colors</title>
<canvas id="loader" height="50" width="50" style="border:1px solid black"></canvas>
<canvas id="anus" height="400" width="400" style="border:1px solid black"></canvas>
<hr>
<script>
  (function () {
		var src = document.getElementById("loader");
		var copy = document.getElementById("anus");

		function drawWeights(frame) {
			var loader = src.getContext("2d");
			var full = copy.getContext("2d");
			var image = loader.getImageData(0, 0, 50, 50);

			for (var i = 0, p = 0; i < image.data.length; i += 4) {
				image.data[i+0] = uint8(frame[p++]);
				image.data[i+1] = uint8(frame[p++]);
				image.data[i+2] = uint8(frame[p++]);
				image.data[i+3] = 255;
			}
			loader.putImageData(image, 0, 0);

			full.drawImage(src, 0, 0, copy.width, copy.height);
		}

		function uint8(v) {
			return Math.max(Math.min(128*v+128, 255), 0);
		}

    var events = new EventSource("?es");
    events.onmessage = function (event) {
    	try {
    		var frameWeights = JSON.parse(event.data);
    		window.requestAnimationFrame(function () {
    			drawWeights(frameWeights);
    		});
    	} catch (err) {
    		console.log(err);
    	}
		};
	})();
</script>
