<?php

	require_once("gdgram.php");
	
	$doc 	= new gdgram();
	$doc->canvasSize(500,500);
	$bg 	= $doc->newLayer("background");
	
	$imgs 	= $doc->newLayer("thumbs");
	
	$bgimg 	= $doc->loadImage("images/Jellyfish.jpg");
	$bgthumb= $doc->fit($bgimg, 500, 500);
	$doc->copy($bgthumb, $bg);
	
	$logo 	= $doc->loadImage("images/logo26.png");
	$doc->copy($logo, $bg, 100, 100);
	$doc->copy($logo, $bg, 200, 200);
	$doc->copy($logo, $bg, 220, 220);
	
	$thumb 	= $doc->fit($logo, 500, 20);
	$doc->copy($thumb, $imgs, 0, 0);
	
	$thumb2 = $doc->fit($logo, 20, 500);
	$doc->copy($thumb2, $imgs, 0, 0);
	
	$qrcode = $doc->loadImage("images/qr.png");
	$qrcode 	= $doc->fit($qrcode, 100, 100);
	$doc->copy($qrcode, $imgs, 500-$qrcode["width"], 500-$qrcode["height"]);
	
	
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
</body>
</html>
