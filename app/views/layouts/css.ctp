<?php

header('content-type:text/css');
header("Expires: ".gmdate("D, d M Y H:i:s", (time()+1)) . " GMT"); 

echo $content_for_layout;

?>