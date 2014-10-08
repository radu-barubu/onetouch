<?php
$user_info=$this->Session->read('UserAccount');
$page_access = $this->QuickAcl->getAccessType("encounters", "superbill");
echo $this->element("enable_acl_read", array('page_access' => $page_access));
$total_fees=0;
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
echo $this->Html->css(array('superbill.css'));
echo $this->Html->script('ipad_fix.js');
$patient_education = '';
$pr=$this->Session->read('PracticeSetting');
if(is_array($PlanEducationItem) && count($PlanEducationItem) != 0)
{
     extract($PlanEducationItem);
	 $patient_education = $PlanEducationItem['EncounterPlanAdviceInstructions']['patient_education_comment'];
}
foreach($services_levels as $sl) {
    if( $sl['AdministrationSuperbillServiceLevel']['service_level_type']== 1) {
      $sltype = ' [New]';
    } 
    else if ($sl['AdministrationSuperbillServiceLevel']['service_level_type']== 2) {
      $sltype = ' [Est]';
    }
    else  {
      $sltype='';
    }
   $service_levels[]= $sl['AdministrationSuperbillServiceLevel']['service_level_code'] . ' ('. $sl['AdministrationSuperbillServiceLevel']['service_level_description'].  $sltype .')';
}
asort($service_levels);

foreach($advanced_levels as $al) {
   $advanced_service_levels[]= array('description' => $al['AdministrationSuperbillAdvanced']['advanced_level_description'], 'code' => $al['AdministrationSuperbillAdvanced']['advanced_level_code']);
}
asort($advanced_service_levels);

$grabbval='';
$bill_types = array("Care", "Priv.", "MC", "Medicaid", "Cash");
$return_to_office_options = array("Days", "Weeks", "Mos", "As Needed");

$txt_svc_codes = 2; //how many free text service codes?
$svc_count=count($advanced_service_levels);
$svc_count = $svc_count + $txt_svc_codes; // tack on txt_svc_codes fields to total count
$svc_count = ($svc_count & 1) ? $svc_count+1 : $svc_count; //if odd number, increase by 1 to make it even
$svc_chunk=array_chunk($advanced_service_levels, ($svc_count/2));

if (!isset($PracticeEncounterTab)) {
	$PracticeEncounterTab = array();
}

$enabledTabs = array();

foreach ($PracticeEncounterTab as $p) {
	$enabledTabs[] = $p['PracticeEncounterTab']['tab'];
}
extract($superbill_item['EncounterSuperbill']);
if(isset($service_level))
	$service_level = html_entity_decode($service_level);

  if (!function_exists('formatCPT')) {
    function formatCPT($string) {
     $regex="/\b[\dA-Za-z]\d{4}|\d{4}[A-Za-z]\b/";
     preg_match_all($regex,
        $string,
      $out, PREG_PATTERN_ORDER);
     $result=implode(', ',$out[0]);
     return '['.$result.']';
   }
  }


 //calculate total POC tests
 $ttl_poc_tests = count($in_house_supplies) + count($in_house_meds) + count($in_house_injections) + count($in_house_immunizations) + count($in_house_procedures) + count($in_house_radiologies) + count($in_house_labs);
?>
<div id="void-dialog" class="hide_for_print hidden">
	<div class="error">
	<?php //only the provider or practice admin can void encounters - #3506
	  if(($provider_info['UserAccount']['user_id'] == $user_info['user_id']) || $user_info['role_id'] == EMR_Roles::PRACTICE_ADMIN_ROLE_ID || $user_info['role_id'] == EMR_Roles::SYSTEM_ADMIN_ROLE_ID ) { $disableVoid=false;
	?>
	   <div id="confirm_void_wrap">
		 Are you sure you want to mark this encounter as void? &nbsp; 
		<input type="radio" id="confirm_void1" name="confirm_void" value="1" /><label for="confirm_void1">Yes</label>
		<input type="radio" id="confirm_void2" name="confirm_void" value="0" checked="checked" /><label for="confirm_void2">No</label>
	   </div>
	 <?php } else { $disableVoid=true; ?>
		Sorry, only the Provider (<?php echo $provider_info['UserAccount']['full_name'];?>) or the Practice Administrator can void this encounter.
	  <?php } ?>	
	</div>
	<br />
</div>
<div id="kareo_error" class="hide_for_print hidden error" style="width:98%;margin-bottom:20px;"></div>
<script language="javascript" type="text/javascript">
 var override_post="",override_post2="";

function endload() 
{
     scrollToTop();
     setTimeout("location.reload(true);", 300);
}

function Override1() {
      override_post=1;
      $("#super_bill_error").hide();

}

function Override2() {
      override_post2=1;
      $("#super_bill_error").hide();

}

