<?php
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$page_access = $this->QuickAcl->getAccessType("encounters", "point_of_care");
echo $this->element("enable_acl_read", array('page_access' => $page_access));
echo $this->Html->css('online_form_builder.css?' . time());
echo $this->Html->script('jquery/Plugins/jsignature/jSignature.js?'.time());



	$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
	$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/'; 
	if(isset($ProcedureItem))
	{
	   extract($ProcedureItem);
	}
	/*if(isset($ProcedureItem1))
	{
	   extract($ProcedureItem1);
	}*/
	
	$hours = __date("H", strtotime($procedure_date_performed));
	$minutes = __date("i", strtotime($procedure_date_performed));
	
	?>
	<script language="javascript" type="text/javascript">
	function showNow()
	{
		var currentTime = new Date();
		var hours = currentTime.getHours();
		var minutes = currentTime.getMinutes();
	
		if (minutes < 10)
			minutes = "0" + minutes;
	
		var time = hours + ":" + minutes ;
		var val=document.getElementById('procedure_time').value=time;
		updateProceduresDate();
	}
	
	function updateProceduresDate()
	{
		updateProceduresData('procedure_date_performed', $('#procedure_date_performed').val())
	}
	
	function updateProceduresData(field_id, field_val) {
                var point_of_care_id = $("#point_of_care_id").val();
		$.post(
                    '<?php echo $this->Session->webroot; ?>encounters/in_house_work_procedures_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', 
                    {
                        'data[submitted][id]': field_id,
                        'data[submitted][value]': field_val,
                        'data[submitted][time]': $('#procedure_time').val(),
                        'point_of_care_id': point_of_care_id
                    }, 
		function(data){
		$('#frmInHouseWorkProcedure').validate().form();
		}
		);
	}
	$(document).ready(function()
	{
	$('textarea').autogrow();
	
        $("#frmInHouseWorkProcedure").validate(
        {
            errorElement: "div",
            submitHandler: function(form) 
            {
                $('#frmPatientMedicationList').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmInHouseWorkProcedure').serialize(), 
                    function(data)
                    {
                    },
                    'json'
                );
            }
        });
	
	    $('#frmInHouseWorkProcedure').validate().form();
	    <?php 
	    $total_providers=count($users);
        if($total_providers== 1)
        {?>
		var ordered_by_id = $("#ordered_by_id").val();
		updateProceduresData('ordered_by_id', ordered_by_id);
		<?php } ?>
		
		$("#procedure_name").autocomplete(['EKG [93000]', 'Holter - 24 hrs [93224]', 'Inhalation TX [94640]', 'Stress Test [93015]', 'Pellet Implantation [11980]'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$("#procedure_body_site").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		$("#procedure_reason").autocomplete('<?php echo $autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		
		$("#cpt").autocomplete('<?php echo $html->url(array('task' => 'load_autocomplete', 'action' => 'cpt4')); ?>', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$("#cpt").result(function(event, data, formatted)
		{
			updateProceduresData('cpt_code', data[1]);
			$("#cpt_code").val(data[1]);
		});
		
		
		$("#procedure_administered_by").autocomplete('<?php echo $this->Html->url(array('controller' => 'schedule', 'action' => 'provider_autocomplete')); ?>', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: true,
			scrollHeight: 200
		});		
		
		<?php echo $this->element('dragon_voice'); ?>
      
     $('#procedure-macros').macros({
       target: '#procedure_details'
     }); 
	});
	/*$(document).ready(function()
	{   
		initCurrentTabEvents('lab_records_area');

		$('.in_house_work_submenuitem').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoad").show();
			loadTab($(this),$(this).attr('url'));
		});

		$('#previousRecordsbtn').click(function()
		{
		    $("#sub_tab_table").css('display', 'none');
			$(".tab_area").html('');
			$("#imgLoad").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'encounters', 'action' => 'poc_previous_records', 'encounter_id' => $encounter_id)); ?>");
		});
	});  */
  <?php if ($admin_form): ?>
        $(function(){

          $('.form-radio-wrap').buttonset();

          $('.form_signature').each(function(){
            var 
              self = this,
              $field = $(this).next()
            ;

            $(self)
              .jSignature();


            if ($.trim($field.val())) {
              $(self).jSignature('setData', JSON.parse($field.val()), 'native');
            }


            $(self).bind('change', function(evt){

              var 
                value = $(this).jSignature('getData', 'native'),
                name = $(this).attr('name');

              $field.val(JSON.stringify(value));
              $field.trigger('blur');
              savePocForm();
            });


            $(self).parent().find('.clear_signature').click(function(evt){
              evt.preventDefault();

              $(self).jSignature('reset');
              savePocForm();
            });

          });

          var $pocForm = $('#poc-form');

          $pocForm.find('input[type=text], textarea').blur(function(){
            savePocForm();
          });
          
          $pocForm.find('input[type=radio], input[type=checkbox], select').change(function(){
            savePocForm();
          });


          function savePocForm() {
            var data = $pocForm.find('input, select, textarea').serializeArray();
            var point_of_care_id = $("#point_of_care_id").val();
            data.push({'name': 'point_of_care_id', 'value' : point_of_care_id});
            data.push({'name': 'procedure_name', 'value' : $('#procedure_name').val()});
            $.post(
                    '<?php echo $this->Session->webroot; ?>encounters/in_house_work_procedures_data/encounter_id:<?php echo $encounter_id; ?>/task:poc_form/poc_id:'+point_of_care_id, 
                    data, 
                  function(res){
                  }
              );            
            
          }
          

        });    
  <?php endif;?>
  
  
  
	</script>
	<div style="float:left; width:100%">
	<form id="frmInHouseWorkProcedure" method="post" accept-charset="utf-8" enctype="multipart/form-data">
	<input type="hidden" name="point_of_care_id" id="point_of_care_id" style="width:450px;" value="<?php echo isset($point_of_care_id)?$point_of_care_id:'' ;?>">
	<input type="hidden" name="data[EncounterPointOfCare][encounter_id]" id="encounter_id" value="<?php echo isset($encounter_id)?$encounter_id:'' ;?>" />
		<input type="hidden" name="data[EncounterPointOfCare][order_type]" id="order_type" value="Procedure" />
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
		<tr>
		    <td width="150"><label>Procedure Name:</label></td>
		    <td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][procedure_name]" id="procedure_name" style="width:450px; background:#eeeeee;" value="<?php echo isset($procedure_name)?$procedure_name:'' ;?>" readonly="readonly"></td>
		</tr>
		<tr>
			<td width="150" style="vertical-align:top;"><label>Reason:</label></td>
			<td><div style="float:left;"><input type="text" name="data[EncounterPointOfCare][procedure_reason]" id="procedure_reason" value="<?php echo $procedure_reason;?>" class="required" style="width:450px;" onblur="updateProceduresData(this.id, this.value);" /></div></td>
		</tr>
		<tr>
			<td width="150"><label>Priority:</label></td>
			<td>
				<select name="data[EncounterPointOfCare][procedure_priority]" id="procedure_priority" onchange="updateProceduresData(this.id, this.value);">
				<option value="" selected>Select Priority</option>
                <option value="Routine" <?php echo ($procedure_priority=='Routine'? "selected='selected'":''); ?>>Routine</option>
                <option value="Urgent" <?php echo ($procedure_priority=='Urgent'? "selected='selected'":''); ?> > Urgent</option>
			    </select>
			</td>
		</tr>
    <?php if ($admin_form): ?>
    <tr>
      <td colspan="2">
        <br />
        <br />
        <div id="poc-form">
        <?php 
          App::import('Lib', 'FormBuilder');
          $formBuilder = new FormBuilder();
        
        echo $formBuilder->build($admin_form, $raw_poc_form); ?>
        </div>
        <br />
        <br />
        <br />
        
      </td>
    </tr>
    <?php endif;?>
		<tr>
			<td valign='top' style="vertical-align:top"><label>Procedure Notes:</label></td>
			<td><textarea cols="20" name="data[EncounterPointOfCare][procedure_details]" id="procedure_details" style="height:180px" onblur="updateProceduresData(this.id, this.value);"><?php echo isset($procedure_details) ? $procedure_details:''; ?></textarea>
        <div id="procedure-macros"></div>
        
      </td>
		</tr>
		<tr>
			<td width="150"><label>Body Site:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][procedure_body_site]" id="procedure_body_site" style="width:450px;" value="<?php echo isset($procedure_body_site)?$procedure_body_site:'' ;?>" onblur="updateProceduresData(this.id, this.value);"></td>
		</tr>
		<tr>
			<td width="150"><label>Administered By:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][procedure_administered_by]" id="procedure_administered_by" style="width:450px;" value="<?php echo isset($procedure_administered_by)?$procedure_administered_by:'' ;?>" onblur="updateProceduresData(this.id, this.value);"></td>
		</tr>
		<tr>
			<td width="150" class="top_pos"><label>Date Performed:</label></td>
			<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][procedure_date_performed]', 'id' => 'procedure_date_performed', 'value' => (isset($procedure_date_performed) and (!strstr($procedure_date_performed, "0000")))?date($global_date_format, strtotime($procedure_date_performed)):date($global_date_format), 'required' => false)); ?></td>
		</tr>	
        <tr>
		   <td width="150"><label>Time:</label></td>
		<td style="padding-right: 10px;"><input type='text' id='procedure_time' size='5' name='procedure_time' value='<?php 
		 echo "$hours:$minutes" ; ?>' onblur='updateProceduresDate();'>  <a href="javascript:void(0)" id='exacttimebtn' onclick="showNow()"><?php echo $html->image('time.gif', array('alt' => 'Time now'));?> NOW</a>           </td>
	   </tr>
		<tr>
            <td><label>Unit(s):</label></td>
            <td><input type="text" name="data[EncounterPointOfCare][procedure_unit]" id="procedure_unit"
