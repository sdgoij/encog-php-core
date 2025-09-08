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

use encog\engine\network\activation\ActivationElliott;
use encog\engine\network\activation\ActivationLinear;
use encog\engine\network\activation\ActivationSoftMax;
use encog\ml\data\basic\BasicMLData;
use encog\neural\networks\BasicNetwork;
use encog\neural\networks\layers\BasicLayer;
use encog\util\arrayutil\NormalizeArray;

require_once __DIR__ . "/../../vendor/autoload.php";

function ff(array $values): array {
	foreach ($values as $value) {
		$result[] = (float)sprintf("%1.02f", $value);
	}
	return $result ?? [];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$input = new BasicMLData((new NormalizeArray(0.0, 1.0))->process(json_decode(file_get_contents("php://input"))));

	$network = new BasicNetwork();
	$network->addLayer(BasicLayer::create(28*28, new ActivationLinear(), true));
	$network->addLayer(BasicLayer::create(38, new ActivationElliott(), true));
	$network->addLayer(BasicLayer::create(10, new ActivationSoftMax(), false));
	$network->getStructure()->finalizeStructure();
	$network->decodeFromArray(require __DIR__ . "/weights.php");

	header("Content-Type: application/json");
	echo json_encode(ff($network->compute($input)->getData()->toArray()));
	exit(1);
}
?>
<html>
<head>
	<script>
		var canvas, scaled, ctx, mousedown = false;

		document.addEventListener('DOMContentLoaded', function() {

			canvas = document.getElementById("draw");
			scaled = document.getElementById("scaled").getContext("2d");

			ctx = canvas.getContext("2d");
			ctx.fillStyle = "rgb(255,255,255)";
			ctx.fillRect(0,0,300,300);

			canvas.addEventListener("mousemove", function(e) {
				var x, y;
				if (!e.offsetX) {
					x = e.pageX - e.target.offsetLeft;
					y = e.pageY - e.target.offsetTop;
				} else {
					x = e.offsetX;
					y = e.offsetY;
				}
				if (mousedown) {
					draw(x, y);
				}
			}, false);

			canvas.addEventListener("mousedown", function() {mousedown = true}, false);
			canvas.addEventListener("mouseout", function() {mousedown = false}, false);
			canvas.addEventListener("mouseup", function() {mousedown = false}, false);
		}, false);

		function reset() {
			ctx.save();
			ctx.setTransform(1, 0, 0, 1, 0, 0);
			ctx.clearRect(0,0, canvas.width, canvas.height);
			ctx.restore();

			scaled.save();
			scaled.setTransform(1, 0, 0, 1, 0, 0);
			scaled.clearRect(0,0, scaled.canvas.width, scaled.canvas.height);
			scaled.restore();

			ctx.fillStyle = "rgb(255,255,255)";
			ctx.fillRect(0,0,300,300);

			console.clear();
		}

		function draw(x, y) {
			ctx.fillStyle = "black";
			ctx.beginPath();
			ctx.arc(x, y, 15, 0, Math.PI*2, true);
			ctx.closePath();
			ctx.fill();

			scaled.drawImage(canvas,0,0,28,28);
		}

		function compute() {
			var image = scaled.getImageData(0,0,28,28).data, data = [];
			for (var i = 0; i < image.length; i += 4) {
				data.push(255-(image[i]+image[i+1]+image[i+2])/3);
			}
			var xhr = new XMLHttpRequest();
			xhr.open("POST", "<?=$_SERVER["PHP_SELF"]?>");
			xhr.setRequestHeader("Content-type", "application/json");
			xhr.onreadystatechange = function() {
				if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
					var result = JSON.parse(xhr.responseText);
					for (var i in result) {
						console.log(i + ": " + result[i]);
					}
				}
			};
			xhr.send(JSON.stringify(data));
		}
	</script>
</head>
<body>
<canvas id="draw" height="300" width="300" style="border: solid"></canvas>
<canvas id="scaled" height="28" width="28" style="border: solid"></canvas>
<hr>
<button onclick="compute();">Compute</button>
<button onclick="reset();">Reset</button>
</body>
</html>