$(document).ready(function()
{
  
    $('#print-btn').click(function(evt){
      evt.preventDefault();
      var url = $(this).attr('href');
      
      $('#print-iframe').attr('src', url);
      
      
    });
  
  
    //create bubble popups for each element with class "button"
    $('.hpi_lbl').CreateBubblePopup();
       //set customized mouseover event for each button
       $('.hpi_lbl').mouseover(function(){ 
            //show the bubble popup with new options
            $(this).ShowBubblePopup({
                alwaysVisible: true,
                position :'right',
                align    :'left',
                tail     : {align: 'middle'},
                innerHtml: '<b> ' + $(this).attr('name') + '</b> ',
                innerHtmlStyle: { color: ($(this).attr('id')!='azure' ? '#FFFFFF' : '#333333'), 'text-align':'center'},                                                                         
                                themeName: $(this).attr('id'),themePath:'<?php echo $this->Session->webroot; ?>img/jquerybubblepopup-theme'                                                              
         });
        }); 
	$('#super_bill_error').hide();


    $('.hot-link').click(function(evt){
        
        evt.preventDefault();
        
        var url = $(this).attr('href');
        
				var $planLink = $('#plan_tab').find('a');
				
				var newUrl = $planLink.attr('href') + '?load_advice=1';
				
				$planLink.data('old_link', $planLink.attr('href'));
				$planLink.attr('href', newUrl);
				
        window.hotLink(url);
        
    });

    $('#bt_superbill,#bt_charges,#lock_only').click(function() 
	{
	  $('#super_bill_error').hide();
	<?php if( $pr['PracticeSetting']['kareo_status'] || $pr['PracticeSetting']['hl7_engine'] ) { ?>
	  //make sure valid charges exist first
	  var slevel=$.trim($('#service_level').val());		
	   <?php if (count($assessments) < 1 && $ttl_poc_tests < 1) { ?>
	     if(!override_post) {
	      $('#super_bill_error').html('Enter at least 1 Assessment or 1 POC test <button onclick="Override1();return false;">Override</button>');
	      $('#super_bill_error').css('display', 'inline-block');
		return false;
	     }
	   <?php } ?>
		if (!slevel && !override_post2) {
		  $('#super_bill_error').html('First choose an E/M Service Level above <button onclick="Override2();return false;">Override</button>');
		  $('#super_bill_error').css('display', 'inline-block');
		  return false;
		}
	<?php } ?>
		$('#super_bill_status').hide();
		if(this.id == 'bt_charges') {
		  URL="<?php echo $html->url(array('task' => 'postcharges', 'encounter_id' => $encounter_id)); ?>";
		} else if (this.id == 'lock_only') {
		  URL="<?php echo $html->url(array('task' => 'lock_only', 'encounter_id' => $encounter_id)); ?>";
		} else {
		  URL="<?php echo $html->url(array('task' => 'lockit', 'encounter_id' => $encounter_id)); ?>";
		}
		if($("#pin").val()=='') {
		  $('#super_bill_error').html('Enter your Pin Number');
                    $('#super_bill_error').css('display', 'inline-block');
                    $("#imgLoading").hide();
		   return false;
		}
		$("#imgLoading").show();
		$('#super_bill_error').hide();
		getJSONDataByAjax(
			URL, 
			$('#frm_superbill').serialize(), 
			function(){}, 
			function(response)
			{
                		$("#imgLoading").hide();
                
                if(response.kareo_error)
		{
		    $('#kareo_error').html('<b>Errors from Kareo:</b><br>'+response.kareo_error);
		    $('#kareo_error').css('display', 'inline-block');
		    scrollToTop();
                    
                    if(typeof($ipad)==='object')$ipad.ready();
		}

		if(response.error)
		{
                    $('#pin').focus();
                    $('#super_bill_error').html(response.error);
                    $('#super_bill_error').css('display', 'inline-block');
                    
                    if(typeof($ipad)==='object')$ipad.ready();
                }

                if(response.msg || response.msg2) 
		{
		    mresponse=(response.msg) ? response.msg : response.msg2;
                    $('#super_bill_status').html(mresponse);
                    $('#super_bill_status').css('display', 'inline-block');
                    
		    $("#pin").val("");
		    if (response.msg) {
                     setTimeout("endload()", 1000);
		    }
                    if(typeof($ipad)==='object')$ipad.ready();
                }
			}
		);
    });


    function saveSuperbillValue(field, value, remove, callback)
    {
        $.post(
            '<?php echo $html->url(array('encounter_id' => $encounter_id, 'task' => 'edit')); ?>', 
            {'data[field]' : field, 'data[value]' : value, 'data[remove]' : remove}, 
            function(data)
            {
                if(callback)
                {
                    callback();
                }
            },
            'json'
        );
                
                initAutoLogoff();
    }
    
    $('#bill_code', $('#frm_superbill')).blur(function() 
    {    
        saveSuperbillValue('bill_code', $(this).val(), false, null);
                
                initAutoLogoff();
    });
    
    $('.superbill_bill_type').click(function() 
    {    
        saveSuperbillValue('bill_type', $(this).val(), false, null);
                
                initAutoLogoff();
    });
    
    $('#service_level').change(function() 
    {   
                $('#superbill_loading_img_service_level').css("visibility", "visible");
                
        saveSuperbillValue('service_level', $(this).val(), false, function()
                {
                        $('#superbill_loading_img_service_level', $('#frm_superbill')).css("visibility", "hidden");
                });
                
                initAutoLogoff();
    });
    
    $('.superbill_diagnosis_check').click(function() 
    {
        $('#superbill_loading_img_diagnosis').css("visibility", "visible");
        
        saveSuperbillValue('ignored_diagnosis', $(this).val(), $(this).is(':checked'), function()
        {
            $('#superbill_loading_img_diagnosis', $('#frm_superbill')).css("visibility", "hidden");
        });
                
                initAutoLogoff();
    });
        
        $('.superbill_in_house_labs_check').click(function() 
    {
        $('#superbill_loading_img_in_house').css("visibility", "visible");
        
        saveSuperbillValue('ignored_in_house_labs', $(this).val(), $(this).is(':checked'), function()
        {
            $('#superbill_loading_img_in_house', $('#frm_superbill')).css("visibility", "hidden");
        });
                
                initAutoLogoff();
    });
        
        $('.superbill_in_house_radiologies_check').click(function() 
    {
        $('#superbill_loading_img_in_house').css("visibility", "visible");
        
        saveSuperbillValue('ignored_in_house_radiologies', $(this).val(), $(this).is(':checked'), function()
        {
            $('#superbill_loading_img_in_house', $('#frm_superbill')).css("visibility", "hidden");
        });
                
                initAutoLogoff();
    });
 
        $('.superbill_in_house_injections_check').click(function() 
    {
        $('#superbill_loading_img_in_house').css("visibility", "visible");
        
        saveSuperbillValue('ignored_in_house_injections', $(this).val(), $(this).is(':checked'), function()
        {
            $('#superbill_loading_img_in_house', $('#frm_superbill')).css("visibility", "hidden");
        });
                
                initAutoLogoff();
    });
           
        $('.superbill_in_house_procedures_check').click(function() 
    {
        $('#superbill_loading_img_in_house').css("visibility", "visible");
        
        saveSuperbillValue('ignored_in_house_procedures', $(this).val(), $(this).is(':checked'), function()
        {
            $('#superbill_loading_img_in_house', $('#frm_superbill')).css("visibility", "hidden");
        });
                
                initAutoLogoff();
    });
        
        $('.superbill_in_house_immunizations_check').click(function() 
    {
        $('#superbill_loading_img_in_house').css("visibility", "visible");
        
        saveSuperbillValue('ignored_in_house_immunizations', $(this).val(), $(this).is(':checked'), function()
        {
            $('#superbill_loading_img_in_house', $('#frm_superbill')).css("visibility", "hidden");
        });
                
                initAutoLogoff();
    });
        
        $('.superbill_in_house_meds_check').click(function() 
    {
        $('#superbill_loading_img_in_house').css("visibility", "visible");
        
        saveSuperbillValue('ignored_in_house_meds', $(this).val(), $(this).is(':checked'), function()
        {
            $('#superbill_loading_img_in_house', $('#frm_superbill')).css("visibility", "hidden");
        });
                
                initAutoLogoff();
    });
    

        $('.superbill_in_house_supplies_check').click(function() 
    {
        $('#superbill_loading_img_in_house').css("visibility", "visible");
        
        saveSuperbillValue('ignored_in_house_supplies', $(this).val(), $(this).is(':checked'), function()
        {
            $('#superbill_loading_img_in_house', $('#frm_superbill')).css("visibility", "hidden");
        });
                
                initAutoLogoff();
    });
        
        $('.superbill_labs_check').click(function() 
    {
        $('#superbill_loading_img_plan').css("visibility", "visible");
        
        saveSuperbillValue('ignored_outside_labs', $(this).val(), $(this).is(':checked'), function()
        {
            $('#superbill_loading_img_plan', $('#frm_superbill')).css("visibility", "hidden");
        });
                
                initAutoLogoff();
    });
        
        $('.superbill_radiologies_check').click(function() 
    {
        $('#superbill_loading_img_plan').css("visibility", "visible");
        
        saveSuperbillValue('ignored_radiologies', $(this).val(), $(this).is(':checked'), function()
        {
            $('#superbill_loading_img_plan', $('#frm_superbill')).css("visibility", "hidden");
        });
                
                initAutoLogoff();
    });
        
        $('.superbill_procedures_check').click(function() 
    {
        $('#superbill_loading_img_plan').css("visibility", "visible");
        
        saveSuperbillValue('ignored_procedures', $(this).val(), $(this).is(':checked'), function()
        {
            $('#superbill_loading_img_plan', $('#frm_superbill')).css("visibility", "hidden");
        });
                
                initAutoLogoff();
    });
	
	$('.superbill_service_level_advanced_check').click(function() {
        
		$('#superbill_loading_img_advanced_service').css("visibility", "visible");        
        saveSuperbillValue('service_level_advanced', $(this).val(), ($(this).is(':checked'))? false : true, function()
        {
            $('#superbill_loading_img_advanced_service', $('#frm_superbill')).css("visibility", "hidden");
        });                
        initAutoLogoff();
		
    });
   
   
      $('#show_advanced').click(function(){
        if ($('#show_advanced').attr('checked')) {
           $('#advanced_area').slideDown("slow");
        } else {
           $('#advanced_area').slideUp("slow");
        }
     });
		 
		if ($('.superbill_service_level_advanced_check:checked').length || $('input.other_code_input[value!=""]').length) {
			$('#show_advanced').attr('checked', 'checked');
			$('#advanced_area').slideDown("slow");
			
		}		 

    $('#superbill_comments').blur(function() 
    {    
        	saveSuperbillValue('superbill_comments', $(this).val(), false, null);
                initAutoLogoff();
    });

       
	 $("#pin").keypad({'showAnim': 'fadeIn'});
	 
 
	function toggleChecboxInTable(selector)
	{
		$('table'+selector+' tr td').not('table'+selector+' tr td.ignore').not('table'+selector+' tr:first td').each(function()
		{
			$(this).click(function(event) {	
				if (event.target.type !== 'checkbox') {
						if($(':checkbox', $(this).parent()).is(':checked'))
							$(':checkbox', $(this).parent()).removeAttr("checked");
						else 
							$(':checkbox', $(this).parent()).attr("checked", "checked");
						//$(':checkbox', $(this).parent()).trigger('click');
						$('#superbill_loading_img_advanced_service').css("visibility", "visible");						
						saveSuperbillValue('service_level_advanced', $(':checkbox', $(this).parent()).val(), ($(':checkbox', $(this).parent()).is(':checked'))? false : true, function()
						{
							$('#superbill_loading_img_advanced_service', $('#frm_superbill')).css("visibility", "hidden");
						});               
				}
			});
		});
	}
	toggleChecboxInTable('#listingServices');
	
	 $('.other_code_input').blur(function(){
		var data = $(".other_code_input").serialize(); 
		$.post(
            '<?php echo $html->url(array('encounter_id' => $encounter_id, 'task' => 'save_other_codes')); ?>', 
            data, 
            function(data)
            {
                initAutoLogoff();
            },
            'json'
        );            
	});
	
	
	var $forwarded = $('#forwarded-msg').hide();
	$('#forward').click(function(evt){
		evt.preventDefault();
		
		var routing = $.trim($('#routing').val());
		
		if (!routing) {
			return false;
		}
		
		$.post(
			'<?php echo $html->url(array('task' => 'forward', 'encounter_id' => $encounter_id)); ?>',
			{'routing': routing},
			function(){
				$forwarded.show().delay(3000).fadeOut();
			}
		);
		
	});	
	
       
});


