<?php

	require_once("gdgram.php");
	
	$doc 		= new gdgram();
	$doc->canvasSize(500,500);
	
	// create layers
	$layer_bg 		= $doc->newLayer("background");
	$layer_imgs 	= $doc->newLayer("thumbs");
	$layer_filters 	= $doc->newLayer("filters");
	
	
	$doc->fill($layer_imgs, array(
		"r"	=> 160,
		"g"	=> 210,
		"b"	=> 9,
		"a"	=> 50
	));
	
	$jellyfish 	= $doc->loadImage("images/Jellyfish.jpg");
	$logo 		= $doc->loadImage("images/logo26.png");
	$qrcode 	= $doc->loadImage("images/qr.png");
	
	$jellythumb	= $doc->fit($jellyfish, 100, 100);
	$doc->copy($jellythumb, $layer_bg);
	$doc->copy($doc->applyFilter($jellythumb, "brightness", array("level"=>20)), $layer_bg,100,0);
	$doc->copy($doc->applyFilter($jellythumb, "brightness", array("level"=>50)), $layer_bg,200,0);
	$doc->copy($doc->applyFilter($jellythumb, "brightness", array("level"=>-20)), $layer_bg,300,0);
	$doc->copy($doc->applyFilter($jellythumb, "grayscale"), $layer_bg,400,0);
	$doc->copy($doc->applyFilter($jellythumb, "colorize", array(
		"r"	=> 160,
		"g"	=> 210,
		"b"	=> 9,
		"a"	=> 50
	)), $layer_bg,0,100);
	$doc->copy($doc->applyFilter($jellythumb, "negative"), $layer_bg,100,100);
	$doc->copy($doc->applyFilter($jellythumb, "pixelate", array("size"=>10,"advanced"=>false)), $layer_bg,200,100);
	$doc->copy($doc->applyFilter($jellythumb, "pixelate", array("size"=>10,"advanced"=>true)), $layer_bg,300,100);
	$doc->copy($doc->applyFilter($jellythumb, "contrast", array("level"=>-80)), $layer_bg,400,100);
	$doc->copy($doc->applyFilter($jellythumb, "edge"), $layer_bg,0,200);
	$doc->copy($doc->applyFilter($jellythumb, "emboss"), $layer_bg,100,200);
	$doc->copy($doc->applyFilter($jellythumb, "blur"), $layer_bg,200,200);
	$doc->copy($doc->applyFilter($jellythumb, "sketch"), $layer_bg,300,200);
	$doc->copy($doc->applyFilter($jellythumb, "smooth", array("level"=>5)), $layer_bg,400,200);
	
	
	$logothumb 	= $doc->fit($logo, 500, 200);
	$doc->copy($logothumb, $layer_imgs, 0, 300);
	
	$logothumb2 = $doc->fit($logo, 500, 200, false);
	$doc->copy($logothumb2, $layer_imgs, 0, 200);
	
	$logothumb3	= $doc->fit($logo, 50, 500);
	$doc->copy($logothumb3, $layer_imgs, 0, 0);
	
	$qrcode 	= $doc->fit($qrcode, 100, 100);
	$doc->copy($qrcode, $layer_imgs, 500-$qrcode["width"], 500-$qrcode["height"]);
	
	$small01	= $doc->fit($logo, 50,50, false, false);
	$doc->export($small01, "small01.png");
	$small02	= $doc->fit($logo, 50,50, false, true);
	$doc->export($small02, "small02.png");
	
	$doc->raster("test.png");
?>

<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<title>GDGRAM</title>
	<meta charset="UTF-8">
</head>
<body style="background-color:	#5c6e7f">
	<img src="test.png" style="border: 1px solid #000; padding: 5px;" />
	<img src="small01.png" style="border: 1px solid #000; padding: 5px;" />
	<img src="small02.png" style="border: 1px solid #000; padding: 5px;" />
</body>
</html>
