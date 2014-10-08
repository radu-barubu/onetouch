<?php

?>
<script language="javascript" type="text/javascript">
$(document).ready(function()
	{
		
		<?php if (!$isMobile): ?>
			$('#last_name').focus();		
		<?php endif;?> 
		    
		    if ($('#show_advanced').attr('checked')) 
				{
					$('#new_advanced_area').show();

				}
		    
		    $('#show_advanced').click(function()
		    {        
				if ($('#show_advanced').attr('checked')) 
				{
					$('#new_advanced_area').slideDown("slow");

				}
				else 
				{
				$('#new_advanced_area').slideUp("slow",function()
				{
					$('#address').val("");
					$('#city').val("");
					$('#zipcode').val("");
					$('#home_phone').val("");
					$('#state').val('all');
					$('#gender').val('all');
					$('#last_location').val('Select Location');
					$('#status_inactive').removeAttr('checked');
					$('#status_deceased').removeAttr('checked');
					$('#status_suspended').removeAttr('checked');
					$('#status_deleted').removeAttr('checked');
					$('#status_pending').prop('checked', true);
					$('#status_active').prop('checked', true);
					$('#status_new').prop('checked', true);
				});
				
				}

			});
		
		$("#last_name,#first_name,#dob,#custom_patient_identifier").keyup(function(e)
		{
			if(e.keyCode == 13)
			{
				initiateSearch('');
			}
		});
     
		$("#ssn").keyup(function(e)
        {
            if ($("#ssn").val().search("x") < 0)
            {
               if(e.keyCode == 13)
                {
                    initiateSearch('SSN');
                }
            }
        });
		

		$("#add_patient").click(function() 
		{       
				var last_name = $('#last_name').val(), first_name = $('#first_name').val(), ssn = $('#ssn').val(), dob = $('#dob').val(), add_patient_url = '<?php echo $html->url(array('action' => 'index', 'task' => 'addnew')); ?>';
				if(last_name || first_name || ssn || dob) { 
						var formObj = $('#frm');
						formObj.append('<input type="hidden" name="data[search_chart]" value="1">');
						formObj.attr('action', add_patient_url); //change the form action
						formObj.attr('method', 'POST');
						formObj.submit();
				} else {
						document.location.href = add_patient_url; // if no value in input, redirect normaly to add patient 
				}
		});

	});
	
	function getSearch()
	{
		var first_name = $.trim($('#first_name').val());
		var last_name = $.trim($('#last_name').val());
		var ssn = $.trim($('#ssn').val());
		var dob = $.trim($('#dob').val());
		var save_session = '<?php echo $html->url(array('action' => 'index', 'task' => 'save_search_data')); ?>';
		var	add_patient_url = '<?php echo $html->url(array('action' => 'index', 'task' => 'addnew')); ?>';
		if(first_name || last_name || ssn || dob)
		{
			$.post(save_session,$('#frm').serialize(),function(data) {
				window.location.href = add_patient_url;
			});
		}
		else
		{
			window.location.href = add_patient_url;
		}
		
		
	}
	
	function convertSearchResultLink(obj)
	{
		var href = $(obj).attr('href');
		$(obj).attr('href', 'javascript:void(0);');
		$(obj).click(function()
		{
			loadSearchResult(href);
		});
	}
	
	function loadSearchResult(url)
	{
		$("#search_charts_results_table").css("cursor", "wait");
		
		$.post(
			url, 
			$('#frm').serialize(), 
			function(html)
			{
				$("#search_charts_results_table").css("cursor", "");
				$('#table_loading').hide();
				$("#search_result_area").show();
				
				$("#search_result_area").html(html);
				
				$("#search_charts_results_table tr:nth-child(odd)").addClass("striped");
				
				$('#search_result_area a.ajax').each(function()
				{
					convertSearchResultLink(this);
				});
				
				$('#search_result_area .paging a').each(function()
				{
					convertSearchResultLink(this);
				});
				
				$('#search_charts_results_table tr td').not('#search_charts_results_table tr td.ignore').not('#search_charts_results_table tr:first td').each(function()
				{
					$(this).click(function(e)
					{
						if($(e.target).hasClass('quick-visit') || e.target.nodeName == 'OPTION' || e.target.nodeName == 'option') 
							return false;
						var edit_url = $(this).parent().attr("editlinkajax");
				
						if (typeof edit_url  != "undefined") 
						{
							window.location = edit_url;
						}
					});
					
					$(this).css("cursor", "pointer");
				});
				
				$('.quick_visit_link').each(function()
				{
					var patient_status = $(this).attr("status");
					var override = $(this).attr("override");
					var uniqueid = $(this).attr("id");
					
					if(patient_status == 'Deceased')
					{
						if($(this).hasClass("image_link"))
						{
							$(this).removeAttr("onclick");
							$(this).unbind("click");
						}
					}
				});
				
				$('.quick_visit_link').click(function(e)
				{
					var patient_status = $(this).attr("status");
					var override = $(this).attr("override");
					var uniqueid = $(this).attr("id");
					
					if(patient_status == 'Deceased')
					{
						if(override == "false")
						{
							e.preventDefault();	
							
							$('#location_span').html('');
							
							//get parent tr
							var parent_tr = $(this).parents('tr');
							var patient_name = $.trim($('td:nth-child(1)', parent_tr).text());
							var patient_mrn = $.trim($('td:nth-child(3)', parent_tr).text());
							var patient_dob = $.trim($('td:nth-child(5)', parent_tr).text());
							$('#patient_deceased_name').html(patient_name);
							$('#patient_deceased_mrn').html(patient_mrn);
							$('#patient_deceased_dob').html(patient_dob);
							$('#patient_deceased_status').html(patient_status);
							$('#patient_deceased_uniqueid').html(uniqueid);	
							
							if($(this).hasClass("image_link"))
							{
								var patient_id = $(this).attr("patient_id");
								show_locations(patient_id, 'location_span');
								
								$('select', $('#location_span')).removeAttr('onchange').unbind('change');
								
								$('#dialog_location_field').show();
							}
							else
							{
								$('#dialog_location_field').hide();
							}
							
							$("#deceased_msg_box").dialog("open");
						}
						else
						{
							//do nothing - continue click
						}
					}
				});
			}
		);
	}
	
    function initiateSearch(auto)
    {
        $('#required_error').hide();
        $("#search_result_area").hide();

				if (Admin.dateIsInFuture('#dob')) {
            $('#required_error').show();
            $('#required_error').html('DOB is in the future');
					
				} else if ($('#last_name').val() == "" && $('#first_name').val() == "" && $('#dob').val() == "" && $('#ssn').val() == "" && auto == "" && $('#location_id').val()=="" && !$('.p_status:checked').length)
        {
            $('#required_error').show();
            $('#required_error').html('Please refine the search by entering Last Name, First Name, SSN, Patient Status and/or DOB. Partial names are accepted');
        }
		/*else if(($('#last_name').val() != "" || $('#first_name').val() != "" || $('#dob').val() != "" || $('#ssn').val() != "" || auto != "" || $('#location_id').val()!= "") && (!$('.p_status:checked').length))
        {
            $('#required_error').show();
            $('#required_error').html('Please Select Patient Status');
        }*/
		 
        else
        {
            // check for ssn if populated
			// and being SSN a main key index 
			if( $('#ssn').val() ) { auto = 'SSN' };
            $('#table_loading').show();
            if (auto)
            {
                loadSearchResult('<?php echo $html->url(array('action' => 'search_charts_view', 'auto' => 'ssn')); ?>');
            }
            else
            {
                loadSearchResult('<?php echo $html->url(array('action' => 'search_charts_view')); ?>');
            }
        }
    }
	var patientId;
	function show_locations(patient, spanId)
	{
		patientId = patient;
		<?php if(count($locations) > 1) { ?>	
		$('#'+spanId).html($('#location_list').html());	
		<?php } else { ?>
		var locationId = $('#location').val()
		quick_encounter(locationId);
		<?php } ?>
	}
	
	function quick_encounter(locationId)
	{
		if(locationId != "")
		{
			document.location.href = '<?php echo $html->url(array('controller' =>'encounters', 'action' =>'quick_encounter')); ?>/patient_id:'+patientId+'/location_id:'+locationId;
		}
	}