value="<?php echo isset($procedure_unit) ? $procedure_unit:''; ?>" style="width:30px;" onblur="updateProceduresData(this.id,
this.value);" /></td>
        </tr>	   
		<tr>
			<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
			<td><textarea cols="20" name="data[EncounterPointOfCare][procedure_comment]" id="procedure_comment" style="height:80px" onblur="updateProceduresData(this.id, this.value);"><?php echo isset($procedure_comment)?$procedure_comment:''; ?></textarea></td>
		</tr>
        <tr>
            <td><label>CPT:</label></td>
            <td>
                <input type="text" name="cpt" id="cpt" style="width:964px;" value="<?php echo isset($cpt)?$cpt:'' ;?>" onblur="updateProceduresData(this.id, this.value);">
                <input type="hidden" name="cpt_code" id="cpt_code" value="<?php echo isset($cpt_code)?$cpt_code:'' ;?>">
            </td>
        </tr>
        <tr>
            <td><label>Modifier(s):</label></td>
            <td>
                <input type="text" name="modifier" id="modifier" style="width:964px;" value="<?php echo isset($modifier)?$modifier:'' ;?>" onblur="updateProceduresData(this.id, this.value);">
            </td>
        </tr>
		<tr>
            <td valign='top' style="vertical-align:top"><label>Fee:</label></td>
            <td><input type="text" name="fee" id="fee" style="width:90px;" value="<?php echo isset($fee)?$fee:'' ;?>" onblur="updateProceduresData(this.id, this.value);"></td>
        </tr>
        <?php 
				      $total_providers=count($users);
                      if($total_providers== 1)
                      {?>
        <tr height="35">
             <td valign='top' style="vertical-align:top"><label>Ordered by:</label></td>
             <td>
			     
					   <input type="hidden" id="ordered_by_id" name="data[EncounterPointOfCare][ordered_by_id]" value="<?php echo $users[0]['UserAccount']['user_id']; ?>" />
                       <?php echo $users[0]['UserAccount']['firstname']. ' '. $users[0]['UserAccount']['lastname']; ?>
					 
					  </td></tr>
			<?php	 } 	 else  
					 {
					   ?>
			 <tr>
             <td><label>Ordered by:</label></td>
             <td>		   
			 <select name="data[EncounterPointOfCare][ordered_by_id]" id="ordered_by_id" onchange="updateProceduresData(this.id, this.value);">
                        <option value="" selected>Select Provider</option>
                         <?php foreach($users as $user): 
						   $provider_id = $user['UserAccount']['user_id'];
						   $provider_name = $user['UserAccount']['firstname'].' '.$user['UserAccount']['lastname'];
						 ?>
                            <option value="<?php echo $provider_id; ?>" <?php if($ordered_by_id==$provider_id) { echo 'selected'; }?>><?php echo $provider_name; ?></option>
                            <?php endforeach; ?>
                        </select>
					
			 </td>
        </tr>
		<?php }
		?>	
        <tr>
            <td width="150"><label>Status:</label></td>
            <td>
                <select name="data[EncounterPointOfCare][status]" id="status" style="width: 130px;" onchange="updateProceduresData(this.id, this.value);">
                 <option value="" selected>Select Status</option>
                 <option value="Open" <?php echo ($status=='Open'? "selected='selected'":''); ?>>Open</option>
                 <option value="Done" <?php echo ($status=='Done'? "selected='selected'":''); ?> > Done</option>
                 </select>
             </td>
        </tr>
</table>
</form>
</div>
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
<script>
	$(function(){
		var isDatepickerOpen = false;
		
		$('.hasDatepicker')
			.unbind('blur.injection')
			.bind('blur.injection',function(){
				var 
					id = $(this).attr('id'),
					value = $(this).val()
				;
				
				if (!isDatepickerOpen) {
					updateProceduresData(id, value);
				}

			});
			
		$('.hasDatepicker').datepicker('option', {
			beforeShow: function(){
				isDatepickerOpen = true;
			},
			onClose: function(){
				var 
					id = $(this).attr('id'),
					value = $(this).val()
				;
				updateProceduresData(id, value);
				isDatepickerOpen = false;
			}
		});	
			
		
	});
</script>
