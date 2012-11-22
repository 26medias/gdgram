<?php

	require_once("gdgram.php");
	
	$s = array(200, 400);
	
	$doc 		= new gdgram();
	$doc->canvasSize($s[0],$s[1]);
	
	// create layers
	$layer_bg 		= $doc->newLayer("background");
	
	$landcape 		= $doc->loadImage("images/landscape.jpg");
	$landscape_t	= $doc->cropfit($landcape, $s[0],$s[1]);
	$portrait 		= $doc->loadImage("images/portrait.jpg");
	$portrait_t		= $doc->cropfit($portrait, $s[0],$s[1]);
	
	$doc->export($landscape_t, "__landcape.png");
	$doc->export($portrait_t, "__portrait.png");
	
	//$doc->copy($thumb, $layer_bg);
	
	//$doc->raster("__prometheus.png");
?>

<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<title>GDGRAM</title>
	<meta charset="UTF-8">
</head>
<body style="background-color:	#5c6e7f">
	<img src="__landcape.png?rnd=<?php echo rand(); ?>" style="border: 1px solid #000; padding: 0px;" />
	<img src="__portrait.png?rnd=<?php echo rand(); ?>" style="border: 1px solid #000; padding: 0px;" />
</body>
</html>
