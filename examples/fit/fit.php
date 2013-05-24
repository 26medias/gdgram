<?php

	require_once("../../gdgram.php");
	
	// New GDGram Instance
	$doc 		= new gdgram();
	
	// We load the image
	$logo 	= $doc->loadImage("../RESS/logo26.png");
	
	// We're going to scale and crop the picture to fit in a 400x200px rectangle, because it's too big
	$logo_resize		= $doc->fit($logo, 400,400, array(
		"scale"		=> false,
		"resize"	=> true
	));
	$logo_resizex		= $doc->fit($logo, 400,400, array(
		"scale"		=> false,
		"resize"	=> true,
		"resizex"	=> false
	));
	$logo_resizey		= $doc->fit($logo, 400,400, array(
		"scale"		=> false,
		"resize"	=> true,
		"resizey"	=> false
	));
	$logo_resizexy		= $doc->fit($logo, 400,400, array(
		"scale"		=> false,
		"resize"	=> true,
		"resizey"	=> false,
		"resizex"	=> false
	));
	
	// We export the generated image
	$doc->export($logo_resize, "logo_resize.png");
	$doc->export($logo_resizex, "logo_resizex.png");
	$doc->export($logo_resizey, "logo_resizey.png");
	$doc->export($logo_resizexy, "logo_resizexy.png");
	
?>

<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<title>GDGRAM</title>
	<meta charset="UTF-8">
</head>
<body style="background-color:	#ffffff">
	<img src="logo_resize.png?rnd=<?php echo rand(); ?>" style="border: 1px solid #000; padding: 0px;" />
	<img src="logo_resizex.png?rnd=<?php echo rand(); ?>" style="border: 1px solid #000; padding: 0px;" />
	<img src="logo_resizey.png?rnd=<?php echo rand(); ?>" style="border: 1px solid #000; padding: 0px;" />
	<img src="logo_resizexy.png?rnd=<?php echo rand(); ?>" style="border: 1px solid #000; padding: 0px;" />
</body>
</html>
