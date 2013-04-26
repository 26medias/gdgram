<?php

	require_once("gdgram.php");
	
	$clicks = array(array("x"=>"798","y"=>"53"),array("x"=>"839","y"=>"74"),array("x"=>"737","y"=>"100"),array("x"=>"880","y"=>"39"),array("x"=>"198","y"=>"45"),array("x"=>"-58","y"=>"338"),array("x"=>"-76","y"=>"258"),array("x"=>"532","y"=>"230"),array("x"=>"389","y"=>"308"),array("x"=>"895","y"=>"45"),array("x"=>"811","y"=>"101"),array("x"=>"872","y"=>"214"),array("x"=>"897.5","y"=>"36"),array("x"=>"905.5","y"=>"45"),array("x"=>"207.5","y"=>"45"),array("x"=>"225.5","y"=>"50"));
	
	
	$s = array(600, 600);
	
	$clicks = array();
	$n = 200;
	for ($i=0;$i<$n;$i++) {
		array_push($clicks, array(round(rand(0,$s[0])),round(rand(0,$s[1]))));
	}
	
	$doc 		= new gdgram();
	$doc->canvasSize($s[0],$s[1]);
	
	// create layers
	$layer_bg 		= $doc->newLayer("background");
	
	$heatmap 		= $doc->heatmap($clicks, $s[0], $s[1], 10, 100);
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
