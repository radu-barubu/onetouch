<?php

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<!DOCTYPE html>';
?>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>TODO supply a title</title>
		<link rel="stylesheet" type="text/css" href="https://speechanywhere.nuancehdp.com/1.2/css/Nuance.SpeechAnywhere.css?7c91b0770fd3bf802a033b6f88c3c134" />
		<script type="text/javascript" src="https://speechanywhere.nuancehdp.com/1.2/scripts/Nuance.SpeechAnywhere.js?1ad8bdebc7a64cc34fc97beb6abc8376"></script>
		<script language="javascript" type="text/javascript">
			function NUSA_configure() 
			{
				NUSA_userId = "onetouch";
				NUSA_enableAll = "true";

				if (!NUSAI_licenseGuid)
					NUSAI_licenseGuid = "6852c262-ba6d-466b-87d0-15672e79bb67";
				if (!NUSAI_partnerGuid)
					NUSAI_partnerGuid = "4a47ee29-553f-4576-82ef-eeffb3695efe";
				
				NUSA_applicationName = "Sample_ReinitializeVUIForm";
			
				// optional - if not set, the control will be inserted as first child of the BODY element
				NUSA_container = "divMain";
			
				// the Nuance SpeechAnywhere topic 
				NUSA_topic = NUSA_topicGeneralMedicine;
			}
		</script>		
	</head>
	<body>
		<div>TODO write content</div>
		<form action="" method="post">
			<textarea name="mytext" id="mytext" rows="3" cols="20"></textarea>
		</form>
			<div id="divMain" class="divMain" style="float:right">

			</div>		
	</body>
</html>
