<html>
<head>
</head>
<body onLoad="Initialisation();">
<script language="JavaScript">
<!--

  function Initialisation()
  { 
	if(typeof wtx==='undefined')
        return false;
	// Edit this line to provide URL of upload script on your server	
    wtx.uploadurl = parent.uploadurl;
	wtx.FormTagName = parent.FormTagName;
	wtx.AddFormVar('data[path_index]', parent.path_index);
	//wtx.filename = "test.pdf";
  }

  function FinishUpload()
  {
    Initialisation();
	if (wtx.uploadstatus == 200)
	{
	  //alert("Upload Succeeded");
	  parent.scanFinishUpload(wtx.UploadReturnString);
	}
	else if (wtx.uploadstatus == 1)
	{
	  //alert("Upload aborted by user");
	}
	else
	{
	  //alert("Upload failed");
	}
  }

//-->
</script>

<OBJECT classid="clsid:5220cb21-c88d-11cf-b347-00aa00a28331"><PARAM NAME="LPKPath" VALUE="webtwainx.lpk"></OBJECT>
<OBJECT id="wtx" classid="CLSID:E1DDE407-68F1-4E89-B080-EDF751B64843" codebase="WebTwainX.cab">
failed to load</OBJECT><br>

<script language="JavaScript" for="wtx" event="onFinishUpload()">
  FinishUpload();
</script>

</body>
</html>
