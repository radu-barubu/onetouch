<?php 
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$lab_result_link = $html->url(array('controller' => 'encounters', 'action' => 'lab_results_electronic', 'encounter_id' => $encounter_id));
$smallAjaxSwirl = $html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
$page = (isset($this->params['named']['page']))?$this->params['named']['page']:"";
$doc_type = (isset($this->params['named']['doc_type']))?$this->params['named']['doc_type']:"";
$flag_no_doc_type = 0;

if(isset($saved_search_array["doc_type"]) && (!is_array($saved_search_array["doc_type"]) && $saved_search_array["doc_type"]=="")){
	$flag_no_doc_type = 1;
} 
$flag_type = 0;
if(empty($this->params['named']['doc_type']) && !empty($page)){
	$flag_type=1;
}

$doc = array();
if(isset($doc_type) && $doc_type!=""){
$doc_type = explode(',',$doc_type);

foreach($doc_type as $doc_typee){
	$doc[] = base64_decode($doc_typee);
}

}
$doc_status = (isset($this->params['named']['doc_status']))?base64_decode($this->params['named']['doc_status']):"";
$doc_fromdate = (isset($this->params['named']['doc_fromdate']))?base64_decode($this->params['named']['doc_fromdate']):"";
$doc_todate = (isset($this->params['named']['doc_todate']))?base64_decode($this->params['named']['doc_todate']):"";
$doc_name = (isset($this->params['named']['doc_name']))?base64_decode($this->params['named']['doc_name']):"";

echo $this->Html->script('multiple-select-master/jquery.multiple.select.js');
//echo $this->Html->css(array('multiple-select.css'));

?>
<link rel="stylesheet" type="text/css" href="/preferences/multiple_select" />
<script language="javascript" type="text/javascript">