</script>
<style>
li {
                margin-top: 0px;
                margin-bottom: 0px;
                margin-left: 10px;
       }
.other_codes_name { width:250px;margin-bottom:0px; height: 15px; padding: 0.25em; }
.other_codes_code { width:60px;margin-bottom:0px; height: 15px; padding: 0.25em; }

#encounter_date_link {
	float: none;
}

.full-width {
  width: 100%;
}

</style>
<div class="hide_for_print">
<?php
$notices=array();

if ($meds_reviewed == 'no' && $encounter_status != 'Closed')
{
 $notices[]="No one has done Medicine Reconciliation on this patient. <a href='".$this->Html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id))."#meds_allergies' id='linkto_meds_allergy' class='hot-link' style='cursor:pointer;' >Click here</a> to review.";
}

if ($patient_education == '' && $encounter_status != 'Closed')
{
 $notices[]="There is no patient education for this patient. <a href='".$this->Html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id))."#plan' id='linkto_advance_instructions' class='hot-link' style='cursor:pointer;' >click here</a> to enter it.";

}

if(sizeof($notices) > 0)
{
?>
<div class="notice" >
NOTICE: <?php foreach ($notices as $notice) echo ' '.$notice; ?>
</div>
<?php
}
?>

</div>
<div style="clear:both"></div>
<?php
          if($this->params['action'] != 'superbill_print') {
?>
<form id="frm_superbill">
<?php } ?>
<div class="hide_for_print">
<?php   echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => '21'));  ?>	
</div>
<table class="form full-width">
    <tr>
        <td>
            <table class="full-width" cellpadding="0" cellspacing="0">
                <tr>
                    <td <?php if($this->params['action']=='superbill_print') { echo 'style="width: 50%; vertical-align: top;"'; } ?>>
                        <table cellpadding="0" cellspacing="0" class="small_table hasSortingArrow">
                            <tr>
                                <th align=left style="vertical-align:top; padding-top: 10px;">
																	<strong>Encounter #: <?php echo $encounter_id; ?></strong>
																	
																	&nbsp; 
																	<strong>Service Date:</strong>
																	
																	<a href="" rel="<?php echo $this->Html->url(array('controller' => 'encounters', 'action' => 'superbill', 'encounter_id' => $encounter_id, 'task' => 'edit_date')); ?>" id="encounter_date_link" class="editable btn">
																		<?php echo __date($global_date_format, strtotime($encounter_date)); ?>
																	</a>
																	<span class="hide_for_print"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...', 'id' => 'date_loading')); ?></span>
																	<span style="visibility: hidden">
																		<input type="text" id="encounter_date_field" name="encounter_date" val="<?php echo __date($global_date_format, strtotime($encounter_date)); ?>" />
																	</span>
																	
																</th>
                                                    <th  align="right" style="vertical-align:top;"><div class="hide_for_print" align="right"><img class="loading_swirl" id="superbill_loading_img_service_level" src="<?php echo $this->Session->webroot; ?>img/ajax_loaderback.gif" width="16" height="16" /></div></th>

                                <th width="100">
					<div class="hide_for_print" style="width:200px">
					<div style="float:left"><a id="void-btn" href="<?php echo $html->url(array('action' => 'superbill', 'task' => 'void', 'encounter_id' => $encounter_id), true); ?>" class="btn" style="margin-right: 30px;"> Void </a></div>
					<div style="float:left;padding:4px 0 0 5px"><a href="<?php echo $html->url(array('action' => 'superbill_print', 'encounter_id' => $encounter_id), true); ?>" class="pdf-btn" ><?php echo ' PDF ' . $html->image('pdf.png',array('style' => 'padding:0 0 0 0')); ?></a></div>
          <div style="float:right"><a href="<?php echo $html->url(array('action' => 'superbill_print', 'encounter_id' => $encounter_id, 'autoprint' => 1), true); ?>" id="print-btn"><?php echo $html->image('printer_icon.png');?></a>
          
            <iframe id="print-iframe" style="display: none;"></iframe>
          </div>
				</div>
																</th>
                            </tr>
                            <tr>
                                    <td colspan="3">
                                    <span>Service Level: <?php if(isset($service_level) && $this->params['action'] == 'superbill_print') { echo empty($service_level)?'(No CPT Selected)':' '.$service_level;  } else { ?> <select name="data[EncounterSuperbill][service_level]" id="service_level" style="margin-bottom: 0px;">
                                                    <option value="" >Select Level</option>
                                                    <?php if(!empty($grabbval)) {
                                                    	  print "<option value='".$grabbval."' selected=selected>".$grabbval;
                                                    	} 
                                                    	foreach($service_levels as $item) { 
                                                    		print "<option value='".$item."'"; 
                                                    		if($item=="---------") {
                                                    		  echo 'disabled=disabled';
                                                    		} 
                                                    		if($service_level == $item) { 
                                                    		  echo 'selected=selected'; 
                                                    		}  
                                                    		print ">     ".$item."</option>";
                                                    	} ?>    
                                       </select> 
                                </span>
                                 <span style="margin-left:20px"> <label for="show_advanced" class="label_check_box"><input type=checkbox id="show_advanced" name="show_advanced"> Advanced</label></span>


                                   <?php } ?>
                                   <?php 
                                                                    if($this->params['action']=='superbill_print') {
                                                                            $advanced_area= 'block';
                                                                            $listingServicesWidth = '100%';
                                                                    } else {
                                                                            $advanced_area= 'none';
                                                                            $listingServicesWidth = '60%';
                                                                    }
                                                       ?>
                                   <div id="advanced_area" style="display:<?php echo $advanced_area; ?>;background-color:#FFFFFF;margin-top:10px;">
				<?php if ($hours !== false && $minutes!== false): ?> 
				 <div style="float: right;">Total time spent for encounter: <?php echo $hours; ?> hour(s), <?php echo $minutes; ?> minute(s)</div>
				<?php endif;?>
				 <br style="clear: right;" />
				 <div style="width: 45% ; float: left;">
				   <table cellpadding="0" cellspacing="0" class="listing" style="width: 100%" id="listingServices">
				    <tr>
				      <th><div class="hide_for_print"><img class="loading_swirl" id="superbill_loading_img_advanced_service" src="<?php echo $this->Session->webroot; ?>img/ajax_loaderback.gif" width="16" height="16" /></div></th>
				      <?php  if($this->params['action'] != 'superbill_print') { ?>
				      <th>Other services</th><th>Code</th>
				      <?php } else { echo '<th></th><th></th>'; } ?>   
				    </tr>
				    <?php foreach($svc_chunk[0] as $advanced_level) {
				    	$value = $advanced_level['code'].' '.'('.$advanced_level['description'].')';
					if(@in_array($value, $service_level_advanced))	
					  $checked = 'checked="checked"';
					//elseif($this->params['action']=='superbill_print')
					 // continue;
					else 
					  $checked = '';
					  
					if($this->params['action']=='superbill_print')
					{
					  if($checked)
					    echo '<tr><td colspan=2>'.$advanced_level['description'] . ' </td> <td>  '.$advanced_level['code'].'</td></tr>';
					}
					else
					{  
				    ?>
				    <tr>
					<td class="ignore"><label for="<?php echo $value; ?>" class="label_check_box"><input class="superbill_service_level_advanced_check" id="<?php echo $value; ?>" type="checkbox" <?php echo $checked; ?> value="<?php echo $value; ?>" /></label></td>
					<td><?php echo $advanced_level['description']; ?></td>
					<td><?php echo $advanced_level['code']; ?></td>
				   </tr>
				   <?php 
				   	}
				   } 
				   ?>
				   </table>
				</div>
				<div style="width: 45% ; float: left; margin-left: 20px;">
				   <table cellpadding="0" cellspacing="0" class="listing" style="width: 100%" id="listingServices">
				    <tr>
				      <th width="15"><div class="hide_for_print"><img class="loading_swirl" id="superbill_loading_img_advanced_service" src="<?php echo $this->Session->webroot; ?>img/ajax_loaderback.gif" width="16" height="16" /></div></th>
				      <?php  if($this->params['action'] != 'superbill_print') { ?>
				      <th>Other services</th><th>Code</th>
				      <?php } ?>   
				    </tr>
				    <?php foreach($svc_chunk[1] as $advanced_level) {
				    	$value = $advanced_level['code'].' '.'('.$advanced_level['description'].')';
					if(@in_array($value, $service_level_advanced))	
					  $checked = 'checked="checked"';
					//elseif($this->params['action']=='superbill_print')
					//  continue;
					else 
					  $checked = '';
					  
					if($this->params['action']=='superbill_print')
					{
					  if($checked)
					    echo '<tr><td colspan=2>'.$advanced_level['description'] . ' </td> <td>  '.$advanced_level['code'].'</td></tr>';
					}
					else
					{ 					  
				    ?>
				    <tr>
					<td class="ignore"><label for="<?php echo $value; ?>" class="label_check_box"><input class="superbill_service_level_advanced_check" id="<?php echo $value; ?>" type="checkbox" <?php echo $checked; ?> value="<?php echo $value; ?>" /></label></td>
					<td><?php echo $advanced_level['description']; ?></td>
					<td><?php echo $advanced_level['code']; ?></td>
				   </tr>
				   <?php 
				   	}
				   } 
				   ?>
				     <?php for($i=0; $i<$txt_svc_codes; $i++) {  ?>
				         <tr> 
				           <td class="ignore"></td>
				           <td class="ignore"><input type="text" name="data[other_codes][<?php echo $i; ?>][description]" class="other_codes_name other_code_input" style="margin-bottom:0px;" value="<?php echo (isset($other_codes[$i]) && isset($other_codes[$i]['description']))? $other_codes[$i]['description'] : '' ; ?>"/></td>
				           <td class="ignore"><input type="text" name="data[other_codes][<?php echo $i; ?>][code]" class="other_codes_code other_code_input" style="margin-bottom:0px;" value="<?php echo (isset($other_codes[$i]) && isset($other_codes[$i]['code']))? $other_codes[$i]['code'] : ''; ?>"  /></td>
				         </tr>
				     <?php 
				     }
				      if($this->params['action']=='superbill_print') {
				         for($i=0; $i<$txt_svc_codes; $i++) { 
				            	if(isset($other_codes[$i]) && isset($other_codes[$i]['description']) && $other_codes[$i]['description']!='') {
				     ?>
				     	<tr><td class="ignore"></td><td><?php echo $other_codes[$i]['description']; ?></td><td><?php echo (isset($other_codes[$i]['code']))? $other_codes[$i]['code'] : ''; ?></td>
				     	</tr>
				     	<?php 
				     		} 
				     	 } 
				     } 			      	
				     ?>			   
				   </table>				
				</div>
				<br style="clear: both;" />	
                                   </div>
                                </td>
                            </tr>
                        </table>                        
                    </td>
                    <?php if($this->params['action']=='superbill_print'): ?>
                    <td style="width: 50%; vertical-align: top;">
                        <strong>Insurance Information:</strong>
                        <br />
                        <?php if (empty($insurance_data)): ?> 
                        Not entered
                        <?php else:?> 
                            <?php foreach($insurance_data as $i): ?>
                           <table style="margin-left: 20px;" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>Payer:</td><td><?php echo $i['PatientInsurance']['payer']; ?>  (Type: <?php echo $i['PatientInsurance']['priority']; ?>)       
                                </td>
                            </tr>
                            <tr>    
                                <td valign=top>Insured/Policy Holder:</td> <td>
                                <?php   $ins_rel='';
                                	foreach($relationships as $relationship_item)
                                	{
                                		if($i['PatientInsurance']['relationship'] == $relationship_item['EmdeonRelationship']['code'])
                                		{
                                			$ins_rel=$relationship_item['EmdeonRelationship']['description'];
                                			echo ' '.$ins_rel;
                                		}
                                	}
                                	  if($i['PatientInsurance']['insured_first_name'] || $i['PatientInsurance']['insured_address_1'])
                                	  {
                                	    print '<br>';
                                	    print !empty($i['PatientInsurance']['insured_first_name'])? $i['PatientInsurance']['insured_first_name'].' ':' ';
                                	    print !empty($i['PatientInsurance']['insured_last_name'])? $i['PatientInsurance']['insured_last_name'].' ':' ';
                                	    print !empty($i['PatientInsurance']['insured_sex'])? ' (Gender: '.$i['PatientInsurance']['insured_sex']. ')':' ';
                                	    print !empty($i['PatientInsurance']['insured_address_1'])? '<br>'.$i['PatientInsurance']['insured_address_1'].' ':' ';
                                	    print !empty($i['PatientInsurance']['insured_address_2'])? '<br>'.$i['PatientInsurance']['insured_address_2'].' ':' ';
                                	    print !empty($i['PatientInsurance']['insured_city'])? '<br>'.$i['PatientInsurance']['insured_city'].', ':' ';
                                	    print !empty($i['PatientInsurance']['insured_state'])? $i['PatientInsurance']['insured_state'].' ':' ';
                                	    print !empty($i['PatientInsurance']['insured_zip'])? $i['PatientInsurance']['insured_zip'].' ':' ';
                                	    print !empty($i['PatientInsurance']['insured_home_phone_number'])? '<br>'.$i['PatientInsurance']['insured_home_phone_number'].' ':' ';
                                	  }  
                     
                                ?></td>
                            </tr>                            
                            <tr>    
                                <td>Member/Policy #</td> <td><?php echo $i['PatientInsurance']['policy_number']; ?></td>
                            </tr>
                            <tr>
                                <td>Group Name/Number:</td><td><?php echo $i['PatientInsurance']['group_name']. ' ' . $i['PatientInsurance']['group_id']; ?></td>
                            </tr>  
                           </table>
                           <br />
                           <br />                          
                            <?php endforeach;?>

                        
                        <?php endif;?>
                    </td>
                    <?php endif;?>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td> </td>
    </tr>
    <tr>
        <td>
					&nbsp;
					<?php if (!$enabledTabs || in_array('Assessment', $enabledTabs) ): ?> 					
            <table cellpadding="0" cellspacing="0" class="small_table">
                <tr>
                    <th align=left>Assessment/Diagnosis</th>
                    <th width="20"><div class="hide_for_print"><img class="loading_swirl" id="superbill_loading_img_diagnosis" src="<?php echo $this->Session->webroot; ?>img/ajax_loaderback.gif" width="16" height="16" /></div></th>
                </tr>
                <?php if(count($assessments) > 0): $n=1; ?>
                <?php  foreach($assessments as $patient_assessment):  ?>
                    <?php if(count($patient_assessment) > 0): 
                                foreach($patient_assessment as $numberofAssessments): 
                                $patient_assessment2 = $numberofAssessments['diagnosis'];
								
								
								$cls = "";
								if($n % 2 == 0)
								{
									$cls = 'class="striped"';	
								}
                    ?>
                        <tr <?php echo $cls; ?>>
                            <td colspan="2" style="vertical-align:middle"><?php echo $n. ') ' . $this->element('superbill_checkbox', array('init_values' => $ignored_diagnosis, 'value' => $patient_assessment2, 'class' => 'superbill_diagnosis_check')); ?> 
                            <?php if ($numberofAssessments['comment'])
                                  {
                                        print '  [<i>'.$numberofAssessments['comment'].'</i>]';
                                  }
                            ?>
                            </td>
                        </tr>
                                <?php $n++; endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2"> None</td>
                    </tr>
                <?php endif; ?>
            </table>
					<?php endif;?> 
        </td>
    </tr>
    <tr>
        <td> </td>
    </tr>
    
    <tr>
        <td>
					&nbsp;
					<?php if (!$enabledTabs || in_array('POC', $enabledTabs) ): ?> 					
            <table cellpadding="0" cellspacing="0" class="small_table">
                <tr>
                    <th align=left>Point of Care </th>
                    <th width="20"><div class="hide_for_print"><img class="loading_swirl" id="superbill_loading_img_in_house" src="<?php echo $this->Session->webroot; ?>img/ajax_loaderback.gif" width="16" height="16" /></th></div>
                </tr>
                
                <tr>
                    <td colspan="2"><strong>Labs</strong></td>
                </tr>
                <?php if(count($in_house_labs) > 0): ?>
                <?php  for($i=0;$i<count($in_house_labs);$i++) 
                  {
                    $j = $i + 1;
					
					$cls = "";
					if($i % 2 == 1)
					{
						$cls = 'class="striped"';	
					}
                 ?>
                        <tr <?php echo $cls; ?>>
                            <td colspan="2" style="vertical-align:middle"><?php echo $j. ') ' . $this->element('superbill_checkbox', array('init_values' => $ignored_in_house_labs, 'value' => $in_house_labs[$i]['lab_test_name'], 'class' => 'superbill_in_house_labs_check')); 

                              if(!empty($in_house_labs[$i]['cpt_code']) || !empty($in_house_labs[$i]['cpt'])) {
                                $cpt = !empty( $in_house_labs[$i]['cpt'] )? $in_house_labs[$i]['cpt']: $in_house_labs[$i]['cpt_code'];
                                echo formatCPT($cpt);
                              }

                              if($in_house_labs[$i]['fee']) {
                                echo ' $'.$in_house_labs[$i]['fee']. ' ';
                                $total_fees = $total_fees + $in_house_labs[$i]['fee'];
                              }                    
                            if($in_house_labs[$i]['lab_reason'])
                                echo ' (Re: '. ucwords($in_house_labs[$i]['lab_reason']).')'; 
                            ?>
                            </td>
                        </tr>
                <? } ?>       
                <?php else: ?>
                    <tr>
                        <td colspan="2"> None</td>
                    </tr>
                <?php endif; ?>
                
                <tr>
                    <td colspan="2"><strong>Radiology</strong></td>
                </tr>
                <?php if(count($in_house_radiologies) > 0): ?>
                <?php
                  for($i=0;$i<count($in_house_radiologies);$i++) 
                  {
                    $k = $i +1;
					
					$cls = "";
					if($i % 2 == 1)
					{
						$cls = 'class="striped"';	
					}
                 ?>

                        <tr <?php echo $cls; ?>>
                            <td colspan="2" style="vertical-align:middle"><?php echo $k. ') ' .$this->element('superbill_checkbox', array('init_values' => $ignored_in_house_radiologies, 'value' => $in_house_radiologies[$i]['radiology_procedure_name'], 'class' => 'superbill_in_house_radiologies_check')); 
                           
                              if(!empty($in_house_radiologies[$i]['cpt_code']) || !empty($in_house_radiologies[$i]['cpt'])) {
                                $cpt = !empty( $in_house_radiologies[$i]['cpt'] )? $in_house_radiologies[$i]['cpt']: $in_house_radiologies[$i]['cpt_code'];
                                echo formatCPT($cpt);
                              } 
                              if($in_house_radiologies[$i]['fee']) {
                                echo ' $'.$in_house_radiologies[$i]['fee']. ' ';
                                $total_fees = $total_fees + $in_house_radiologies[$i]['fee'];
                              }                              
                               if($in_house_radiologies[$i]['radiology_reason'])
                                echo ' (Re: '. ucwords($in_house_radiologies[$i]['radiology_reason']).')'; 
                            ?>
                            </td>
                        </tr>
                 <?php } ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2"> None</td>
                    </tr>
                <?php endif; ?>
                
                <tr>
                    <td colspan="2"><strong>Procedures</strong></td>
                </tr>
                <?php if(count($in_house_procedures) > 0): ?>
                <?php
                  for($i=0;$i<count($in_house_procedures);$i++) 
                  {
                    $l = $i + 1 ;
					
					$cls = "";
					if($i % 2 == 1)
					{
						$cls = 'class="striped"';	
					}
                 ?>
                        <tr <?php echo $cls; ?>>
                            <td colspan="2" style="vertical-align:middle"><?php echo $l. ') ' . $this->element('superbill_checkbox', array('init_values' => $ignored_in_house_procedures, 'value' => $in_house_procedures[$i]['procedure_name'], 'class' => 'superbill_in_house_procedures_check')); 
                            
                              if(!empty($in_house_procedures[$i]['cpt_code']) || !empty($in_house_procedures[$i]['cpt'])) {
                                $cpt = !empty( $in_house_procedures[$i]['cpt'] )? $in_house_procedures[$i]['cpt']: $in_house_procedures[$i]['cpt_code'];
                                echo formatCPT($cpt);
                              }
                              if($in_house_procedures[$i]['modifier']) {
                                echo ' modifier(s): '.$in_house_procedures[$i]['modifier']. ' ';
                              }
                              if($in_house_procedures[$i]['fee']) {
                                echo '  $'.$in_house_procedures[$i]['fee']. ' ';
                                $total_fees = $total_fees + $in_house_procedures[$i]['fee'];
                              }   
                              
                              if($in_house_procedures[$i]['procedure_unit']) {
                                echo ' ('.$in_house_procedures[$i]['procedure_unit'].' units) ';
                              }
                                                                                       
                              if($in_house_procedures[$i]['procedure_reason'])
                                echo ' (Re: '. ucwords($in_house_procedures[$i]['procedure_reason']).')';                             
                            ?></td>
                        </tr>

                <?php } ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2"> None</td>
                    </tr>
                <?php endif; ?>
                
                <tr>
                    <td colspan="2"><strong>Immunizations</strong></td>
                </tr>
                <?php if(count($in_house_immunizations) > 0): ?>
               <?php
                  for($i=0;$i<count($in_house_immunizations);$i++) 
                  {
                    $m = $i + 1;
					
					$cls = "";
					if($i % 2 == 1)
					{
						$cls = 'class="striped"';	
					}
                 ?>
                        <tr <?php echo $cls; ?>>
                            <td colspan="2" style="vertical-align:middle"><?php echo $m. ') ' .$this->element('superbill_checkbox', array('init_values' => $ignored_in_house_immunizations, 'value' => $in_house_immunizations[$i]['vaccine_name'], 'class' => 'superbill_in_house_immunizations_check')); 
                            
                              if(!empty($in_house_immunizations[$i]['cpt_code']) || !empty($in_house_immunizations[$i]['cpt'])) {
                                $cpt = !empty( $in_house_immunizations[$i]['cpt'] )? $in_house_immunizations[$i]['cpt']: $in_house_immunizations[$i]['cpt_code'];
                                echo formatCPT($cpt);
                              }
                              if($in_house_immunizations[$i]['fee']) {
                                echo ' $'.$in_house_immunizations[$i]['fee']. ' ';
                                $total_fees = $total_fees + $in_house_immunizations[$i]['fee'];
                              }
                              if($in_house_immunizations[$i]['vaccine_reason'])
                                echo ' (Re: '. ucwords($in_house_immunizations[$i]['vaccine_reason']).')';     
                                                            
                            ?></td>
                        </tr>
  
                <?php } ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2"> None</td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <td colspan="2"><strong>Injections</strong></td>
                </tr>
                <?php  if(count($in_house_injections) > 0): ?>
               <?php
                  for($i=0;$i<count($in_house_injections);$i++) 
                  {
                    $m = $i + 1;
					
					$cls = "";
					if($i % 2 == 1)
					{
						$cls = 'class="striped"';	
					}
                 ?>
                        <tr <?php echo $cls; ?>>
                            <td colspan="2" style="vertical-align:middle"><?php echo $m. ') ' .$this->element('superbill_checkbox', array('init_values' => $ignored_in_house_injections, 'value' => $in_house_injections[$i]['injection_name'], 'class' => 'superbill_in_house_injections_check')); 

                              if($in_house_injections['rxnorm'][$i]) {
                                echo ' RxNorm/NDC: '.$in_house_injections['rxnorm'][$i].' ';
                              }                            
                              if(!empty($in_house_injections[$i]['cpt_code']) || !empty($in_house_injections[$i]['cpt'])) {
				$cpt = !empty( $in_house_injections[$i]['cpt'] )? $in_house_injections[$i]['cpt']: $in_house_injections[$i]['cpt_code'];
                                echo formatCPT($cpt);
                              }
                              if(!empty($in_house_injections[$i]['fee'])) {
                                echo ' $'.$in_house_injections[$i]['fee']. ' ';
                                $total_fees = $total_fees + $in_house_injections[$i]['fee'];
                              }
                              if(!empty($in_house_injections[$i]['injection_unit'])) {
                                echo ' ('.$in_house_injections[$i]['injection_unit']. ' units) ';
                                $total_fees = $total_fees + $in_house_injections[$i]['fee'];
                              }
                              if(!empty($in_house_injections[$i]['reason']))
                                echo ' (Re: '. ucwords($in_house_injections[$i]['reason']).')';     
                                                            
                            ?></td>
                        </tr>
  
                <?php } ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2"> None</td>
                    </tr>
                <?php endif; ?>
 
                
                <tr>
                    <td colspan="2"><strong>Meds</strong></td>
                </tr>
                <?php if(count($in_house_meds) > 0): ?>
               <?php
                  for($i=0;$i<count($in_house_meds);$i++) 
                  {
                    $o = $i + 1;
					
					$cls = "";
					if($i % 2 == 1)
					{
						$cls = 'class="striped"';	
					}
                 ?>
                        <tr <?php echo $cls; ?>>
                            <td colspan="2" style="vertical-align:middle"><?php echo $o. ') ' . $this->element('superbill_checkbox', array('init_values' => $ignored_in_house_meds, 'value' => $in_house_meds[$i]['drug'], 'class' => 'superbill_in_house_meds_check')); 
                         
                              if(!empty($in_house_meds[$i]['quantity'])) {
                                echo '  #'.$in_house_meds[$i]['quantity'];
                              }
   
                              if(!empty($in_house_meds[$i]['cpt_code']) || !empty($in_house_meds[$i]['cpt'])) {
                                $cpt = !empty( $in_house_meds[$i]['cpt'] )? $in_house_meds[$i]['cpt']: $in_house_meds[$i]['cpt_code'];
                                echo formatCPT($cpt);
                              }

                              if($in_house_meds[$i]['fee']) {
                                echo ' $'.$in_house_meds[$i]['fee']. ' ea ';
                                $total_fees = $total_fees + $in_house_meds[$i]['fee'];
                              }
                              if($in_house_meds[$i]['drug_reason'])
                                echo ' (Re: '. ucwords($in_house_meds[$i]['drug_reason']).')';  
                                                            
                            ?></td>
                        </tr>
                <?php } ?>
                <?php else: ?>

                    <tr>
                        <td colspan="2"> None</td>
                    </tr>
                <?php endif; ?>

               <tr>
                    <td colspan="2"><strong>Supplies</strong></td>
                </tr>
                <?php if(count($in_house_supplies) > 0): ?>
               <?php for($i=0;$i<count($in_house_supplies);$i++) 
                  {
                    $p = $i + 1;
					$cls = "";
					if($i % 2 == 1)
					{
						$cls = 'class="striped"';	
					}
                 ?>
                        <tr <?php echo $cls; ?>>
                            <td colspan="2" style="vertical-align:middle"><?php echo $p. ') ' .$this->element('superbill_checkbox', array('init_values' => $ignored_in_house_supplies, 'value' => $in_house_supplies[$i]['supply_name'], 'class' => 'superbill_in_house_supplies_check')); 
                              if(!empty($in_house_supplies[$i]['cpt_code']) || !empty($in_house_supplies[$i]['cpt'])) {
                                $cpt = !empty( $in_house_supplies[$i]['cpt'] )? $in_house_supplies[$i]['cpt']: $in_house_supplies[$i]['cpt_code'];
                                echo formatCPT($cpt);
                              }
                              if(!empty($in_house_supplies[$i]['fee'])) {
                                echo ' $'.$in_house_supplies[$i]['fee']. ' ';
                                $total_fees = $total_fees + $in_house_supplies[$i]['fee'];
                              }
                            ?></td>
                        </tr>
                <?php } ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2"> None</td>
                    </tr>
                <?php endif; ?>
                                
            </table>
						<?php endif; ?> 
        </td>
    </tr>
    <tr>
        <td> </td>
    </tr>
    
    <tr>
        <td>
					&nbsp;
						<?php if (!$enabledTabs || in_array('Plan', $enabledTabs) ): ?> 
            <table cellpadding="0" cellspacing="0" class="small_table">
                <tr>
                    <th align=left>Plan</th>
                    <th width="20"><div class="hide_for_print"><img class="loading_swirl" id="superbill_loading_img_plan" src="<?php echo $this->Session->webroot; ?>img/ajax_loaderback.gif" width="16" height="16" /></div></th>
                </tr>
                
                <tr>
                    <td colspan="2"><strong>Outside Labs</strong></td>
                </tr>
                <?php if(count($labs) > 0): $q=1;?>
                <?php foreach($labs as $lab): ?>
                    <?php if(strlen(trim($lab)) > 0): 
					
					$cls = "";
					if($q % 2 == 0)
					{
						$cls = 'class="striped"';	
					}
					
					?>
                    
                    
                        <tr <?php echo $cls; ?>>
                            <td colspan="2" style="vertical-align:middle"><?php echo $q. ') ' .$this->element('superbill_checkbox', array('init_values' => $ignored_outside_labs, 'value' => $lab, 'class' => 'superbill_labs_check')); ?></td>
                        </tr>
                    <?php $q++; endif; ?>
                <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2"> None</td>
                    </tr>
                <?php endif; ?>
                
                
                <tr>
                    <td colspan="2"><strong>Radiology</strong></td>
                </tr>
                <?php if(count($radiologies) > 0): $r=1; ?>
                <?php foreach($radiologies as $radiology): ?>
                    <?php if(strlen(trim($radiology)) > 0): 
					$cls = "";
					if($r % 2 == 0)
					{
						$cls = 'class="striped"';	
					}
					?>
                        <tr <?php echo $cls; ?>>
                            <td colspan="2" style="vertical-align:middle"><?php echo $r. ') ' .$this->element('superbill_checkbox', array('init_values' => $ignored_radiologies, 'value' => $radiology, 'class' => 'superbill_radiologies_check')); ?></td>
                        </tr>
                    <?php $r++; endif; ?>
                <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2"> None</td>
                    </tr>
                <?php endif; ?>
                
                
                <tr>
                    <td colspan="2"><strong>Procedures</strong></td>
                </tr>
                <?php if(count($procedures) > 0): $s=1;?>
                <?php foreach($procedures as $procedure): ?>
                    <?php if(strlen(trim($procedure)) > 0): 
					
					$cls = "";
					if($s % 2 == 0)
					{
						$cls = 'class="striped"';	
					}
					
					?>
                        <tr <?php echo $cls; ?>>
                            <td colspan="2" style="vertical-align:middle"><?php echo $s. ') ' .$this->element('superbill_checkbox', array('init_values' => $ignored_procedures, 'value' => $procedure, 'class' => 'superbill_procedures_check')); ?></td>
                        </tr>
                    <?php $s++; endif; ?>
                <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2"> None</td>
                    </tr>
                <?php endif; ?>
                
                <tr>
                    <td colspan="2"><strong>Follow Up</strong></td>
                </tr>
                <?php if((strlen($return_time) == 0 || strlen($return_period) == 0) && strlen($visit_summary_given) == 0): ?>
                <tr>
                    <td colspan="2"> None</td>
                </tr>
                <?php else: ?>
                <tr>
                        <td colspan="2">
                        <?php if(strlen($return_time) > 0 && strlen($return_period) > 0): ?>
                        Return In: <?php echo $return_time; ?> <?php echo $return_period; ?> and/or if there is no improvement.
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>

                        <td colspan="2">
                        <?php if(strlen($visit_summary_given) > 0): ?>
                        Visit Summary Given: <?php echo $visit_summary_given; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
					<?php endif; ?>
        </td>
    </tr>
    <tr>
        <td> </td>
    </tr>
    
    <tr>
        <td> <br />                
           <table cellpadding="0" cellspacing="0" class="small_table">
                <tr>
                    <th align=left colspan="2">Options</th>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top:7px"><strong>Comments:</strong></td>
                </tr>
                <tr>
                    <td colspan="2"><textarea cols="20" name="data[EncounterSuperbill][superbill_comments]" id="superbill_comments"><?php echo $superbill_comments;?></textarea></td>
                </tr>
                
