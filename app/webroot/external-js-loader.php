<?php
	$file_url = $_GET['url'];
	
	$content = file_get_contents($file_url);
	
	$pos = strrpos($content, '}') + 1;
	
	$content = substr($content, 0, $pos);
	
	header("content-type: application/x-javascript");
	
	echo $content;
?>