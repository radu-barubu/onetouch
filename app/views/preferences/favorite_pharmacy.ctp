<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

if(isset($PracticeData))
{
$rx_setup =  $PracticeData['PracticeSetting']['rx_setup'];
}

if($task == 'addnew' || $task == 'edit')
{ ?>
   
   <script language="javascript" type="text/javascript">
function addTestSearchData(data)
{
	var test_codes = $("#tableTestCode").data('data');
	
	var found = false;
	
	for(var i = 0; i < test_codes.length; i++)
	{
		if(test_codes[i]['orderable'] == data['orderable'])
		{
			found = true;
		}
	}
	
	if(!found)
	{
		test_codes[test_codes.length] = data;
	}
	
	$("#tableTestCode").data('data', test_codes);

}
function validateRxForm()
{
	var valid = true;
		
	$('#name').removeClass("error");
	$('.error[htmlfor="name"]').remove();

	if($('#name').val() == "")
	{
		$('#name').addClass("error");
		$('#name').after('<div htmlfor="name" generated="true" class="error">This field is required.</div>');
		valid = false;
	}
	return valid;
}
function submitRx()
{
	//collectOrderData();
	if(validateRxForm())
	{
		$('#saveBtn').addClass("button_disabled");
		$('#saveBtn').unbind('click');
		$('#submit_swirl').show();
		$('#frm').submit();
	}
}	
$(document).ready(function()
{	
    $('#saveBtn').click(submitRx);
});
    </script>
<?php 	if($task == 'edit')
	{
		extract($EditItem['EmdeonFavoritePharmacy']);
		$id_field = '<input type="hidden" name="data[favorite_pharmacy_id]" id="favorite_pharmacy_id" value="'.$favorite_pharmacy_id.'" />';
		$pharmacy_orgpreference = '<input type="hidden" name="data[pharmacy_orgpreference]" id="pharmacy_orgpreference" value="'.$pharmacy_orgpreference.'" />';
    }
	else
	{
		//Init default value here
		$id_field = "";
		$prescriber_id = '';
        $pharmacy_id = '';
        $pharmacy_name = '';
        $pharmacy_address_1 = "";
        $pharmacy_address_2 = "";
        $pharmacy_state = "";
        $pharmacy_city = "";
        $pharmacy_phone = "";
        $pharmacy_zip = "";
		$pharmacy_orgpreference = "";
	}
	
    echo $this->element("pharmacy_search", array('submit' => 'addTestSearchData', 'open' => 'imgSearchPharmacyOpen', 'container' => 'pharmacy_search_container', 'form_name' => 'favorite_pharmacy')); 
	?>
    
	<div style="overflow: hidden;">
		<?php echo $this->element('preferences_favorite_links'); ?>
		<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php 
		echo $id_field; 
		echo $pharmacy_orgpreference;
		?>
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td class="field_title" width="180"><label>Physician:</label></td>
				<td>      
                     <select name="data[prescriber_id]" id="prescriber_id" style="width:200px;" >
                     <option value=""></option>
                     <?php foreach($caregivers as $caregiver): ?>
                     <option value="<?php echo $caregiver['caregiver']; ?>" <?php if($prescriber_id == $caregiver['caregiver']):?>selected="selected"<?php endif; ?>><?php echo $caregiver['cg_first_name']; ?>, <?php echo $caregiver['cg_last_name']; ?></option>
                     <?php endforeach; ?>
                     </select>
				</td>
			</tr>
			<tr>
                <td style="vertical-align: top;"><label>Favorite Pharmacy:</label></td>
                <td>
                    <input type="hidden" name="data[pharmacy_id]" id="pharmacy_id" value="<?php echo $pharmacy_id; ?>"/>
                    <input type="hidden" name="data[pharmacy_address_1]" id="pharmacy_address_1" value="<?php echo $pharmacy_address_1; ?>"/>
                    <input type="hidden" name="data[pharmacy_address_2]" id="pharmacy_address_2" value="<?php echo $pharmacy_address_2; ?>"/>
                    <input type="hidden" name="data[pharmacy_city]" id="pharmacy_city" value="<?php echo $pharmacy_city; ?>"/>
                    <input type="hidden" name="data[pharmacy_state]" id="pharmacy_state" value="<?php echo $pharmacy_state; ?>"/>
                    <input type="hidden" name="data[pharmacy_phone]" id="pharmacy_phone" value="<?php echo $pharmacy_phone; ?>"/>
                    <input type="hidden" name="data[pharmacy_zip]" id="pharmacy_zip" value="<?php echo $pharmacy_zip; ?>"/>
                    <div style="float:left;"><input name="data[pharmacy_name]" id="pharmacy_name" class="required" type="text" style="width:400px;" value="<?php echo $pharmacy_name; ?>" /></div>								
                    <div style="float:left; padding-left:5px;"><img id="imgSearchPharmacyOpen" style="cursor: pointer;margin-top: 3px;" src="<?php echo $this->Session->webroot . 'img/search_data.png'; ?>" width="20" height="20" onclick="$('#pharmacy_search_row').css('display','table-row');" /></div>
                </td>
            </tr>
            <tr id="pharmacy_search_row" style="display:none;">
                <td colspan="2">
                    <div style="float: left; clear: both; margin-bottom: 10px; width: 80%;">
                        <div id="pharmacy_search_container" style="clear:both;"></div>
                    </div>
                </td>
            </tr>
		</table>
		</form>
	</div>
	<div class="actions">
		<ul>
			<li><a id="saveBtn" href="javascript:void(0);" class="btn">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'favorite_pharmacy'));?>&nbsp;&nbsp;</li>
		</ul>
		<span class = "submit_swirl_unit" id="submit_swirl" style="float: left; margin-top: 5px; display:none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
	</div>
	<?php
}
else
{
	if($rx_setup == 'Standard'): ?>
	<div class="error"><b>Warning:</b> e-Prescribing service is not turned on.</div><br /><?php endif; ?>
	<div style="overflow: hidden;">
		<?php echo $this->element('preferences_favorite_links'); ?>

		<form id="frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15">
                <label class="label_check_box">
                <input type="checkbox" class="master_chk" />
                </label>
                </th>
				<th><?php echo $paginator->sort('Name', 'pharmacy_name', array('model' => 'EmdeonFavoritePharmacy'));?></th>
				<th><?php echo $paginator->sort('Address', 'pharmacy_address_1', array('model' => 'EmdeonFavoritePharmacy'));?></th>
				<th><?php echo $paginator->sort('City', 'pharmacy_city', array('model' => 'EmdeonFavoritePharmacy'));?></th>
			</tr>

			<?php
			foreach ($EmdeonFavoritePharmacy as $EmdeonFavoritePharmacy):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'favorite_pharmacy', 'task' => 'edit', 'favorite_pharmacy_id' => $EmdeonFavoritePharmacy['EmdeonFavoritePharmacy']['favorite_pharmacy_id']), array('escape' => false)); ?>">
					<td class="ignore">
                    <label class="label_check_box">
                    <input name="data[EmdeonFavoritePharmacy][favorite_pharmacy_id][<?php echo $EmdeonFavoritePharmacy['EmdeonFavoritePharmacy']['pharmacy_orgpreference']; ?>]" type="checkbox" class="child_chk" value="<?php echo $EmdeonFavoritePharmacy['EmdeonFavoritePharmacy']['favorite_pharmacy_id'].'|'.$EmdeonFavoritePharmacy['EmdeonFavoritePharmacy']['pharmacy_orgpreference']; ?>" />
                    </label>
                    </td>
					<td><?php echo $EmdeonFavoritePharmacy['EmdeonFavoritePharmacy']['pharmacy_name']; ?></td>
					<td><?php echo $EmdeonFavoritePharmacy['EmdeonFavoritePharmacy']['pharmacy_address_1']; ?></td>
					<td><?php echo $EmdeonFavoritePharmacy['EmdeonFavoritePharmacy']['pharmacy_city']; ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'favorite_pharmacy', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>

			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'EmdeonFavoritePharmacy', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('EmdeonFavoritePharmacy') || $paginator->hasNext('EmdeonFavoritePharmacy'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('EmdeonFavoritePharmacy'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'EmdeonFavoritePharmacy', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'EmdeonFavoritePharmacy', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('EmdeonFavoritePharmacy'))
					{
						echo $paginator->next('Next >>', array('model' => 'EmdeonFavoritePharmacy', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
			</div>
	</div>

	<script language="javascript" type="text/javascript">
		function deleteData()
		{
			var total_selected = 0;
			
			$(".child_chk").each(function()
			{
				if($(this).is(":checked"))
				{
					total_selected++;
				}
			});
			
			if(total_selected > 0)
			/*{
				var answer = confirm("Delete Selected Item(s)?")
				if (answer)*/
				{
					$("#frm").submit();
				}
			/*}*/
			else
			{
				alert("No Item Selected.");
			}
		}
	</script>
	<?php
}
?>