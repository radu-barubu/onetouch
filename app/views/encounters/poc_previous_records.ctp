<?php

$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
if(isset($PatientTestItem['PatientTest']))
{
    extract($PatientTestItem['PatientTest']);
}
$RequestRecords = isset($RequestRecords)?$RequestRecords:'';
$DiscussTests = isset($DiscussTests)?$DiscussTests:'';
?>
<style>

.lab_txt_box, {
	cursor: pointer;
}
.lab_txt_box:hover {
	background: #e2e2e2;
}

table.previousRecords {
	border-width: 1px;
	border-spacing: 0px;
	border-style: outset;
	border-color: gray;
	border-collapse: separate;
	background-color: white;
    width:100%;

}
table.previousRecords th {
	border-width: 1px;
	padding: 1px;
	border-style: inset;
	border-color: gray;
	background-color: #DFDFDF;
	-moz-border-radius: 0px 0px 0px 0px;
}
table.previousRecords td {
	border-width: 1px;
	padding: 1px;
	border-style: inset;
	border-color: gray;
	background-color: white;
	-moz-border-radius: 0px 0px 0px 0px;
}

table.labs {
	border-width: 1px;
	border-spacing: 0px;
	border-style: outset;
	border-color: gray;
	border-collapse: separate;
	background-color: white;
    width:100%;
    text-align:top;

}
table.labs th {
	border-width: 1px;
	padding: 1px;
	border-style: inset;
	border-color: gray;
	background-color: #DFDFDF;
	-moz-border-radius: 0px 0px 0px 0px;
}
table.labs td {
	border-width: 1px;
	padding: 1px;
	border-style: inset;
	border-color: gray;
	background-color: white;
	-moz-border-radius: 0px 0px 0px 0px;
}

table.inHouseTestsTbl {
	border-width: 1px;
	border-spacing: 0px;
	border-style: outset;
	border-color: gray;
	border-collapse: separate;
	background-color: white;
    width:80%;
    text-align:top;

}
table.inHouseTestsTbl th {
	border-width: 1px;
	padding: 1px;
	border-style: inset;
	border-color: gray;
	background-color: #DFDFDF;
	-moz-border-radius: 0px 0px 0px 0px;
}
table.inHouseTestsTbl td {
	border-width: 1px;
	padding: 1px;
	border-style: inset;
	border-color: gray;
	background-color: white;
	-moz-border-radius: 0px 0px 0px 0px;
}
</style>