</script>

<div id="deceased_msg_box" title="Deceased Patient" style="display: none;">
	<span id="patient_deceased_uniqueid" style="display: none;"></span>
	<p class="error">The selected patient is already deceased.</p>
    <p>&nbsp;</p>
    <p>
    	<form>
            <table border="0" cellspacing="0" cellpadding="0" class="form">
                <tr>
                    <td width="70"><label>Patient:</label></td>
                    <td id="patient_deceased_name"></td>
                </tr>
                <tr>
                    <td><label>MRN:</label></td>
                    <td id="patient_deceased_mrn"></td>
                </tr>
                <tr>
                    <td><label>DOB:</label></td>
                    <td id="patient_deceased_dob"></td>
                </tr>
                <tr>
                    <td><label>Status:</label></td>
                    <td id="patient_deceased_status"></td>
                </tr>
                <tr style="display: none;" id="dialog_location_field">
                    <td><label>Location:</label></td>
                    <td id="location_span"></td>
                </tr>
            </table>
        </form>
    </p>
</div>

<script language="javascript" type="text/javascript">
	$(function() {
		$("#dialog:ui-dialog").dialog("destroy");
	
		$("#deceased_msg_box").dialog({
			width: 450,
			modal: true,
			resizable: false,
			draggable: false,
			autoOpen: false,
			buttons: {
				'Create Visit Anyway': function(){
					var current_uniqueid = $('#patient_deceased_uniqueid').html();
					$('#'+current_uniqueid).attr("override", "true");
					
					if($('#'+current_uniqueid).hasClass("normal_link"))
					{
						window.location = $('#'+current_uniqueid).attr('href');
					}
					else
					{
						if($('#location_span select').val() != "")
						{
							quick_encounter($('#location_span select').val());	
						}
						else
						{
							$('#location_span select').addClass("error");
							$('#location_span select').after('<div class="error" id="deceased_location_error">This field is required.</div>');
						}
					}
				},
				'Cancel': function(){
					
					$(this).dialog('close');
				}
			}
		});
	});
