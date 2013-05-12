<?php

	require_once("../../gdgram.php");
	
	// Size of the image
	$s = array(600, 600);
	
	// We generate random clicks
	$clicks = array();
	$n = 200;
	for ($i=0;$i<$n;$i++) {
		array_push($clicks, array(round(rand(0,$s[0])),round(rand(0,$s[1]))));
	}
	
	// New GDGram Instance
	$doc 		= new gdgram();
	
	// Setup the size of the work area. In this example, we won't use the canvas, so this is not required.
	$doc->canvasSize($s[0],$s[1]);
	
	// heatmap() generate a new image
	$heatmap 		= $doc->heatmap($clicks, $s[0], $s[1], 10, 100);
	
	// We export the generated image
	$doc->export($heatmap, "heatmap.png");
	
?>

<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<title>GDGRAM</title>
	<meta charset="UTF-8">
</head>
<body style="background-color:	#ffffff">
	<img src="heatmap.png?rnd=<?php echo rand(); ?>" style="border: 1px solid #000; padding: 0px;" />
</body>
</html>