<script language="javascript" type="text/javascript">

    var ajax_load = '<img src="<?php echo $this->Session->webroot; ?>img/ajax_loaderback.gif" alt="loading..." />';  
	lab_trigger_func = function()
	{	
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/poc_previous_records/encounter_id:<?php echo $encounter_id; ?>/task:get_list/', 
			'', 
			function(msg)
			{
			    //alert('loaded'+msg);
             var initialTestRecordsArr = msg.PatientTestData;
             var inhouseTestArr = msg.InhouseTest;
             var labsObj = msg.labs;
             var htmlStr = "<table class='demographic' width='100%'><tr><td width=80%>";
    
             try
			 {
          	      htmlStr += "<div id='previousRecordsRecordDiv'><br>";
          	      htmlStr +="<table width='100%'><tr><td width='600px'><table cellpadding='0' cellspacing='0' class='previousRecords'><tr><th>Date Entered</th><th>Date Done</th><th>Test</th></tr>";
          	      for(i=0;i<initialTestRecordsArr.length; i++)
			      {
	                  var initialObj = initialTestRecordsArr[i].PatientTestData;
	          	      htmlStr +="<tr><td>"+initialObj.date+"</td>";
	          	      htmlStr +="<td>"+initialObj.test_date+"</td>";
	          	      var testsString = initialObj.id+"_"+initialObj.test_results;
	          	      htmlStr +="<td><span onclick=getTestRecordDetails("+initialObj.id+",'"+initialObj.test_results+"') style='cursor:pointer;cursor:hand;text-decoration:underline;'>"+initialObj.test_results+"</span></td></tr>";
          	      }
          	  
          	      htmlStr +="</table></td>";
          	      htmlStr +="<td width='100px'></td><td><div id='labs_tests'></div></td></tr></table>";
          	      htmlStr +="</div>";
            }
			catch(ex)
			{
          	    htmlStr += "<br/><div id='previousRecordsRecordDiv'><h3>No records Found</h3></div>";          
          	    $('#previousRecordsRecordDiv').html(htmlStr);
          	    if(typeof($ipad)==='object')$ipad.ready();
            }
           
           try
		   {
               if(inhouseTestArr.length)
			   {
           	       htmlStr += "<div id='inHouseTestsDiv' style='display:none'>";
	               htmlStr += "<br> <table cellpadding='0' cellspacing='0' class='inHouseTestsTbl'><tr><th>Tests</th><th>selection</th></tr>";
	               for(i=0;i<inhouseTestArr.length;i++)
				   {
	                   var inhouseTestobj =inhouseTestArr[i].InhouseTest;
	          	       htmlStr += "<tr><td>"+inhouseTestobj.Test+"</td>";
	          	       var checkboxStr ="<input type='checkbox' checked id=''/>"
	          	       if(inhouseTestobj.checked ==0)
				       {
	          	 	       checkboxStr ="<input type='check' id=''/>"
	          	       }
	          	       htmlStr += "<td>"+checkboxStr+"</td></tr>";
          	       }
       		       htmlStr += "</table>";
               }
			   else
			   {
           	       htmlStr += "<div id='inHouseTestsDiv' style='display:none;'><br><h3>No records Found</h3></div>";
          	       $('#inHouseTestsDiv').html(htmlStr);
          	       if(typeof($ipad)==='object')$ipad.ready();
               }
           
          }
		  catch(ex)
		  {
               htmlStr += "<div id='inHouseTestsDiv' style='display:none;'><br><h3>No records Found</h3></div>";          
          	   $('#inHouseTestsDiv').html(htmlStr);
          	   if(typeof($ipad)==='object')$ipad.ready();
          }
         
          htmlStr += "</div>";
          $('#lab_listing_area').html(htmlStr);
          if(typeof($ipad)==='object')$ipad.ready();
		  
	   },'json');
	   
   }
   function getTestRecordDetails(labId,testname)
   {
	   //var URL = "labs_pulllab.html?Ttype=lab&record_id="+labId;
	  // alert('pull lab'+labId+testname);
	    $.post('<?php echo $this->Session->webroot; ?>encounters/poc_previous_records/encounter_id:<?php echo $encounter_id; ?>/task:get_testrecord_details/', 'record_id='+labId,
		function(msg)
		{     
    	    var responseObj = msg.response;
    	    try
			{
    		    var HtmlStr ="";
    		    if(responseObj.length)
				{
    			    HtmlStr = "";
    			    HtmlStr +="<span id='displabs'>";
				    HtmlStr +="<div class='lab_comments' id='div_comments' >";
				    HtmlStr +="<span class='lab_txt_box' id='div_edit"+labId+"' onclick='editComment("+labId+")'>(Optional comments)</span></div>";
				    HtmlStr +="<div id='ireviewed'><input type=checkbox id='review' name='review' onClick='ReviewIt("+labId+");' /> Reviewed?</div>";
				    HtmlStr +="<table cellpadding='0' cellspacing='0' class='labs'>";
				    HtmlStr +="<tr><td colspan='3'>"+testname+"</td></tr>";
    			   for(i=0;i<responseObj.length;i++)
				   {
    				   var testsObj=responseObj[i].PatientTestData; 
					   var test_result = testsObj.test_results;
    				   var test_splitted = test_result.split("?");
    			       HtmlStr +="<tr>";
					   HtmlStr +="<td>"+test_splitted[0]+"</td>";
					   HtmlStr +="<td>"+test_splitted[1]+"</td>";
					   HtmlStr +="<td>"+test_splitted[2]+"</td>";
				 	   HtmlStr +=" </tr>";
    			   }
    			   HtmlStr +="</table></span>";    			
    		    }
				else
				{
    			   HtmlStr +="No Records Found";
    		    }
    		    $('#labs_tests').html(HtmlStr);
    		    if(typeof($ipad)==='object')$ipad.ready();
    	    }
		    catch( ex)
		    {
    		    alert(ex);    		
    	    }
        }, 'json');  
   }
   function showPreviousRecords()
   {
	   $("#previousRecordsRecordDiv").css("display", "block");
	   $("#inHouseTestsDiv").css("display", "none");
   }

   function showInHouseTests()
   {
	   $("#inHouseTestsDiv").css("display", "block");
	   $("#previousRecordsRecordDiv").css("display", "none");
   }
   function OrderRecs()
   {
	   postUrl='<?php echo $this->Session->webroot; ?>encounters/poc_previous_records/encounter_id:<?php echo $encounter_id; ?>/task:update_test/'
       //$("#OrderRecs").html(ajax_load).load(postUrl);     
	   var val = (jQuery('#request_oldrecord').is(':checked'))?'1':'0';

	   var formobj = $("<form></form>");
	   formobj.append('<input name="data[submitted][id]" type="hidden" value="'+'RequestRecords'+'">');
	   formobj.append('<input name="data[submitted][value]" type="hidden" value="'+val+'">');
			
	   $.post(postUrl, formobj.serialize(), function(data){ });
   }
   function Discuss()
   {
       postUrl='<?php echo $this->Session->webroot; ?>encounters/poc_previous_records/encounter_id:<?php echo $encounter_id; ?>/task:update_test/'
	   var val = (jQuery('#discuss_test').is(':checked'))?'1':'0';

	   var formobj = $("<form></form>");
	   formobj.append('<input name="data[submitted][id]" type="hidden" value="'+'DiscussTests'+'">');
	   formobj.append('<input name="data[submitted][value]" type="hidden" value="'+val+'">');
			
	   $.post(postUrl, formobj.serialize(), function(data){ });  
   }
   function editComment(tabid)
   {
	   $('.lab_txt_box').editable('<?php echo $this->Session->webroot; ?>encounters/poc_previous_records/encounter_id:<?php echo $encounter_id; ?>/task:edit/', { 
			 cancel    : '<span class="btn">Cancel</span>',
			 submit    : '<span class="btn">OK</span>',
			 indicator : '<?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?>',
			 tooltip   : '(Optional comments)',
			 placeholder: '(Optional comments)'
		});	
   }

   function updateComment(tabId)
   {
	//var divObj = "#div_edit"+tabid;
	//var  htmlStr =comments;
   }
   function ReviewIt(review_id)
   {
	    //lUrlb="lab_requests.html?q=ReviewLab&confirm="+pullab;
        //$("#ireviewed").html(ajax_load).load("data");   
		postUrl='<?php echo $this->Session->webroot; ?>encounters/poc_previous_records/encounter_id:<?php echo $encounter_id; ?>/task:update_patient_testdata/'
       //$("#OrderRecs").html(ajax_load).load(postUrl);     
	   var val = (jQuery('#review').is(':checked'))?'1':'0';

	   var formobj = $("<form></form>");
	   formobj.append('<input name="record_id" id="record_id" type="hidden" value="'+review_id+'">');
	   formobj.append('<input name="data[submitted][id]" type="hidden" value="'+'reviewed'+'">');
	   formobj.append('<input name="data[submitted][value]" type="hidden" value="'+val+'">');
			
	   $.post(postUrl, formobj.serialize(), function(data){ });  
   }
	$(document).ready(function()
	{
		lab_trigger_func();		
		
		  $('#InHouseWork').click(function()
		  {
		      $("#previous_record_area").html('');
			  $("#imgLoad").show();
			  loadTab($(this), "<?php echo $html->url(array('controller' => 'encounters', 'action' => 'in_house_work_labs', 'encounter_id' => $encounter_id)); ?>");
		  });
		  <?php echo $this->element('dragon_voice'); ?>
	});