<?php if($this->params['action'] != 'superbill_print'): ?>    

    		<tr>
        	  <td style="width:50%">
            <?php
                   if (sizeof($Attendings) > 0)
                   {
                   ?>
                            <div class="hpi_lbl" id="azure" name="Forward copy of this Encounter<br> to a Physician for review?" style="text-align:left;float:left; "><strong>Forward Visit Summary to:</strong> <?php echo $html->image('help.png'); ?> </div>
                             <select style="margin-left:10px" name="routing" id="routing">
                            <option value="" selected></option>
                            <?php
                            foreach ($Attendings as $Attendings2):
                                echo "<option value='".$Attendings2['UserAccount']['user_id']."'>".$Attendings2['UserAccount']['firstname']." ".$Attendings2['UserAccount']['lastname']."</option>";
                            endforeach;
                            ?>
                            </select>
				<a id="forward" class="btn" style="float:none;">Send</a>
				<span id="forwarded-msg" class="notice"> Copy sent. </span>
		  <?php
		  } 
		 ?>
		   </td>
		  <?php if ( $pr['PracticeSetting']['kareo_status'] ) { // allow to set kareo supervising provider and overwride rendering provider ?>
		   <td><div class="hpi_lbl" id="azure" name="If desired you can modify and set the <br>Supervising Provider for billing purposes" style="text-align:left;float:left; "><strong>Supervising Provider:</strong> <?php echo $html->image('help.png'); ?> </div>
			<?php	if ($encounter_status != 'Closed') { ?>
			<select name="data[EncounterSuperbill][supervising_provider_id]" id="supervising_provider_id" style="margin-left:10px"> 
				<option value="0" selected>None</option>
				<?php
				foreach ($Attendings as $Attending) {
				 	if( in_array($Attending['UserAccount']['role_id'], $supervising_providers) ) {
					  print '<option value="'.$Attending['UserAccount']['user_id'].'" ';
					     if (isset($supervising_provider->user_id) && ($supervising_provider->user_id == $Attending['UserAccount']['user_id'])) echo " selected ";

					  print '>'.$Attending['UserAccount']['firstname']." ".$Attending['UserAccount']['lastname'].'</option>';
					}
				}
				?>
			</select> 
			 <?php } else { 
				 echo '  <span style="margin-left:10px; font-weight:bold">'.((isset($supervising_provider->full_name)) ? $supervising_provider->full_name:'None Defined').'</span>';
				} ?>

		    </td>
		    <?php } ?>

		  </tr>
	        </table>
	  </td>
	  </tr>
	 <tr>
	   <td>                    
                    <?php 

            if($encounter_group_defined)
            {

				if ($encounter_status != 'Closed'): ?>
					<?php  if(!empty($kareo_con_err) && $pr['PracticeSetting']['kareo_status'] ){ ?>
					<table>
						<tr>
			     			<td colspan=3 style="padding:0 0 15px 0"><span class='error'>Warning: Kareo appears to be down. Please do not lock the encounter right now, instead try again later so your data posts sucessfully</span></td>
						</tr>
					</table>	
		    		<?php } ?>
                    <table style="padding:15px 0 0 5px;">
			<?php

				$define_supervising_provider=(in_array($provider_info['UserAccount']['role_id'], $supervising_providers))? true:false;
				if(!$define_supervising_provider)
					$define_supervising_provider = (isset($supervising_provider->user_id))? true:false;

			    if(empty($_SESSION['UserAccount']['provider_pin']) && $encounter_status != 'Closed' ) { ?>
                        <tr>
			     <td colspan=3 style="padding:0 0 15px 0"><span class='error'>WARNING: You have not yet set a PIN. <a href="/preferences/system_settings">Click here</a> or go to Preferences -> System Settings to set one.</span></td>
			</tr>
                        <?php } else if (!$define_supervising_provider && $pr['PracticeSetting']['kareo_status'] && $encounter_status != 'Closed') { ?>
                        <tr>
                             <td colspan=3 style="padding:0 0 15px 0"><span class='error'>WARNING: You should define a Supervising Provider above before locking note.</span></td>
                        </tr>

			<?php } ?>
			<tr>

                        <?php	$pluslock='/Lock Record';
				   // Allow option to post to Billing software
                                if( $pr['PracticeSetting']['kareo_status'] || $pr['PracticeSetting']['hl7_engine'] ) {
                                     $pmname=($pr['PracticeSetting']['kareo_status']) ? 'Kareo':$pr['PracticeSetting']['hl7_engine'];
                                     $pmtext = "- OR -  <a id='bt_charges' name='bt_charges'  class='btn' style='float:none;'>Post Charges Only</a>".
						" - OR - <a id='lock_only' name='lock_only'  class='btn' style='float:none;'>Close Only</a>   ";
					$pluslock=' + Post Charges';
                              }       
			?>
                            <td>Provider PIN:</td>
                            <td> <input size=10 type='text' name='data[pin]' id='pin'></td>
                            <td> <a id='bt_superbill' name='bt_superbill'  class="btn" style="float:none;">Close<?php echo $pluslock;?></a> <?php echo (isset($pmtext))?$pmtext:''; ?>	<span id="imgLoading" class="hide_for_print" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?> </span> 
			    <span id='super_bill_status' class='notice2' style="display: none;"></span>
			    <span id='super_bill_error' class='error'></span>
			 </td>
                        </tr>
                    </table>
		    <?php else: ?>
			<div><b>Provider: <?php echo $provider_info['UserAccount']['full_name']. ' '. $provider_info['UserAccount']['degree']?></b></div>
                    <?php endif;?> 
                    <?php
                
            }
            else
            {
                echo "WARNING: No Encounter Lock User Group Function Have Been Defined. Cannot Lock Encounter.";
            }
            ?>
        </td>
    </tr>
