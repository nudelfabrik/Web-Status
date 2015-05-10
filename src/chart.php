<?php	 

	/**
	* Chart Class
	*
	* This class creates a chart from a submitted
	* json file and displays it.
	*
	* @author Kevin Fiedler <kevinfiedler93f@gmail.com>
	* @copyright 2015 Kevin Fiedler
	* @license Check license file in this repo
	*/
	class chart {
		
		const VALUE_NOT_FOUND = -424242;
		
		private $colors = array('#2e89f9', '#ee2e22', '#fed105', '#f48026', '#31e618', '#97015e');
		
		// private section
		private $input = "";
		private $json = "";
		private $error = "";
		
		/**
	   	* Sets $foo to a new value upon class instantiation
	   	*
	   	* @param string $val a value required for the class
	   	* @return void
	   	*/
		private function decodeJSON() {
			$this->debug_to_console('Decoding JSON file.');
			$this->json = json_decode(utf8_encode(file_get_contents($this->input)), false, 512, JSON_BIGINT_AS_STRING);
			$this->error = json_last_error();
		}
		
		private function getGraphTitle() {
			return $this->json->graph->title;
		}
		
		private function getGraphType() {
			return $this->json->graph->type;
		}
		
		private function getYAxisMin() {
			$min = $this->json->graph->yAxis->minValue;
			if ($min == "") {
				return self::VALUE_NOT_FOUND;
			}
			return $min;
		}
		
		private function getYAxisMax() {
			$max = $this->json->graph->yAxis->maxValue;
			if ($max == "") {
				return self::VALUE_NOT_FOUND;
			}
			return $max;
		}
		
		private function getSequenceCount() {
			return count($this->json->graph->datasequences);
		}
		
		private function getSequenceLength() {
			return count($this->json->graph->datasequences[0]->datapoints);
		}
		
		private function getSequenceTitle($sequence) {
			return $this->json->graph->datasequences[$sequence]->title;
		}
		
		private function getDatapoint($sequence, $index) {
			return $this->json->graph->datasequences[$sequence]->datapoints[$index]->value;
		}
		
		function debug_to_console($data) {
			if ( is_array( $data ) )
				$output = "<script>console.log('Chart PHP: " . implode( ',', $data) . "');</script>";
			else
				$output = "<script>console.log('Chart PHP: " . $data . "');</script>";
	
			echo $output;
		}
	
		
		
		// public section
		public function __construct($filename) {
			if (!isset($filename)) {
				$this->debug_to_console("The class could not be created. No filename submitted.");
			} else {
				$this->debug_to_console("The class was initiated with parameter \"" . $filename .  "\".");
				$this->input = $filename;
				$this->decodeJSON();
				$this->debug_to_console($this->getError());
			}
  		}	
		
		public function __destruct() {
      		$this->debug_to_console('The class was destroyed.');
  		}
		
		public function __toString() {
      		return __CLASS__ . '"' . $this->input . '"<br />';
  		}
		
		
		public function getError() {
			$result = "Error: ";
			switch($error) {
        		case JSON_ERROR_NONE:
					$result = $result . 'No errors'; break;
				case JSON_ERROR_DEPTH:
					$result = $result . 'Depth error'; break;
				case JSON_ERROR_STATE_MISMATCH:
					$result = $result . 'Invalid JSON'; break;
				case JSON_ERROR_CTRL_CHAR:
					$result = $result . 'Control character'; break;
				case JSON_ERROR_SYNTAX:
					$result = $result . 'Syntax error'; break;
				case JSON_ERROR_UTF8:
					$result = $result . 'UTF-8 error'; break;
				default:
					$result = $result . 'Unknown error'; break;
			}
			return $result;
		}
		
		
		public function drawChart() {
			$rand = rand();
			$entr = array_rand($this->colors, $this->getSequenceCount());
			
			echo '	<canvas class="chart" id="chart' . $rand . '">
						Your browser does not support the HTML5 canvas tag.
					</canvas>
					<div class="value_output" id="c' . $rand . '"></div>';
					
			echo ' 	<script type="text/javascript">
						var sectorsarray = [];
						
						var canvasWidth = $("#chart' . $rand . '").css("width").replace(/[^-\d\.]/g, "");
						var canvasHeight = $("#chart' . $rand . '").css("height").replace(/[^-\d\.]/g, "");
						
						function drawChart' . $rand . '() {
							var c = document.getElementById("chart' . $rand . '");
			  				var ctx = c.getContext("2d");
							
							c.setAttribute("width", canvasWidth);
							c.setAttribute("height", canvasHeight);

 
							drawOutline();
							drawChart();
 							
				
							// This draws all the captions and
							// borders of the chart				
							function drawOutline() {
								
								// draw the caption of the graph
								ctx.fillStyle = "#FFF";
								ctx.font = "bold 20px Helvetica";
								ctx.fillText("' . $this->getGraphTitle() . '", 10, 25);
								
								// draw sequence titles
								var rightdistance = 10;
								';
								
			for ($i = $this->getSequenceCount()-1; $i >= 0; $i--) {
				echo '			ctx.fillStyle = "' . $this->colors[$entr[$i]] . '";
								ctx.font = "bold 15px Helvetica";
								ctx.textAlign = "end"; 
								ctx.fillText("' . $this->getSequenceTitle($i) . '", canvasWidth - rightdistance, 25);
								rightdistance += (ctx.measureText("' . $this->getSequenceTitle($i) . '").width + 10);
								';
			}
			
			
			if (($this->getGraphType() == "line") || ($this->getGraphType() == "bar")) { 
				echo '			// if line or bar: some reference lines are required
				
								// bottom line
								var bottomDistance = 25;
								
								ctx.strokeStyle = "#FFF";
								ctx.beginPath();
								ctx.moveTo(10, canvasHeight-bottomDistance);
								ctx.lineTo(canvasWidth-10,canvasHeight-bottomDistance);
								ctx.stroke();';
			};
			echo '			}
							';
			
			echo '			// This draws the chart
							function drawChart() {
							';
			
			// pie chart
			if ($this->getGraphType() == "pie") {
				echo '			var radius = Math.min(canvasHeight, canvasWidth)/3;
								var lineWidth = Math.min(canvasHeight, canvasWidth)/7;
								';
				$total = 0;
				// calculate total number
				for ($i = 0; $i < $this->getSequenceCount(); $i++) {
					$total += $this->getDatapoint($i, 0);
				}
				
				// print each value
				$angle = 0;
				$prevangle = 0;
				for ($i = 0; $i < $this->getSequenceCount(); $i++) {
					$prevangle = $angle;
					$angle += ($this->getDatapoint($i,0)*360/$total) * (pi()/180);
					echo '		ctx.beginPath();
								ctx.moveTo(canvasWidth/2,canvasHeight/2);
								ctx.arc(canvasWidth/2, canvasHeight/2, radius, ' . $prevangle . ', ' . $angle . ');
								ctx.lineTo(canvasWidth/2,canvasHeight/2);
								ctx.closePath();
								ctx.fillStyle = "' . $this->colors[$entr[$i]] .  '";
								ctx.fill();
								ctx.lineWidth = 0;
								ctx.strokeStyle = "rgba(0,0,0,0.1)";
								ctx.stroke();
								
								var x' . ($i+1) . ' = {
									start : ' . $prevangle . ',
									end : ' . $angle . ',
									name : "' . $this->getSequenceTitle($i) . '",
									details : "' . $this->getDatapoint($i,0) . '"
								};
								sectorsarray.push(x' . ($i+1) . ');
								';	
				}
				
				echo '			ctx.beginPath();
								ctx.arc(canvasWidth/2, canvasHeight/2, lineWidth, 0, 2 * Math.PI, false);
								ctx.closePath();
								ctx.clip();
								ctx.clearRect(canvasWidth/2 - lineWidth - 1, canvasHeight/2 - lineWidth - 1, lineWidth * 2 + 2, lineWidth * 2 + 2);
					  ';
			}
			
			echo '			}
						};
						
						
						function isInsideSector(point, center, radius, angle1, angle2) {
						  function areClockwise(center, radius, angle, point2) {
							var point1 = {
							  x : (center.x + radius) * Math.cos(angle),
							  y : (center.y + radius) * Math.sin(angle)
							};
							return -point1.x*point2.y + point1.y*point2.x > 0;
						  }
						
						  var relPoint = {
							x: point.x - center.x,
							y: point.y - center.y
						  };
						
						  return !areClockwise(center, radius, angle1, relPoint) &&
								 areClockwise(center, radius, angle2, relPoint) &&
								 (relPoint.x*relPoint.x + relPoint.y*relPoint.y <= radius * radius);
						}
												
						
						drawChart' . $rand . '();
						
						$("#chart' . $rand . '").parent().on( "resize", function( event, ui ) { drawChart' . $rand . '(); } );
						
					
						$("#chart' . $rand . '").mousemove(function (e) {
							var canvasOffset = $("#chart' . $rand . '").offset();
							var rect = document.getElementById("chart' . $rand . '").getBoundingClientRect();
							var p = { x: e.clientX - rect.left, y: e.clientY - rect.top };
							var c = { x: canvasWidth/2, y: canvasHeight/2 };
							var notPointed = true;
							for(var i in sectorsarray){								
								if (isInsideSector(p, c, (Math.min(canvasHeight, canvasWidth)/3), sectorsarray[i].start, sectorsarray[i].end)) {
									$("#c' . $rand . '").html(sectorsarray[i].name + ": " + sectorsarray[i].details);
									notPointed = false;
								} 
							}
							if (notPointed) {
								$("#c' . $rand . '").html("");
							}
						});
					</script>';
		}
	}
?>