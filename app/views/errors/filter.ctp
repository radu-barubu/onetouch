<h2>Filtering Error</h2>
<p>There was a problem with the input sent to this page.</p>
<br />
<?php
if(isset($message)) {
	echo "Error Message: ";
	
	echo $message;
}
?>