</script>

<div class="error" id="required_error" style="display: none;"></div>
<div style="overflow: hidden;">
    <h2>Search Charts</h2>
    <form id="frm">
        <table cellpadding="0" cellspacing="0" class="form">
            <tr>
                <td width="120">Last Name:</td>
              <td><input type="text" name="data[last_name]" id="last_name" style="width:200px;" /></td>
                <td width="40">&nbsp;</td>
                <td width="140">First Name:</td>
              <td><input type="text" name="data[first_name]" id="first_name" style="width:200px;" /></td>
                <td>&nbsp;</td>
               <!-- <td><span style="margin-left: 10px">Status:</span></td>&nbsp;&nbsp;
			
				
                <td>
                <span style="margin-left: 10px"><?php  
                $ptoptions = array( "New and Established"=>"New & Established", "Established"=>"Established", "New"=>"New","Inactive"=>"Inactive", "Deceased"=>"Deceased", "Suspended"=>"Suspended"); 
                echo $this->element('dropdown',
		array(
		'id'=> 'data[patient_status]', // id
		'data' => $ptoptions,  // associate array with supplied data
		'selected'=> "New and Established") ///key -  prints three as  selected value
		);
                ?></span>
              </td>-->
            </tr>
            <tr>
                <td style="vertical-align:top; padding-top: 3px;">SSN:</td>
                <td style="vertical-align:top; padding-top: 0px;"><input type="text" name="data[ssn]" id="ssn" style="width:200px;" class="ssn" /></td>
                <td>&nbsp;</td>
                <td style="vertical-align:top; padding-top: 3px;">DOB:</td>
                <td><?php echo $this->element("date", array('name' => 'data[dob]', 'id' => 'dob', 'value' => '', 'required' => false, 'width' => "175")); ?></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            
            <tr>
            	<td style="vertical-align:top; padding-top: 3px;">MRN:</td>
                <td style="vertical-align:top; padding-top: 0px;"><input type="text" name="data[mrn]" id="ssn" style="width:200px;" class="mrn" /></td>
                <td>&nbsp;</td>
            	<td style="vertical-align:top; padding-top: 3px;">Custom Patient ID: </td>
                <td style="vertical-align:top; padding-top: 0px;"><input type="text" name="data[custom_patient_identifier]" id="custom_patient_identifier" style="width:200px;" class="custom_patient_identifier" /></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                 <td><span style="margin-left:55px"><label for="show_advanced" class="label_check_box"><input type="checkbox" name="show_advanced" id="show_advanced">&nbsp;Advanced</label></span></td>
            </tr>
            </table>
			
        </table>
        <div id="new_advanced_area" style="display:none;">
		<table style='margin-top:10px;' cellpadding="0" cellspacing="0" class="form">
		<tr>
			<td width="120">Address:</td>
            <td><input type="text" id="address" style="width:200px;" name="data[address1]" /></td>
			<td width="40">&nbsp;</td>
			<td width="140">City:</td>
            <td><input type="text" id="city" name="data[city]" style="width:200px;"/></td>
            <td>&nbsp;</td>
        </tr>
        <tr>
			<td style="vertical-align:top; padding-top: 3px;">State:</td>
            <td style="vertical-align:top; padding-top: 0px;"> 
				<select id="state" name="data[state]" style="width:212px;">
                    <option value="all" selected="selected">All</option>
                    <?php
                
						foreach($StateCode as $state_item)
						{
					?>
						<option  value="<?php echo $state_item['StateCode']['state']; ?>"><?php echo $state_item['StateCode']['fullname']; ?></option>
                    <?php
					}

					?>
                </select>
            
            </td>
            <td>&nbsp;</td>
            <td style="vertical-align:top; padding-top: 3px;">Zip Code:</td>
            <td style="vertical-align:top; padding-top: 0px;"><input type="text" id="zipcode" name="data[zipcode]" style="width:200px;"/></td>
			<td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
		</tr>
		
		<tr>
			
            <td style="vertical-align:top; padding-top: 3px;">Gender:</td>
            <td style="vertical-align:top; padding-top: 0px;">
				<select id="gender" name="data[gender]" style="width:212px;">
					<option value="all" selected="selected">All</option>
					<option value="M">Male</option>
					<option value="F">Female</option>
				</select>
            </td>
            <td>&nbsp;</td>
		<?php if(count($locations) > 1) { ?>
			
                <td style="vertical-align:top; padding-top: 3px;">By Last Location:</td>
                <td style="vertical-align:top; padding-top: 0px;">
				<?php echo $form->input('location_id', array('label'=>false, 'options' => $locations, 'empty' => 'Select Location','id'=>'last_location')); ?>
				</td>
                <td>&nbsp;</td>
                <td style="vertical-align:top; padding-top: 3px;">&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            
			<?php } ?>
			</tr>
			<tr>
			<td style="vertical-align:top; padding-top: 3px;">Home Phone:</td>
            <td style="vertical-align:top; padding-top: 0px;">
				<input type="text" name="data[home_phone]" id="home_phone" class="phone areacode" style="width:200px;" />
            </td>
            <td>&nbsp;</td>
			</tr>
			</table>
			<table>
		    <tr>
			    <td style="vertical-align:top; padding-top: 3px;">Patients Status:</td>
				<?php $statusList = PatientDemographic::getStatusList(); ?> 
                <?php foreach ($statusList as $s): ?> 
                	<?php
					$checked = '';
					
					if($s == 'Active' || $s == 'Pending' || $s == 'New')
					{
					
						 $checked = 'checked="checked"';
					}
					?>
                	<?php $_s = strtolower($s); ?>
                	<td style="vertical-align:top; padding-top: 0px;"><span style="margin-left: 20px"><label for="status_<?php echo $_s; ?>" class="label_check_box"><input type="checkbox" name="data[status_<?php echo $_s; ?>]" id="status_<?php echo $_s; ?>" value="<?php echo $s; ?>" <?php echo $checked; ?> class="p_status" />&nbsp;<?php echo $s; ?></label></span></td>
                <?php endforeach;?>
	         </tr>
			</table>
			</div>
    </form>

    <div class="actions">
        <ul>
            <li><a href="javascript: void(0);" onclick="initiateSearch('');">Search</a></li>
            <li>
            <a href="javascript: void(0);" onclick="getSearch();">Add New Patient</a>
          </li>
        </ul>
    </div>
    <table cellpadding="0" cellspacing="0" id="table_loading" width="100%" style="display: none;">
        <tr>
            <td align="center">
                <?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?>
            </td>
        </tr>
    </table>
    <div id="search_result_area"></div>
	<div id="location_list" style="display:none">
		<?php 
			if(count($locations) > 1) { 
				echo $form->input('location_list', array('class' => 'quick-visit', 'label'=>false, 'options' => $locations, 'empty' => 'Select Location', 'id' => 'location', 'onchange' => 'quick_encounter(this.value)'));
			} else {
				echo $form->input('location_list', array('class' => 'quick-visit', 'label'=>false, 'options' => $locations, 'id' => 'location'));
			}
		?>
	</div>
</div>
<script type="text/javascript">
$(function(){
	$('#dob').datepicker('option', 'maxDate', '+0d');
});
</script>
