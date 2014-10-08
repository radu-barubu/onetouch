<?php $Ttime=time();?>

<div style="overflow: hidden;">
  <h2>Administration</h2>
    <?php echo $this->element("administration_general_links"); ?>
    <?php echo $this->element("administration_services_menu"); ?>
 
  <div style="border: 1px solid;border-color:gainsboro; width:430px; padding: 5px; height:180px; float:left"> 
  <h2>CSV Export</h2>  
    <div>This will do a full CSV file export of all <u>Patient Demographics</u> in this account.</div>
	<div class="actions">
		<ul>
			<li>
				<a id="download-dump" href="">Export/Dump All Patients (CSV)</a>
			</li>
		</ul>
	</div>
	<form id="download_dump" action="<?php echo $this->Session->webroot; ?>administration/patient_export/task:download_dump/file_id:<?php echo $Ttime;?>" method="post">
	</form>		
	<div id="download-message"></div>
 </div>
   <div style="border: 1px solid;border-color:gainsboro; width:430px; padding: 5px; height:180px; float:right">   
    <h2>CCR Export</h2>  
    <div>This will do a full CCR export as a Zip file of all Patient data (demographics, medical history, etc) in this account.</div>
	<div class="actions">
		<ul>
			<li>
				<a id="download-dump2" href="">Export/Dump All Patients (CCR)</a>
			</li>
		</ul>
	</div>
	<form id="download_dump2" action="<?php echo $this->Session->webroot; ?>administration/patient_export_ccr/task:download_dump/file_id:<?php echo $Ttime;?>" method="post">
	</form>		
	<div id="download-message2"></div>
 </div>
 
</div>
<script type="text/javascript">	
$(function(){
		$('#download-dump').click(function(evt){
			evt.preventDefault();
			var num=new Date().getTime();
			var x=window.confirm("Are you sure you want to download/dump all patients?")
			if (x) {
			$("#download-message").html('<span style="color:red;font-weight:bold">PROCESSING....<em>may take a while on large accounts</em></span>');
			$.post("<?php echo $this->Session->webroot; ?>administration/patient_export/task:process_csv_dump/file_id:<?php echo $Ttime;?>/", 
			function(data) {
   			 
 			});
 			setTimeout(function() { listenForFile();}, 300); 
			
 			}
		});
		$('#download-dump2').click(function(evt){
			evt.preventDefault();
			var num=new Date().getTime();
			var x2=window.confirm("Are you sure you want to download/dump all patients?")
			if (x2) {
			$("#download-message2").html('<span style="color:red;font-weight:bold">PROCESSING....<em>may take a while on large accounts</em></span>');
			$.post("<?php echo $this->Session->webroot; ?>administration/patient_export_ccr/task:process_ccr_dump/file_id:<?php echo $Ttime;?>/", 
			function(data) {
   			 
 			});
 			setTimeout(function() { listenForFile2();}, 300); 
			
 			}
		});		
});
function listenForFile()
{
	$.post("<?php echo $this->Session->webroot; ?>administration/patient_export/task:wait_for_result/file_id:<?php echo $Ttime;?>/", function(data) {
	  if (data) {
	     $("#download-message").html('<span style="color:red;font-weight:bold">FINISHED! <em>now downloading...</em></span>');
	     setTimeout(function() { $('#download_dump').submit();}, 3000); 

   	  } else {
   	    setTimeout(function() { listenForFile();}, 3000);
   	  }    
 	}); 		
}
function listenForFile2()
{
	$.post("<?php echo $this->Session->webroot; ?>administration/patient_export_ccr/task:wait_for_result/file_id:<?php echo $Ttime;?>/", function(data) {
		initAutoLogoff();
	  if (data) {
	     $("#download-message2").html('<span style="color:red;font-weight:bold">FINISHED! <em>now downloading...</em></span>');
	     setTimeout(function() { $('#download_dump2').submit();}, 3000); 

   	  } else {
   	    setTimeout(function() { listenForFile2();}, 3000);
   	  }    
 	}); 		
}			
</script>	