function processRequest(){
		
		//for base64 encoding
			
			var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}
			
			var string_test="";
			var test_name = $('#doc_types').val();
			
			if(test_name){
			var tst;
			if(test_name.indexOf(',')){
				tst = test_name.toString().split(",");
			}
			count = tst.length;
			var string_test="";
				for(var i=0;i<tst.length;i++){
					var test_value = Base64.encode(tst[i]);
					if(i==(count-1)){
						string_test += test_value;
					} else {
						string_test += test_value+',';
					}
				}
			} else {
				string_test="";
			}
			
			var doc_name = Base64.encode($('#doc_name').val());
			var doc_status = Base64.encode($('#doc_status').val());
			var doc_fromdate = Base64.encode($('#from_date').val());
			var doc_todate = Base64.encode($('#to_date').val());
			
		
		var url = '<?php echo $html->url(array('controller' => 'encounters', 'action' => 'encounter_documents', 'encounter_id' => $encounter_id,'task'=>'update_result')); ?>/doc_name:'+doc_name+'/doc_type:'+string_test+'/doc_status:'+doc_status+'/doc_fromdate:'+doc_fromdate+'/doc_todate:'+doc_todate;
		$('#documents_area').show();
		$('#documents_area').html('<?php echo $smallAjaxSwirl; ?>');
		$('#documents_area').css('min-height','260px');
		$.get(url,function(data){			
				$('#documents_area').html(data);
				});
		$('#documents_area').show();
	}

    $(document).ready(function()
    {
		
		<?php if( !empty( $saved_search_array ) && empty($page)){ ?>
		$('#doc_name').val('<?php echo $saved_search_array["doc_name"];?>');
		$('#doc_status').val('<?php echo $saved_search_array["doc_status"];?>');
		$('#from_date').val('<?php echo $saved_search_array["doc_fromdate"];?>');
		$('#to_date').val('<?php echo $saved_search_array["doc_todate"];?>');
		processRequest();
		
		<?php } ?>
		
		<?php  if(isset($doc_status) && $doc_status!=""){ ?>
			$('#doc_status').val('<?php echo $doc_status; ?>');
		<?php  } ?>
		<?php if(isset($doc_name) && $doc_name!=""){ ?>
			$('#doc_name').val('<?php echo $doc_name; ?>');
		<?php } ?>
		<?php if(isset($doc_fromdate) && $doc_fromdate!=""){ ?>
			$('#from_date').val('<?php echo $doc_fromdate; ?>');
		<?php } ?>
		<?php if(isset($doc_todate) && $doc_todate!=""){ ?>
			$('#to_date').val('<?php echo $doc_todate; ?>');
		<?php } ?>
		
		
		$('select#doc_types').multipleSelect({
			  placeholder:"Document Type",
			   onClick : function(){
				  processRequest();
			  }
		});
		
		//$('select#doc_types').multipleSelect('checkAll');
		$('#doc_status').bind('change',function(){
		processRequest();
		});
		$('#show_advanced').click(function(){		
			if ($('#show_advanced').attr('checked')) {			
			   $('#new_advanced_area').slideDown("slow");
			} else {
			   $('#new_advanced_area').slideUp("slow");
			}
		});
		$("#doc_name").addClear(
		{
			closeImage: "<?php echo $this->Session->webroot; ?>img/clear.png",
			onClear: function()
			{
				processRequest();
				
			}
		});
		var globalTimeout = null;
		function SearchFunc(){  
			globalTimeout = null;  
			processRequest();
		}
		$('#doc_name').keyup(function(evt){
                        if (evt.which === 13) {
                            return false;
                        }
						
						//1 second delay on the keyup                   
                        if(globalTimeout != null) clearTimeout(globalTimeout);  
						globalTimeout =setTimeout(SearchFunc,1000); 
                        //loadEncounterTable(current_url);

			
		});
		$('#doc_name').siblings('a').css('right','10px');
		var allSelected_doctypes = $("select#doc_types option:not(:selected)").length == 0;
			  if(allSelected_doctypes==true){
				  $('select#doc_types').multipleSelect('checkAll');
			  }
		$('#to_date').bind('change',function(){
			if($('#from_date').val() && $('#to_date').val()){
			processRequest();
			}
		});
		$('#from_date').bind('change',function(){
			if($('#from_date').val() && $('#to_date').val()){
			processRequest();
			}
		});
		$('#save_filter').click(function(){
		
		//for base64 encoding
			
			var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}

			
			var doc_name = Base64.encode($('#doc_name').val());
			var doc_type = $('#doc_types').val();
			var doc_status = Base64.encode($('#doc_status').val());
			var doc_fromdate = Base64.encode($('#from_date').val());
			var doc_todate = Base64.encode($('#to_date').val());
		
			
			var url = '<?php echo $html->url(array('controller' => 'encounters','task'=>'save_filter', 'action' => 'encounter_documents', 'encounter_id' => $encounter_id)); ?>';
			$.post(url,{doc_name:doc_name,doc_status:doc_status,doc_type:doc_type,doc_fromdate:doc_fromdate,doc_todate:doc_todate},function(data){			
					if( data ) {
						$('#search_saved_message').show('slow');
						setTimeout(function(){$('#search_saved_message').hide('slow');} , 4000);
					}
			});
		
		});
		<?php if((empty($saved_search_array["doc_type"]) && empty($doc))){ ?>
			$('select#doc_types').multipleSelect('checkAll');
		<? } ?>
		<?php if($flag_type==1 || $flag_no_doc_type==1){ ?>
			$('select#doc_types').multipleSelect('uncheckAll');
		<?php } ?>
		
		$('#reset_cache_filter').click(function(){
		
			$(this).hide();
			var url = '<?php echo $html->url(array('controller' => 'encounters','task'=>'delete_filter', 'action' => 'encounter_documents', 'encounter_id' => $encounter_id)); ?>';
			$.post(url,'',function(){
				$('#search_filter').hide();
				$('#reset_cache_filter').hide();	
				$('#doc_name').val('');
				$('select#doc_types').multipleSelect('checkAll');
				$('#doc_status').val('all');
				$('#from_date').val('');
				$('#to_date').val('');
				
				
				processRequest();		
			});
		
		});
		/*
		$("#referred_to").autocomplete('<?php echo $this->Session->webroot; ?>encounters/encounter_documents/encounter_id:<?php echo $encounter_id; ?>/task:document_name/',{
			minChars: 1,
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		
		$("#referred_to").result(function(event, data, formatted)
		{
			var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}

			
			var doc_name = Base64.encode($("#referred_to").val());
			
			//var doc_name = $("#referred_to").val();
			
			var url = '<?php echo $html->url(array('controller' => 'encounters', 'action' => 'encounter_documents', 'encounter_id' => $encounter_id,'task'=>'update_result')); ?>/doc_name:'+doc_name;
			$.get(url,function(data){
				
				$('#documents_area').html(data);
			});
			
		});
		*/
	 	initCurrentTabEvents('documents_area');
		
		$('.section_btn').click(function(){
			$('#doc_info').css('display','none');
		});
		$('#doc_info').css('display','block');
		$('#pointofcareBtn').click(function()
		{
			
			$('#doc_info').css('display','none');
			$(".tab_area").html('');
			$("#imgLoadLabResults").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_lab', 'encounter_id' => $encounter_id)); ?>");
		});

		 $('.title_area .section_btn').click(function()
        	{
            	   $(".tab_area").html('');
            	   $("#imgLoadInhouseLab").show();
		   loadTab($(this),$(this).attr('url'));
        	});
        		
		$('#outsideLabBtn').click(function()
		{
			
			$('#doc_info').css('display','none');
            $(".tab_area").html('');
			$("#imgLoadInhouseLab").show();
			loadTab($(this), "<?php echo $lab_result_link; ?>");
		});
		$('#documentsBtn').click(function()
		{
			$('#doc_info').css('display','none');
			$(".tab_area").html('');
			$("#imgLoadInhouseLab").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'encounters', 'action' => 'encounter_documents', 'encounter_id' => $encounter_id)); ?>");
		});
		
		
		
		
		<?php echo $this->element('dragon_voice'); ?>
	});
	function update_reviewed(obj) 
			{
				if($(obj).attr('checked'))
				{
					
					$(obj).parent().siblings('.text_comment').show('slow');
					
				}
				
				
				var document_id = obj.value;
				
				if(obj.checked==true) {
					var reviewed = 1;
				} else {
					var reviewed = 0;
				}
				$.post(
					'<?php echo $html->url(array('controller' => 'encounters','action' => 'encounter_documents', 'task' => 'save_reviewed', 'encounter_id' => $encounter_id)); ?>/document_id:'+document_id, 
					{ 'data[PatientDocument][document_test_reviewed]': reviewed,'data[PatientDocument][document_id]': document_id  }, 
					function(data)
					{
						showInfo(data.msg, "notice");				
					},
					'json'
				); 
				
				if(reviewed==0)
				{
					$(obj).parent().siblings('.text_comment').val("");
					var comment = $(obj).parent().siblings('.text_comment').val();
					$.post(
					'<?php echo $html->url(array('controller' => 'encounters','action' => 'encounter_documents', 'task' => 'save_reviewed_comment', 'encounter_id' => $encounter_id)); ?>/document_id:'+document_id, 
					{ 'data[PatientDocument][comment]': comment,'data[PatientDocument][document_id]': document_id  }
					); 
					
					$(obj).parent().siblings('.text_comment').hide('slow');
					
				}
				
			}
			function update_comment(obj)
			{
				var document_id = obj.id;
				var comment = $.trim(obj.value);
				
					
					$.post(
					'<?php echo $html->url(array('controller' => 'encounters','action' => 'encounter_documents', 'task' => 'save_reviewed_comment', 'encounter_id' => $encounter_id)); ?>/document_id:'+document_id, 
					{ 'data[PatientDocument][comment]': comment,'data[PatientDocument][document_id]': document_id  }
				);
				
				
			}
