<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$mainURL = $html->url(array('action' => 'in_house_work_supplies', 'encounter_id' => $encounter_id)) . '/';

$page_access = $this->QuickAcl->getAccessType("encounters", "point_of_care");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

?>
<style>
   .tab_area,.title_area {  -webkit-user-select: none; //disable copy/paste }
</style>
<script language="javascript" type="text/javascript">

    function addData(point_of_care_id, supply_name, supply_unit)
    {
        var val = (jQuery('#supply_'+point_of_care_id).is(':checked'))?'1':'0';

        if(val==1)
        {           
            $.post(
            '<?php echo $this->Session->webroot; ?>encounters/in_house_work_supplies/encounter_id:<?php echo $encounter_id; ?>/task:addSupplyTest/', 
            {
              'data[administration_point_of_care_id]': point_of_care_id,
              'data[item_value]': supply_name,
              'data[item_unit]': supply_unit
            }, 
            function(data)
            {
            }
            );
            $('#label_supply_'+point_of_care_id).css('cursor', 'pointer');
        }
        else
        {
            $('#in_house_work_supply_form_area').hide();
            $.post(
            '<?php echo $this->Session->webroot; ?>encounters/in_house_work_supplies/encounter_id:<?php echo $encounter_id; ?>/task:deleteSupplyTest/', 
            {
              'data[item_value]': supply_name
            }, 
            function(data)
            {
            }
            );
            $('#label_supply_'+point_of_care_id).css('cursor', 'auto');
        }
    }
    
    function loadData(point_of_care_id, supply_name)
    {
        var val = (jQuery('#supply_'+point_of_care_id).is(':checked'))?'1':'0';
        if(val==1)
        {
        $('#in_house_work_supply_form_area').show();
        $('#in_house_work_supply_form_area').html('');
        $("#imgLoad").show();
            $.post(
            '<?php echo $this->Session->webroot; ?>encounters/in_house_work_supplies/encounter_id:<?php echo $encounter_id; ?>/task:checkSupplyTest/', 
            {
              'data[item_value]': supply_name
            }, 
            function(data)
            {
               //alert('test'+data.supply_test['exist']);
               
               if(data.supply_test['exist']=='yes')
               {
                  $.post('<?php echo $this->Session->webroot; ?>encounters/in_house_work_supplies_data/encounter_id:<?php echo $encounter_id; ?>/',     {'supply_name':supply_name}, 
                  function(data){ 
                    $('#in_house_work_supply_form_area').html(data);
                    $("#imgLoad").hide();
				            if(typeof($ipad)==='object')$ipad.ready();
                  });
               }
               else
               {
                  $('#in_house_work_supply_form_area').html('');
                  $("#imgLoad").hide();
               }
               $('html, body').animate({  scrollTop: $(document).height()-300 },1000);
            },
            'json'
            );
            
        }
        else
        {
          $('#in_house_work_supply_form_area').hide();
        }
    }
	
	function loadInitialData()
	{
		<?php if($init_point_of_care_name): ?>
		$.post('<?php echo $this->Session->webroot; ?>encounters/<?php echo $this->params['action']; ?>_data/encounter_id:<?php echo $encounter_id; ?>/', 'supply_name='+'<?php echo $init_point_of_care_name; ?>', 
		function(data)
		{ 
		   $('#in_house_work_supply_form_area').html(data);
		   $("#imgLoad").hide();
		   if(typeof($ipad)==='object')$ipad.ready();
		});
		<?php endif; ?>
	}
	
    $(document).ready(function()
    {   
        $('.label_check_box_home').click(function() {
          if ( $(this).find('.fast-chkbox').is(":checked")) {
                $(this).addClass("label_active");
          } else {
                $(this).removeClass("label_active");
          }
        });
        $('.label_check_box_home').children("input:checked").map(function() {
                $(this).closest('.label_check_box_home').addClass("label_active");
        });

        initCurrentTabEvents('supply_records_area');
        //alert('test1');
        $.post(
            '<?php echo $this->Session->webroot; ?>encounters/in_house_work_supplies/encounter_id:<?php echo $encounter_id; ?>/task:get_admin_poc_supplies/', 
            '', 
            function(data)
            {
            //alert(data.supply_items.length);
            if(data.supply_items.length > 0)
                {            
                    for(var i = 0; i < data.supply_items.length; i++)
                    {
                    var point_of_care_id = data.supply_items[i].AdministrationPointOfCare.point_of_care_id;
            $( "#rerun_"+point_of_care_id).button({
                    text: false,
                    icons: {
                        primary: "ui-icon-triangle-1-s"
                    }
                })
                .buttonset();
                }
                }
            },
            'json'
            );
        
                
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
		
				$('.fast-chkbox').each(function(){
					var poc_id = $(this).data('poc_id');
					var supply_name = $(this).data('poc_supply_name');
					var supply_unit = $(this).data('poc_supply_unit');
					$(this).click(function(){
						addData(poc_id, supply_name, supply_unit);
						
					});
				});
				
				
				$('.poc-button').each(function(){
					var poc_id = $(this).closest('.poc-wrapper').find('.fast-chkbox').data('poc_id');
					var supply_name = $(this).closest('.poc-wrapper').find('.fast-chkbox').data('poc_supply_name');
					
					
					$(this).click(function(){
						loadData(poc_id, supply_name);
					});
				});    
    
		loadInitialData();
		<?php echo $this->element('dragon_voice'); ?>
    });  
</script>
<body>
<?php echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 17)); ?>
<div id="poc_area" style="overflow: hidden;">
    <div class="title_area">
        <div class="title_text">
            <a href="javascript:void(0);" class="in_house_work_submenuitem" url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'in_house_work_labs', 'encounter_id' => $encounter_id)); ?>" style="float: none;">Labs</a>    
            <a href="javascript:void(0);" class="in_house_work_submenuitem" url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'in_house_work_radiology', 'encounter_id' => $encounter_id)); ?>" style="float: none;">Radiology</a>
            <a href="javascript:void(0);" class="in_house_work_submenuitem" url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'in_house_work_procedures', 'encounter_id' => $encounter_id)); ?>" style="float: none;">Procedures</a>
            <a href="javascript:void(0);" class="in_house_work_submenuitem" url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'in_house_work_immunizations', 'encounter_id' => $encounter_id)); ?>" style="float: none;">Immunization</a>
            <a href="javascript:void(0);" class="in_house_work_submenuitem" url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'in_house_work_injections', 'encounter_id' => $encounter_id)); ?>" style="float: none;">Injections</a>
            <a href="javascript:void(0);" class="in_house_work_submenuitem" url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'in_house_work_meds', 'encounter_id' => $encounter_id)); ?>" style="float: none;">Meds</a>
            <a href="javascript:void(0);" class="in_house_work_submenuitem active" url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'in_house_work_supplies', 'encounter_id' => $encounter_id)); ?>" style="float: none;">Supplies</a>
        </div>   
    </div>
		<?php if($categories): ?>
		<?php echo $this->element('poc_category_menu', array(
			'poc_type' => 'supplies',
			'currentCategory' => $currentCategory,
			'categories' => $categories,
			'encounter_id' => $encounter_id,
		));?>
		<?php endif;?>
	
    <div id="supply_records_area" class="tab_area"> 
        <div>
            <?php
			$EncounterPointOfCare = array_map('strtolower', $EncounterPointOfCare);
            foreach ($AdministrationPointOfCare as $AdministrationPointOfCare):
            $point_of_care_id = $AdministrationPointOfCare['AdministrationPointOfCare']['point_of_care_id']; 
            $supply_name = $AdministrationPointOfCare['AdministrationPointOfCare']['supply_name'];
            $supply_unit = $AdministrationPointOfCare['AdministrationPointOfCare']['supply_unit'];
            ?>
          <div class="poc-wrapper"><label class="label_check_box_home" id="label_supply_<?php echo $point_of_care_id; ?>" name="label_supply_<?php echo $point_of_care_id; ?>"   ><input name="supply_<?php echo $point_of_care_id; ?>" id="supply_<?php echo $point_of_care_id; ?>" type="checkbox" class="fast-chkbox" value="<?php echo $point_of_care_id; ?>" <?php echo (in_array(strtolower($supply_name), $EncounterPointOfCare))?'checked':''; ?> data-poc_id="<?php echo $point_of_care_id; ?>" data-poc_supply_name="<?php echo $supply_name; ?>" data-poc_supply_unit="<?php echo $supply_unit; ?>" />&nbsp;<?php echo $supply_name; ?></label><button class="poc-button" id="rerun_<?php echo $point_of_care_id; ?>" >&nbsp;</button></div>        
            <?php endforeach; ?>
        </div>
    </div>
    <span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <div class="clear"></div>
    <div id="supply_records_area" class="tab_area"> 
        <div id="in_house_work_supply_form_area" style="padding-top:15px;"></div>
    </div>
</div>
<style>
	
	.poc-wrapper {
		float:left; display:inline; margin-left:2px; margin-right: 6px;
	}
  .poc-wrapper label.label_check_box_home {
    vertical-align:middle; 
    cursor:pointer; 
    float:none;
  }
  
	.poc-button {
		vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3);
		-moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);
		
		-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;
	}	
	
	.fast-chkbox, .label_check_box_home {
		-webkit-tap-highlight-color: rgba(0,0,0,0);
		-webkit-user-select: none;
		-webkit-touch-callout: none;		
	}
	
</style>	
	<?php if($isiPadApp):?>
<style>
.label_check_box_home{
/*	width:auto;*/
	display:inline-block;
	color:black;
	margin:-10px 0 0 0;
	padding:3px 8px; 
	border-radius: 4px;
	background-color: transparent;
	border: 1px solid #fff;
	-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3);
	-moz-box-shadow:0 0 2px rgba(0,0,0,0.3);
	box-shadow:0 0 2px rgba(0,0,0,0.3);
	-webkit-transition: none;
	-moz-transition: none;
	-o-transition: none;
}	

	.poc-button {
		-webkit-transition: none;
		-moz-transition: none;
		-o-transition: none;
	}	

</style>
	<?php endif;?>
