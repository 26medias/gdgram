<?php
	//error_reporting(0);
	require_once("gdgram.php");
	
	if (!isset($_POST["url"])) {
		$_POST["url"] = "http://www.youtube.com/watch?v=tCnBZi2URTE";
	}
	
	if (isset($_POST["url"])) {
		$doc 		= new gdgram();
		$doc->canvasSize(200,200);
		
		$layerroot	= $doc->newLayer("root");
		$layerbg	= $doc->newLayer("bg");
		$layer		= $doc->newLayer("main");
		$qr 		= $doc->generateQRCode($_POST["url"], 200, 200, 1);
		
		
		switch ($_POST["effect"]) {
			case "green":
			$qr = $doc->applyFilter($qr, "colorize", array(
				"r"	=> 160,
				"g"	=> 210,
				"b"	=> 9,
				"a"	=> 0
			));
			$qr = $doc->applyFilter($qr, "sketch");
			break;
			case "black":
			$qr = $doc->applyFilter($qr, "colorize", array(
				"r"	=> 50,
				"g"	=> 50,
				"b"	=> 50,
				"a"	=> 0
			));
			$qr = $doc->applyFilter($qr, "sketch");
			$qr = $doc->applyFilter($qr, "sketch");
			$qr = $doc->applyFilter($qr, "sketch");
			break;
			case "logo":
			$doc->fill($layerroot, array(
				"r"	=> 255,
				"g"	=> 255,
				"b"	=> 255,
				"a"	=> 0
			));
			$qr = $doc->applyFilter($qr, "colorize", array(
				"r"	=> 160,
				"g"	=> 210,
				"b"	=> 9,
				"a"	=> 70
			));
			$qr = $doc->applyFilter($qr, "sketch");
			$qr 	= $doc->transparent($qr, 1, 1);
			$qr		= $doc->opacity($qr, 70);
			
			$logo	= $doc->loadImage("images/php.png");
			$logo	= $doc->opacity($logo, 50);
			$logo	= $doc->fit($logo, 100, 100);
			$doc->copy($logo, $layerbg, 50, 50);
			break;
			case "alpha":
			$qr 	= $doc->transparent($qr, 1, 1);
			break;
		}
		
		$doc->copy($qr, $layer, 0, 0);
		
		$doc->raster("qr.png");
	}
	
?>

<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<title>GDGRAM</title>
	<meta charset="UTF-8">
</head>
<body style="background-color:	#5c6e7f">
	<form action="" method="post">
		<label for="url">URL</label>
		<input type="text" name="url" id="url" style="width: 200px;" value="<?php echo $_POST["url"]; ?>" />
		<select name="effect" id="effect">
			<option value="simple">Simple</option>
			<option value="green">Green Blocks</option>
			<option value="black">Black Lines</option>
			<option value="logo">Logo</option>
			<option value="alpha">Transparent</option>
		</select>
		<input type="submit" value="Generate" />
	</form>
	<img src="qr.png" style="border: 1px solid #000; padding: 5px;" />
</body>
</html>