</script>
<style>
#from_date,#to_date{
	border: 1px solid #AAAAAA;
	font-size:14px;
	width:105px;
	padding:5px;
}
#new_advanced_area table, #new_advanced_area tr, #new_advanced_area td, #new_advanced_area tbody{
	vertical-align:middle;
}
</style>
<div style="overflow: hidden;" >	
	<div class="title_area">
            <?php echo $this->element('../encounters/tabs_results', array('encounter_id' => $encounter_id)); ?>	
        <div  class="title_text"> 
            <a href="javascript:void(0);" id="pointofcareBtn"  style="float: none;">Point of Care</a>
            <a href="javascript:void(0);" id="outsideLabBtn" style="float: none;">Outside Labs</a>
			<a href="javascript:void(0);" id="documentsBtn" style="float: none;" class="active">Documents</a>
        </div>       
    </div>
    	    <span id="imgLoadInhouseLab" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
<?php if( !empty( $saved_search_array )){ ?>
<div class="small_notice" style="position:relative;width:270px;" id="search_filter">Your search filter is in effect.  <input type="button"  id="reset_cache_filter" class="smallbtn" style="margin-left:5px;float:none;display:inline-block;" value="Reset"></div>
<?php } ?>
<div class="notice" id="search_saved_message" style="display:none;">
		Your search preference has been saved.
