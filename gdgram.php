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
			//return count($this->layers)-1;
			return array(
				"name"		=> $name,
				"ress"		=> $ress,
				"width"		=> $width,
				"height"	=> $height
			);
		}
		
		
		public function fill($ress, $rgba) {
			$color = imagecolorallocatealpha($ress["ress"], $rgba["r"], $rgba["g"], $rgba["b"], $rgba["a"]);
			imagefill($ress["ress"], 0, 0, $color);
		}
		
		public function replace($ress, $x, $y, $rgba_to) {
			$buffer		= $this->duplicate($ress);
			imagetruecolortopalette($buffer["ress"],false, 255);
			$color		= imagecolorat($buffer["ress"], $x, $y);
			imagecolorset($buffer["ress"], $color, $rgba_to["r"], $rgba_to["g"], $rgba_to["b"]);
			return $buffer;
		}
		
		public function transparentColor($ress, $rgba) {
			$buffer		= $this->duplicate($ress);
			imagetruecolortopalette($buffer["ress"],false, 255);
			$color = imagecolorallocatealpha($ress["ress"], $rgba["r"], $rgba["g"], $rgba["b"], $rgba["a"]);
			imagecolortransparent($buffer["ress"], $color);
			return $buffer;
		}
		
		public function transparent($ress, $x, $y) {
			$buffer		= $this->duplicate($ress);
			imagetruecolortopalette($buffer["ress"],false, 255);
			$color		= imagecolorat($buffer["ress"], $x, $y);
			imagecolortransparent($buffer["ress"], $color);
			return $buffer;
		}
		
		function opacity($ress, $opacity) {
			$buffer		= $this->duplicate($ress);
			$img 		= &$buffer["ress"];
			if( !isset( $opacity ) ){
				return false;
			}
			$opacity /= 100;
		
			//get image width and height
			$w = imagesx( $img );
			$h = imagesy( $img );
		
			//turn alpha blending off
			imagealphablending( $img, false );
		
			//find the most opaque pixel in the image (the one with the smallest alpha value)
			$minalpha = 127;
			for( $x = 0; $x < $w; $x++ ) {
				for( $y = 0; $y < $h; $y++ ) {
					$alpha = ( imagecolorat( $img, $x, $y ) >> 24 ) & 0xFF;
					if( $alpha < $minalpha )
					{ $minalpha = $alpha; }
				}
			}
			//loop through image pixels and modify alpha for each
			for( $x = 0; $x < $w; $x++ ) {
				for( $y = 0; $y < $h; $y++ ){
					//get current alpha value (represents the TANSPARENCY!)
					$colorxy = imagecolorat( $img, $x, $y );
					$alpha = ( $colorxy >> 24 ) & 0xFF;
					//calculate new alpha
					if( $minalpha !== 127 ){
						$alpha = 127 + 127 * $opacity * ( $alpha - 127 ) / ( 127 - $minalpha );
					}
					else{
						$alpha += 127 * $opacity;
					}
					//get the color index with new alpha
					$alphacolorxy = imagecolorallocatealpha( $img, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
					//set pixel with the new color + opacity
					if( !imagesetpixel( $img, $x, $y, $alphacolorxy ) ){
						return false;
					}
				}
			}
			return $buffer;
		}
		
		public function file_get($url) {
			if (strpos($url,"://")===false) {
				return file_get_contents($url);
			} else {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				$output = curl_exec($ch);
				curl_close($ch);
				return $output;
			}
		}
		
		public function loadString($str) {
			
		    $ress = imagecreatefromstring($str);
		    imagealphablending($ress, true);
			imagesavealpha($ress, true);
			
			return array(
				"ress"		=> $ress,
				"width"		=> imagesx($ress),
				"height"	=> imagesy($ress)
			);
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
			  default:
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
		
		public function fit($ress, $mw, $mh, $scale=true, $resize=false) {
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
				if (!$scale) {
					if ($nw > $ress["width"]) {
						$nw = $ress["width"];
					}
					if ($nh > $ress["height"]) {
						$nh = $ress["height"];
					}
				}
				$nx				= ($mw-$nw)/2;
				$ny				= 0;
			}
			// create the box
			if (!$resize) {
				$buffer 		= $this->createTransparentRessource($mw, $mh);
				imagealphablending($buffer, true);
				imagecopyresampled($buffer, $ress["ress"], $nx, $ny, 0, 0, $nw, $nh, $ress["width"], $ress["height"]);
				imagesavealpha($buffer,true);
				return array(
					"ress"		=> $buffer,
					"width"		=> $mw,
					"height"	=> $mh
				);
			} else {
				$buffer 		= $this->createTransparentRessource($nw, $nh);
				imagealphablending($buffer, true);
				imagecopyresampled($buffer, $ress["ress"], 0, 0, 0, 0, $nw, $nh, $ress["width"], $ress["height"]);
				imagesavealpha($buffer,true);
				return array(
					"ress"		=> $buffer,
					"width"		=> $nw,
					"height"	=> $nh
				);
			}
		}
		
		public function center($ress, $w, $h) {
			$buffer 	= $this->createTransparentRessource($w, $h);
			imagecopy($buffer, $ress["ress"], ($w - $ress["width"])/2, ($h - $ress["height"])/2, 0, 0, $ress["width"], $ress["height"]);
			return array(
				"ress"		=> $buffer,
				"width"		=> $w,
				"height"	=> $h
			);
		}
		
		public function duplicate($ress) {
			$buffer 	= $this->createTransparentRessource($ress["width"], $ress["height"]);
			imagealphablending($buffer, true);
			imagecopy($buffer, $ress["ress"], 0, 0, 0, 0, $ress["width"], $ress["height"]);
			imagesavealpha($buffer,true);
			return array(
				"ress"		=> $buffer,
				"width"		=> $ress["width"],
				"height"	=> $ress["height"]
			);
		}
		
		public function applyFilter($ress, $filterName, $options=array()) {
			// create a copy
			$buffer		= $this->duplicate($ress);
			switch ($filterName) {
				case "grayscale":
				imagefilter($buffer["ress"], IMG_FILTER_GRAYSCALE);
				break;
				case "brightness":
				imagefilter($buffer["ress"], IMG_FILTER_BRIGHTNESS, $options["level"]);
				break;
				case "contrast":
				imagefilter($buffer["ress"], IMG_FILTER_CONTRAST, $options["level"]);
				break;
				case "smooth":
				imagefilter($buffer["ress"], IMG_FILTER_SMOOTH, $options["level"]);
				break;
				case "colorize":
				imagefilter($buffer["ress"], IMG_FILTER_COLORIZE, $options["r"], $options["g"], $options["b"], $options["a"]);
				break;
				case "negative":
				imagefilter($buffer["ress"], IMG_FILTER_NEGATE);
				break;
				case "edge":
				imagefilter($buffer["ress"], IMG_FILTER_EDGEDETECT);
				break;
				case "emboss":
				imagefilter($buffer["ress"], IMG_FILTER_EMBOSS);
				break;
				case "blur":
				imagefilter($buffer["ress"], IMG_FILTER_GAUSSIAN_BLUR);
				break;
				case "sketch":
				imagefilter($buffer["ress"], IMG_FILTER_MEAN_REMOVAL);
				break;
				case "pixelate":
				imagefilter($buffer["ress"], IMG_FILTER_PIXELATE, $options["size"], $options["advanced"]);
				break;
			}
			return $buffer;
		}
		
		
		public function gradientRect($w, $h,$direction="circle",$startcolor="#FC98E3",$endcolor="#AA0682",$step=0) {
			$buffer 	= $this->createTransparentRessource($w, $h);
			
			
			$gradient	= new gd_gradient_fill($w,$h,$direction,$startcolor,$endcolor,$step);
			imagecopy($buffer, $gradient, 0, 0, 0, 0, $ress["width"], $ress["height"]);
			return array(
				"ress"		=> $buffer,
				"width"		=> $w,
				"height"	=> $h
			);
		}
		// From http://planetozh.com/blog/my-projects/images-php-gd-gradient-fill/
		// Direction: vertical, horizontal, ellipse, ellipse2, circle, circle2, rectangle, diamond
		function gradientFill($w, $h, $direction="circle",$start="FC98E3",$end="AA0682",$step=0) {
			$buffer 	= $this->createTransparentRessource($w, $h);
			$im 		= $buffer;
			switch($direction) {
				default:
				case 'horizontal':
				$line_numbers 		= imagesx($im);
				$line_width 		= imagesy($im);
				list($r1,$g1,$b1) 	= $this->hex2rgb($start);
				list($r2,$g2,$b2) 	= $this->hex2rgb($end);
				break;
				case 'vertical':
				$line_numbers 		= imagesy($im);
				$line_width 		= imagesx($im);
				list($r1,$g1,$b1) 	= $this->hex2rgb($start);
				list($r2,$g2,$b2) 	= $this->hex2rgb($end);
				break;
				case 'ellipse':
				$width 				= imagesx($im);
				$height 			= imagesy($im);
				$rh					= $height>$width?1:$width/$height;
				$rw					= $width>$height?1:$height/$width;
				$line_numbers 		= min($width,$height);
				$center_x 			= $width/2;
				$center_y 			= $height/2;
				list($r1,$g1,$b1) 	= $this->hex2rgb($end);
				list($r2,$g2,$b2) 	= $this->hex2rgb($start);
				imagefill($im, 0, 0, imagecolorallocate( $im, $r1, $g1, $b1 ));
				break;
				case 'ellipse2':
				$width 				= imagesx($im);
				$height 			= imagesy($im);
				$rh					= $height>$width?1:$width/$height;
				$rw					= $width>$height?1:$height/$width;
				$line_numbers 		= sqrt(pow($width,2)+pow($height,2));
				$center_x 			= $width/2;
				$center_y 			= $height/2;
				list($r1,$g1,$b1) 	= $this->hex2rgb($end);
				list($r2,$g2,$b2) 	= $this->hex2rgb($start);
				break;
				case 'circle':
				$width 				= imagesx($im);
				$height 			= imagesy($im);
				$line_numbers 		= sqrt(pow($width,2)+pow($height,2));
				$center_x 			= $width/2;
				$center_y 			= $height/2;
				$rh = $rw 			= 1;
				list($r1,$g1,$b1) 	= $this->hex2rgb($end);
				list($r2,$g2,$b2) 	= $this->hex2rgb($start);
				break;
				case 'circle2':
				$width 				= imagesx($im);
				$height 			= imagesy($im);
				$line_numbers 		= min($width,$height);
				$center_x 			= $width/2;
				$center_y 			= $height/2;
				$rh = $rw 			= 1;
				list($r1,$g1,$b1) 	= $this->hex2rgb($end);
				list($r2,$g2,$b2) 	= $this->hex2rgb($start);
				imagefill($im, 0, 0, imagecolorallocate( $im, $r1, $g1, $b1 ));
				break;
				case 'square':
				case 'rectangle':
				$width 				= imagesx($im);
				$height 			= imagesy($im);
				$line_numbers 		= max($width,$height)/2;
				list($r1,$g1,$b1) 	= $this->hex2rgb($end);
				list($r2,$g2,$b2) 	= $this->hex2rgb($start);
				break;
				case 'diamond':
				list($r1,$g1,$b1) 	= $this->hex2rgb($end);
				list($r2,$g2,$b2) 	= $this->hex2rgb($start);
				$width 				= imagesx($im);
				$height 			= imagesy($im);
				$rh					= $height>$width?1:$width/$height;
				$rw					= $width>$height?1:$height/$width;
				$line_numbers 		= min($width,$height);
				break;
			}
			for ( $i = 0; $i < $line_numbers; $i=$i+1+$step ) {
				// old values :
				$old_r		= $r;
				$old_g		= $g;
				$old_b		= $b;
				// new values :
				$r 			= ( $r2 - $r1 != 0 ) ? intval( $r1 + ( $r2 - $r1 ) * ( $i / $line_numbers ) ): $r1;
				$g 			= ( $g2 - $g1 != 0 ) ? intval( $g1 + ( $g2 - $g1 ) * ( $i / $line_numbers ) ): $g1;
				$b 			= ( $b2 - $b1 != 0 ) ? intval( $b1 + ( $b2 - $b1 ) * ( $i / $line_numbers ) ): $b1;
				// if new values are really new ones, allocate a new color, otherwise reuse previous color.
				// There's a "feature" in imagecolorallocate that makes this function
				// always returns '-1' after 255 colors have been allocated in an image that was created with
				// imagecreate (everything works fine with imagecreatetruecolor)
				if ( "$old_r,$old_g,$old_b" != "$r,$g,$b") {
					$fill = imagecolorallocate( $im, $r, $g, $b );
				}
				switch($direction) {
					case 'vertical':
					imagefilledrectangle($im, 0, $i, $line_width, $i+$step, $fill);
					break;
					case 'horizontal':
					imagefilledrectangle( $im, $i, 0, $i+$step, $line_width, $fill );
					break;
					case 'ellipse':
					case 'ellipse2':
					case 'circle':
					case 'circle2':
					imagefilledellipse ($im,$center_x, $center_y, ($line_numbers-$i)*$rh, ($line_numbers-$i)*$rw,$fill);
					break;
					case 'square':
					case 'rectangle':
					imagefilledrectangle ($im,$i*$width/$height,$i*$height/$width,$width-($i*$width/$height), $height-($i*$height/$width),$fill);
					break;
					case 'diamond':
					imagefilledpolygon(
						$im, 
						array (
							$width/2,
							$i*$rw-0.5*$height,
							$i*$rh-0.5*$width,
							$height/2,
							$width/2,
							1.5*$height-$i*$rw,
							1.5*$width-$i*$rh,
							$height/2
						),
						4, 
						$fill
					);
					break;
					default:
				}
			}
			return array(
				"ress"		=> $buffer,
				"width"		=> imagesx($buffer),
				"height"	=> imagesy($buffer)
			);
		}
	
		// #ff00ff -> array(255,0,255) or #f0f -> array(255,0,255)
		// From http://planetozh.com/blog/my-projects/images-php-gd-gradient-fill/
		function hex2rgb($color) {
			$color = str_replace('#','',$color);
			$s = strlen($color) / 3;
			$rgb[]=hexdec(str_repeat(substr($color,0,$s),2/$s));
			$rgb[]=hexdec(str_repeat(substr($color,$s,$s),2/$s));
			$rgb[]=hexdec(str_repeat(substr($color,2*$s,$s),2/$s));
			return $rgb;
		}
		
		public function generateQRCode($url, $width, $height, $margin=2, $eclevel='L') {
			$ggurl = "http://chart.apis.google.com/chart?chs=".$width."x".$height."&cht=qr&chld=".$eclevel."|".$margin."&chl=".urlencode($url);
			//$ress = $this->createTransparentRessource($width, $height);
			$ress = $this->loadString($this->file_get($ggurl));
			return $ress;
		}
		
		public function copy($ress, $layer, $x=0, $y=0) {
			imagealphablending($layer["ress"], true);
			imagecopy($layer["ress"], $ress["ress"], $x,$y,0,0,$ress["width"],$ress["height"]);
			imagesavealpha($layer["ress"],true);
		}
		
		private function createTransparentRessource($width=false, $height=false) {
			$ress = imagecreatetruecolor($width, $height);
			imagealphablending($ress, true);
			imagesavealpha($ress,true);
			$col = imagecolorallocatealpha($ress,255,255,255,127);
			imagefill($ress, 0, 0, $col);
			return $ress;
		}
		
		public function raster($filename=false) {
			$raster = $this->createTransparentRessource($this->canvas["width"], $this->canvas["height"]);
			foreach ($this->layers as $layer) {
				$coord = array();
				$coord["x"] 	= ($this->canvas["width"]-$layer["width"])/2;
				$coord["y"] 	= ($this->canvas["height"]-$layer["height"])/2;
				imagealphablending($raster, true);
				imagecopy($raster, $layer["ress"], $coord["x"],$coord["y"],0,0,$layer["width"],$layer["height"]);
				imagesavealpha($raster,true);
			}
			if ($filename) {
				imagepng($raster, $filename, 9, PNG_ALL_FILTERS);
			}
			return array(
				"ress"		=> $raster,
				"width"		=> $this->canvas["width"],
				"height"	=> $this->canvas["height"]
			);
		}
		
		
		public function erase($layer) {
			$layer["ress"] = $this->createTransparentRessource($layer["width"], $layer["height"]);
			return $layer;
		}
		
		public function eraseAll() {
			foreach ($this->layers as $layerID => $layer) {
				$this->layers[$layerID]["ress"] = $this->createTransparentRessource($this->layers[$layerID]["width"], $this->layers[$layerID]["height"]);
			}
		}
		
		public function export($ress, $filename) {
			imagepng($ress["ress"], $filename, 9, PNG_ALL_FILTERS);
		}
	}
	
	
	
	/*
	Script Name: GD Gradient Fill
	Script URI: http://planetozh.com/blog/my-projects/images-php-gd-gradient-fill/
	Description: Creates a gradient fill of any shape (rectangle, ellipse, vertical, horizontal, diamond)
	Author: Ozh
	Version: 1.1
	Author URI: http://planetozh.com/
	*/
	
	/* Release history :
	* 1.1
	*        - changed : more nicely packaged as a class
	*        - fixed : not displaying proper gradient colors with image dimension greater than 255 (because of a limitation in imagecolorallocate)
	*        - added : optional parameter 'step', more options for 'direction'
	* 1.0
	*        - initial release
	*/
	
	/* Usage :
	*
	* require_once('/path/to/gd-gradient-fill.php');
	* $image = new gd_gradient_fill($width,$height,$direction,$startcolor,$endcolor,$step);
	*
	* Parameters :
	*        - width and height : integers, dimesions of your image.
	*        - direction : string, shape of the gradient.
	*          Can be : vertical, horizontal, rectangle (or square), ellipse, ellipse2, circle, circle2, diamond.
	*        - startcolor : string, start color in 3 or 6 digits hexadecimal.
	*        - endcolor : string, end color in 3 or 6 digits hexadecimal.
	*        - step : integer, optional, default to 0. Step that breaks the smooth blending effect.
	* Returns a resource identifier.
	*
	* Examples :
	*
	* 1.
	* require_once('/home/ozh/www/includes/gd-gradient-fill.php');
	* $image = new gd_gradient_fill(200,200,'horizontal','#fff','#f00');
	*
	* 2.
	* require_once('c:/iis/inet/include/gd-gradient-fill.php');
	* $myimg = new gd_gradient_fill(80,20,'diamond','#ff0010','#303060');
	*
	*/
	
	
	// Test it :
	// $image = new gd_gradient_fill(400,200,'ellipse','#f00','#000',0);
	
	class gd_gradient_fill {
	
		// Constructor. Creates, fills and returns an image
		function gd_gradient_fill($w,$h,$d,$s,$e,$step=0) {
			$this->width = $w;
			$this->height = $h;
			$this->direction = $d;
			$this->startcolor = $s;
			$this->endcolor = $e;
			$this->step = intval(abs($step));
	
			// Attempt to create a blank image in true colors, or a new palette based image if this fails
			if (function_exists('imagecreatetruecolor')) {
				$this->image = imagecreatetruecolor($this->width,$this->height);
			} elseif (function_exists('imagecreate')) {
				$this->image = imagecreate($this->width,$this->height);
			} else {
				die('Unable to create an image');
			}
	
			// Fill it
			$this->fill($this->image,$this->direction,$this->startcolor,$this->endcolor);
	
			// Show it
			//$this->display($this->image);
	
			// Return it
			return $this->image;
		}
	
	
		// Displays the image with a portable function that works with any file type
		// depending on your server software configuration
		function display ($im) {
			if (function_exists("imagepng")) {
				//header("Content-type: image/png");
				imagepng($im);
			}
			elseif (function_exists("imagegif")) {
				//header("Content-type: image/gif");
				imagegif($im);
			}
			elseif (function_exists("imagejpeg")) {
				//header("Content-type: image/jpeg");
				imagejpeg($im, "", 0.5);
			}
			elseif (function_exists("imagewbmp")) {
				//header("Content-type: image/vnd.wap.wbmp");
				imagewbmp($im);
			} else {
				die("Doh ! No graphical functions on this server ?");
			}
			return true;
		}
	
	
		// The main function that draws the gradient
		function fill($im,$direction,$start,$end) {
	
			switch($direction) {
				case 'horizontal':
				$line_numbers = imagesx($im);
				$line_width = imagesy($im);
				list($r1,$g1,$b1) = $this->hex2rgb($start);
				list($r2,$g2,$b2) = $this->hex2rgb($end);
				break;
				case 'vertical':
				$line_numbers = imagesy($im);
				$line_width = imagesx($im);
				list($r1,$g1,$b1) = $this->hex2rgb($start);
				list($r2,$g2,$b2) = $this->hex2rgb($end);
				break;
				case 'ellipse':
				$width = imagesx($im);
				$height = imagesy($im);
				$rh=$height>$width?1:$width/$height;
				$rw=$width>$height?1:$height/$width;
				$line_numbers = min($width,$height);
				$center_x = $width/2;
				$center_y = $height/2;
				list($r1,$g1,$b1) = $this->hex2rgb($end);
				list($r2,$g2,$b2) = $this->hex2rgb($start);
				imagefill($im, 0, 0, imagecolorallocate( $im, $r1, $g1, $b1 ));
				break;
				case 'ellipse2':
				$width = imagesx($im);
				$height = imagesy($im);
				$rh=$height>$width?1:$width/$height;
				$rw=$width>$height?1:$height/$width;
				$line_numbers = sqrt(pow($width,2)+pow($height,2));
				$center_x = $width/2;
				$center_y = $height/2;
				list($r1,$g1,$b1) = $this->hex2rgb($end);
				list($r2,$g2,$b2) = $this->hex2rgb($start);
				break;
				case 'circle':
				$width = imagesx($im);
				$height = imagesy($im);
				$line_numbers = sqrt(pow($width,2)+pow($height,2));
				$center_x = $width/2;
				$center_y = $height/2;
				$rh = $rw = 1;
				list($r1,$g1,$b1) = $this->hex2rgb($end);
				list($r2,$g2,$b2) = $this->hex2rgb($start);
				break;
				case 'circle2':
				$width = imagesx($im);
				$height = imagesy($im);
				$line_numbers = min($width,$height);
				$center_x = $width/2;
				$center_y = $height/2;
				$rh = $rw = 1;
				list($r1,$g1,$b1) = $this->hex2rgb($end);
				list($r2,$g2,$b2) = $this->hex2rgb($start);
				imagefill($im, 0, 0, imagecolorallocate( $im, $r1, $g1, $b1 ));
				break;
				case 'square':
				case 'rectangle':
				$width = imagesx($im);
				$height = imagesy($im);
				$line_numbers = max($width,$height)/2;
				list($r1,$g1,$b1) = $this->hex2rgb($end);
				list($r2,$g2,$b2) = $this->hex2rgb($start);
				break;
				case 'diamond':
				list($r1,$g1,$b1) = $this->hex2rgb($end);
				list($r2,$g2,$b2) = $this->hex2rgb($start);
				$width = imagesx($im);
				$height = imagesy($im);
				$rh=$height>$width?1:$width/$height;
				$rw=$width>$height?1:$height/$width;
				$line_numbers = min($width,$height);
				break;
				default:
			}
	
			for ( $i = 0; $i < $line_numbers; $i=$i+1+$this->step ) {
				// old values :
				$old_r=$r;
				$old_g=$g;
				$old_b=$b;
				// new values :
				$r = ( $r2 - $r1 != 0 ) ? intval( $r1 + ( $r2 - $r1 ) * ( $i / $line_numbers ) ): $r1;
				$g = ( $g2 - $g1 != 0 ) ? intval( $g1 + ( $g2 - $g1 ) * ( $i / $line_numbers ) ): $g1;
				$b = ( $b2 - $b1 != 0 ) ? intval( $b1 + ( $b2 - $b1 ) * ( $i / $line_numbers ) ): $b1;
				// if new values are really new ones, allocate a new color, otherwise reuse previous color.
				// There's a "feature" in imagecolorallocate that makes this function
				// always returns '-1' after 255 colors have been allocated in an image that was created with
				// imagecreate (everything works fine with imagecreatetruecolor)
				if ( "$old_r,$old_g,$old_b" != "$r,$g,$b")
				$fill = imagecolorallocate( $im, $r, $g, $b );
				switch($direction) {
					case 'vertical':
					imagefilledrectangle($im, 0, $i, $line_width, $i+$this->step, $fill);
					break;
					case 'horizontal':
					imagefilledrectangle( $im, $i, 0, $i+$this->step, $line_width, $fill );
					break;
					case 'ellipse':
					case 'ellipse2':
					case 'circle':
					case 'circle2':
					imagefilledellipse ($im,$center_x, $center_y, ($line_numbers-$i)*$rh, ($line_numbers-$i)*$rw,$fill);
					break;
					case 'square':
					case 'rectangle':
					imagefilledrectangle ($im,$i*$width/$height,$i*$height/$width,$width-($i*$width/$height), $height-($i*$height/$width),$fill);
					break;
					case 'diamond':
					imagefilledpolygon($im, array (
					$width/2, $i*$rw-0.5*$height,
					$i*$rh-0.5*$width, $height/2,
					$width/2,1.5*$height-$i*$rw,
					1.5*$width-$i*$rh, $height/2 ), 4, $fill);
					break;
					default:
				}
			}
		}
	
		// #ff00ff -> array(255,0,255) or #f0f -> array(255,0,255)
		function hex2rgb($color) {
			$color = str_replace('#','',$color);
			$s = strlen($color) / 3;
			$rgb[]=hexdec(str_repeat(substr($color,0,$s),2/$s));
			$rgb[]=hexdec(str_repeat(substr($color,$s,$s),2/$s));
			$rgb[]=hexdec(str_repeat(substr($color,2*$s,$s),2/$s));
			return $rgb;
		}
	}
?>