</script>
<form>
<div style="overflow: hidden;">
	<div class="title_area">
		<div class="title_text">
<!--table cellpadding="0" cellspacing="0" class="form">
	<tr>
		<td>
		<span class="title_item active" style="cursor:pointer;">Previous Records</span>&nbsp;&nbsp;
		<a href="javascript:void(0);" id="InHouseWork">In-House Work</a>&nbsp;&nbsp;
		</td>
	</tr>
</table-->
		</div>	   
	</div>
</div>
<span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
<div id="previous_record_area">
<table cellpadding="0" cellspacing="0" class="form">
	<!--<tr>
	    <td colspan="2">
		<div id='OrderRecs'><input type='checkbox' id='request_oldrecord' name='request_oldrecord' "<?php echo $RequestRecords==1?'checked':''; ?>" onclick='javacsript: OrderRecs();'>&nbsp;Request Old Records?</div>
        <div id='Discuss'><input type='checkbox' id='discuss_test' name='discuss_test' "<?php echo $DiscussTests==1?'checked':''; ?>" onclick='javacsript: Discuss();'>&nbsp;Discussed Test(s) with MD?</div>
	    </td>
	</tr>-->
    <tr>
		<td colspan="2">
        	<div id="lab_listing_area"></div>
		</td>
	</tr>
</table>
</div>
</form>