</div>
<div style="margin-bottom:10px;" id="doc_info">
<table class="form" cellspacing="0" cellpadding="0" border='1' style="font-size: 14px;vertical-align: middle;">
<tr>
<td style="width:118px;">Document Name: </td><td><input type="text" id="doc_name" style="border: 1px solid #AAAAAA;font-size:14px;margin-left:5px;margin-right:5px;padding: 5px;width:300px;">
<td> <span style="margin-left:20px"> <label for="show_advanced" class="label_check_box"><input type="checkbox" id="show_advanced" name="show_advanced"> Advanced</label></span></td>
</tr>
</table>
<!--<input type='text' name='doc_name' id='referred_to' style="border: 1px solid #AAAAAA;font-size: 14px;margin-bottom: 10px;margin-left:50px;padding: 5px;width:350px;">-->
<div id="new_advanced_area" style="display:none;margin-bottom:5px;">
<?php
echo $this->element('adavanced_document_search', array('doc_types' => $doc_types,'saved_search_array'=>(!empty($saved_search_array["doc_type"]))?$saved_search_array["doc_type"]:'','doc'=>(!empty($doc))?$doc:'')); 
?>
</div>
</div>
<div class="tab_area" id="documents_area" style="min-height:260px;">

	<form id="frmEncounterDocumentsGrid" method="post" accept-charset="utf-8">
		<table cellpadding="0" cellspacing="0" class="listing">
		<tr>
             <th width="16.66%"><?php echo $paginator->sort('Document Name', 'document_name', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>
             <th width="16.66%"><?php echo $paginator->sort('Comment', 'description', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>             
             <th width="16.66%"><?php echo $paginator->sort('Document Type', 'document_type', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>				
             <th width="16.66%"><?php echo $paginator->sort('Service Date', 'service_date', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>
             <th width="16.66%" style="text-align:center;"><?php echo $paginator->sort('Reviewed', 'document_test_reviewed', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>
             <th width="16.66%" style="text-align:center"><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>
        </tr>
		<?php $g = 0;
		
		foreach ($PatientDocument as $PatientDocument_record):
		?>
		<tr>
		<?php
		//if($PatientDocument_record['PatientDocument']['document_type']=="Lab")
		//{
		 ?>
            <td  class="<?php echo ($PatientDocument_record['PatientDocument']['attachment']!="")?'ignore':'';?>">
			<div class="link_hash" style="float: left; margin-top: 3px; cursor: pointer;"><?php echo $html->image('valid_hash.png', array('alt' => '')); ?></div>&nbsp;&nbsp;
            <?php 
            if($PatientDocument_record['PatientDocument']['attachment']!="")
            {
             echo $html->link($PatientDocument_record['PatientDocument']['document_name'], array('action' => 'encounter_documents', 'task' => 'download_file', 'document_id' => $PatientDocument_record['PatientDocument']['document_id'])); 
             echo $this->Html->image("download.png", array(    "alt" => "Download",    'url' => array('action' => 'encounter_documents', 'task' => 'download_file', 'document_id' => $PatientDocument_record['PatientDocument']['document_id']) ));
             }
             else
             {
                  echo $PatientDocument_record['PatientDocument']['document_name'];
             }
			 ?>
             </td>
             <td><?php echo $PatientDocument_record['PatientDocument']['description'];?></td>
			 <td><?php echo $PatientDocument_record['PatientDocument']['document_type']; ?></td>
			 <td><?php echo __date($global_date_format, strtotime($PatientDocument_record['PatientDocument']['service_date'])); ?></td>
			 
			 <td style="text-align:center" class="ignore"><label for="reviewed<?php echo $g;?>" class="label_check_box_hx"><input type="checkbox" value="<?php echo $PatientDocument_record['PatientDocument']['document_id']; ?>" id="reviewed<?php echo $g;?>" onclick="update_reviewed(this);" <?php if($PatientDocument_record['PatientDocument']['document_test_reviewed']) { echo 'checked="checked"'; $display= 'display:block'; } else { $display= 'display:none'; } ?>  /></label><br />
			
			 <textarea placeholder="Comment (Optional)" onblur="update_comment(this)" class="text_comment"  style="<?php echo $display; ?>; margin-top:5px;" id="<?php echo $PatientDocument_record['PatientDocument']['document_id']; ?>"><?php if(!empty($PatientDocument_record['PatientDocument']['comment'])) echo $PatientDocument_record['PatientDocument']['comment']; ?></textarea>
			
			  </td>
             <td style="text-align:center"><?php echo $PatientDocument_record['PatientDocument']['status']; ?></td>
		<?php 
		//}
	 ?>
	</tr>
	<?php  
	$g++; 
	endforeach; ?>
       </table>
		</form>
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'PatientDocument', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientDocument') || $paginator->hasNext('PatientDocument'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientDocument'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientDocument', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientDocument', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientDocument', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
    
    </div>
<script>
$('input[name=selectAll]').click(function(){
				processRequest();
			});
</script>		