<? endif; ?>
</table>
</form>

<script type="text/javascript">
$(function(){
		var 
			$encounterDateLink = 		$('#encounter_date_link'),
			$encounterDateField =		$('#encounter_date_field'),
			$dateLoading = $('#date_loading').hide(),
			$voidBtn = $('#void-btn'),
			$voidDialog = $('#void-dialog').slideUp();
		;

		$encounterDateLink.click(function(evt){
				evt.preventDefault();
				$encounterDateField.datepicker('show');
			});
	
		$encounterDateField.datepicker({
						changeMonth: true,
						changeYear: true,
						showOn: false,
						showButtonPanel: true,
						dateFormat: "<?php if($global_date_format=='d/m/Y') { echo 'dd/mm/yy'; } else if($global_date_format=='Y/m/d') { echo 'yy/mm/dd'; } else{ echo 'mm/dd/yy'; } ?>",
						yearRange: '1900:2050',
						defaultDate: '<?php echo __date($global_date_format, strtotime($encounter_date)); ?>',
						onSelect: function(dateText, inst)
						{
							
							var dateFormat = $(this).datepicker('option', 'dateFormat');
							
							var dDate = $.datepicker.parseDate(dateFormat, dateText);
							
							var isoDate = $.datepicker.formatDate($.datepicker.ISO_8601, dDate);
							
							
							$dateLoading.show();
							$.post($encounterDateLink.attr('rel'),{
								encounter_date: isoDate
							}, function(){
								$encounterDateLink.text(dateText);
								$dateLoading.hide();
							}); 
							
						}				
			})

		$('button.ui-datepicker-current').die('click.today');
		$('button.ui-datepicker-current').live('click.today', function() {
			var input = $.datepicker._curInst.input;

			input.datepicker('setDate', new Date());

			var dateFormat = input.datepicker('option', 'dateFormat');
			var dateText = $.datepicker.formatDate(dateFormat, input.datepicker('getDate'));
			var isoDate = $.datepicker.formatDate($.datepicker.ISO_8601, input.datepicker('getDate'));

			$dateLoading.show();
			$.post($encounterDateLink.attr('rel'),{
				encounter_date: isoDate
			}, function(){
				$encounterDateLink.text(dateText);
				$dateLoading.hide();
				input.datepicker('hide');
			}); 
		});	
		$('#confirm_void_wrap')
			.find('label')
				.width(120)
				.end()
			.buttonset();
		
		$('input[name="confirm_void"]')
			.unbind('click')
			.click(function(evt){
				var doVoid = parseInt($(this).val(), 10);

				if (doVoid) {
					$.post($voidBtn.attr('href'), {
						'void': 1
					}, function(){
						
						$voidDialog
							.html('<div class="error">Encounter marked as void. Redirecting you to the encounters page ...</div> <br />')
						
						setTimeout(function(){
							window.location.href = '<?php echo $this->Html->url(array('controller' => 'encounters', 'action' => 'index')); ?>';
						}, 3000);
					});

				} else {
					evt.preventDefault();
					$voidDialog.slideUp();
				}

			});
		

			
		$voidBtn.click(function(evt){
			evt.preventDefault();
			
			$voidDialog.slideDown();

			<?php if($disableVoid) { ?>
			setTimeout("$('#void-dialog').slideUp('slow');",7000);
			<?php } ?>
		});
});
</script>

