<?php
	function debug($label, $data) {
		echo "<div style=\"margin-left: 40px;\"><u><h3>".$label."</h3></u><pre style=\"border-left:2px solid #000000;margin:10px;padding:4px;\">".print_r($data, true)."</pre></div>";
	}
	
	class gdgram {
		public function __construct() {
			$this->layers 		= array();
			$this->canvas		= array(
				"width"		=> 200,
				"height"	=> 200
			);
			$this->exportFormat	= "PNG";
		}
		public function canvasSize($w,$h) {
			$this->canvas["width"] 	= $w;
			$this->canvas["height"] = $h;
		}
		
		public function newLayer($name, $width=false, $height=false) {
			if (!$width) { 	$width 	=  $this->canvas["width"]; }
			if (!$height) { $height =  $this->canvas["height"]; }
			$ress = $this->createTransparentRessource($width, $height);
			array_push($this->layers, array(
				"name"		=> $name,
				"ress"		=> $ress,
				"width"		=> $width,
				"height"	=> $height
			));
			return count($this->layers)-1;
		}
		
		public function loadImage($filename) {
			$info = pathinfo($filename);
			switch($info["extension"]) {
			  case "jpg":
			  case "jpeg":
			    $ress = imagecreatefromjpeg($filename);
			    break;
			  case "gif":
			    $ress = imagecreatefromgif($filename);
			    break;
			  case "png":
			    $ress = imagecreatefrompng($filename);
			    imagealphablending($ress, true);
				imagesavealpha($ress, true);
			    break;
			}
			list($width, $height, $type, $attr) = getimagesize($filename);
			
			return array(
				"ress"		=> $ress,
				"width"		=> $width,
				"height"	=> $height
			);
		}
		
		public function fit($ress, $mw, $mh) {
			$ress_ratio 	= $ress["width"] / $ress["height"];
			$box_ratio		= $mw / $mh;
			$diff = array(
				"width"			=> $ress["width"] / $mw,
				"height"		=> $ress["height"] / $mh
			);
			$diff_ratio		= $diff["width"] / $diff["height"];
			
			if ($ress_ratio > $box_ratio) {
				$scale_ratio	= $ress["width"] / $mw;
				$nw				= $mw;
				$nh				= $ress["height"] / $scale_ratio;
				$nx				= 0;
				$ny				= ($mh-$nh)/2;
			} else {
				$scale_ratio	= $ress["height"] / $mh;
				$nw				= $ress["width"] / $scale_ratio;
				$nh				= $mh;
				$nx				= ($mw-$nw)/2;
				$ny				= 0;
			}
			// create the box
			$buffer 		= $this->createTransparentRessource($mw, $mh);
			imagealphablending($buffer, true);
			imagecopyresampled($buffer, $ress["ress"], $nx, $ny, 0, 0, $nw, $nh, $ress["width"], $ress["height"]);
			imagesavealpha($buffer,true);
			return array(
				"ress"		=> $buffer,
				"width"		=> $mw,
				"height"	=> $mh
			);
		}
		
		public function copy($ress, $layerID, $x=0, $y=0) {
			imagealphablending($this->layers[$layerID]["ress"], true);
			imagecopy($this->layers[$layerID]["ress"], $ress["ress"], $x,$y,0,0,$ress["width"],$ress["height"]);
			imagesavealpha($this->layers[$layerID]["ress"],true);
		}
		
		private function createTransparentRessource($width=false, $height=false) {
			$ress = imagecreatetruecolor($width, $height);
			imagealphablending($ress, true);
			imagesavealpha($ress,true);
			$col = imagecolorallocatealpha($ress,255,255,255,127);
			imagefill($ress, 0, 0, $col);
			return $ress;
		}
		
		public function raster($filename) {
			$raster = $this->createTransparentRessource($this->canvas["width"], $this->canvas["height"]);
			foreach ($this->layers as $layer) {
				$coord = array();
				$coord["x"] 	= ($this->canvas["width"]-$layer["width"])/2;
				$coord["y"] 	= ($this->canvas["height"]-$layer["height"])/2;
				imagealphablending($raster, true);
				imagecopy($raster, $layer["ress"], $coord["x"],$coord["y"],0,0,$layer["width"],$layer["height"]);
				imagesavealpha($raster,true);
			}
			return imagepng($raster, $filename, 9, PNG_ALL_FILTERS);
		}
	}
?>
