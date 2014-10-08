<?php
$practice_settings = $this->Session->read("PracticeSetting");
$rx_setup =  $practice_settings['PracticeSetting']['rx_setup'];

$dosespot_screen_access = false;

if($is_physician && $rx_setup == 'Electronic_Dosespot')
{
	$dosespot_screen_access = true;
}

$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$no_medication = isset($no_medication)?$no_medication:'';
$no_allergy = isset($no_allergy)?$no_allergy:'';
echo $this->Html->script('ipad_fix.js');

$link_to_medication = $html->url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information',  'task' => 'edit', 'patient_id' => $patient_id, 'view_medications' => 1));

$page_access = $this->QuickAcl->getAccessType("encounters", "meds");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

?>
<script language="javascript" type="text/javascript">
    function addAllergy(item_value, item_value2, item_type, allergy_code, allergy_code_type)
    {
		//if no value set, define defaults
		if($.trim(allergy_code) == '')
		{
	  		allergy_code = "3";
		}
        
		if($.trim(allergy_code_type) == '')
        {
	  		allergy_code_type = "AllergyClass";
        }
        
		if($.trim(item_value) == '')
        {
            return;
        }

        $("#imgLoading").show();
        var formobj = $("<form></form>");
        formobj.append('<input name="data[item_value]" type="hidden" value="'+ item_value +'">');
        formobj.append('<input name="data[item_reaction]" type="hidden" value="'+ item_value2 +'">');
        formobj.append('<input name="data[item_type]" type="hidden" value="'+ item_type +'">');
        formobj.append('<input name="data[allergy_code]" type="hidden" value="'+ allergy_code +'">');
        formobj.append('<input name="data[allergy_code_type]" type="hidden" value="'+ allergy_code_type +'">');        

        var showAllAllergy = ($('#show_all_allergies').is(':checked'))?'yes':'no';
        
        $.post(
            '<?php echo $this->Session->webroot; ?>encounters/meds_allergy/encounter_id:<?php echo $encounter_id; ?>/task:addAllergy/show_all_allergies:'+showAllAllergy+'/', 
            formobj.serialize(), 
            function(data)
            {
                $("#imgLoading").hide();
				if(item_type == 'medication')
				{
                    $("#allergySearch").val(""); 
					$("#allergyReaction").val("");
				}
				else
				{
				    $("#allergy").val(""); 
					$("#allergyReaction").val("");
				}
                resetAllergy(data);
            },
            'json'
        );
       initAutoLogoff();
    }
    
    function deleteAllergy(item_value, dosespot_allergy_id, allergy_id_emdeon)
    {
        $("#imgLoadingAllergy").show();
        var formobj = $("<form></form>");
        formobj.append('<input name="data[item_value]" type="hidden" value="'+item_value+'">');
        formobj.append('<input name="data[dosespot_allergy_id]" type="hidden" value="'+dosespot_allergy_id+'">');
		formobj.append('<input name="data[allergy_id_emdeon]" type="hidden" value="'+allergy_id_emdeon+'">');
		
        var showAllAllergy = ($('#show_all_allergies').is(':checked'))?'yes':'no';
        
        $.post(
            '<?php echo $this->Session->webroot; ?>encounters/meds_allergy/encounter_id:<?php echo $encounter_id; ?>/task:deleteAllergy/show_all_allergies:'+showAllAllergy+'/', 
            formobj.serialize(), 
            function(data)
            {
                $("#imgLoadingAllergy").hide();
                resetAllergy(data);
                $('#form_area').hide();
                $('#form_area').html('');
            },
            'json'
        );
       initAutoLogoff();
    }
    
    function resetMedsAllergyTableState()
    {
        $("#table_medication_listing tr").each(function()
        {
            $(this).removeClass("table_highlight");

            if($(this).attr("oriclass") != "")
            {
                $(this).addClass($(this).attr("oriclass"));
            }
        });

        $("#table_allergy_listing tr").each(function()
        {
            $(this).removeClass("table_highlight");

            if($(this).attr("oriclass") != "")
            {
                $(this).addClass($(this).attr("oriclass"));
            }
        });
    }
	
	function jsPaginate(selector)
    {
	var rows=$(selector).find('tbody tr.js-page').length;
	var no_rec_per_page=15;
	var no_pages= Math.ceil(rows/no_rec_per_page);
	var $pagenumbers=$('<div class="paging js-paging"></div>');

	$(selector).find('div.paging').closest('tr').remove();

	if(no_pages>1) {
		for(i=0;i<no_pages;i++) {
			$('<span class="page"><a href="javascript:;">'+(i+1)+'</a></span>').appendTo($pagenumbers);
		}
	}
	//if($(selector).is(':visible')) $pagenumbers.show(); else $pagenumbers.hide();
	//$pagenumbers.insertAfter(selector);
	$(selector).append('<tr deleteable="true"><td colspan="4"><div class="paging js-paging">'+$pagenumbers.html()+'</div></td></tr>');
	$('.paging span.page', $(selector)).click(function(event){
		$(selector).find('tbody tr.js-page').hide();
		$('.paging span.page').removeClass('curPage');
		$(this).addClass('curPage');
		for(i=($(this).text()-1)*no_rec_per_page;i<=$(this).text()*no_rec_per_page-1;i++)
		{
			$(tr[i]).show();
		}
	});
	$(selector).find('tbody tr.js-page').hide();
	var tr=$(selector+' tbody tr.js-page'); 
	for(var i=0;i<=no_rec_per_page-1;i++)
	{
		$(tr[i]).show();
	}
	$('.paging span.page:first').addClass('curPage');
}
	
	function jsPaginate_medication(selector)
    {
	var rows=$(selector).find('tbody tr.js-page').length;
	var no_rec_per_page=15;
	var no_pages= Math.ceil(rows/no_rec_per_page);
	var $pagenumbers=$('<div class="paging js-paging"></div>');
	
	$(selector).find('div.paging').closest('tr').remove();
	
	if(no_pages>1) {
		for(i=0;i<no_pages;i++) {
			$('<span class="page"><a href="javascript:;">'+(i+1)+'</a></span>').appendTo($pagenumbers);
		}
	}
	//if($(selector).is(':visible')) $pagenumbers.show(); else $pagenumbers.hide();
	//$pagenumbers.insertAfter(selector);
	$(selector).append('<tr deleteable="true"><td colspan="4"><div class="paging js-paging">'+$pagenumbers.html()+'</div></td></tr>');
	$('.paging span.page', $(selector)).click(function(event){
		$(selector).find('tbody tr.js-page').hide();
		$('.paging span.page').removeClass('curPage');
		$(this).addClass('curPage');
		for(i=($(this).text()-1)*no_rec_per_page;i<=$(this).text()*no_rec_per_page-1;i++)
		{
			$(tr[i]).show();
		}
	});
	$(selector).find('tbody tr.js-page').hide();
	var tr=$(selector+' tbody tr.js-page'); 
	for(var i=0;i<=no_rec_per_page-1;i++)
	{
		$(tr[i]).show();
	}
	$('.paging span.page:first').addClass('curPage');
    }

    
    function resetAllergyTable(data)
    {
        $("#table_allergy_listing tr").each(function()
        {
            if($(this).attr("deleteable") == "true")
            {
                $(this).remove();
            }
        });
        
        if(data.allergy_list.length > 0)
        {
           //disable the NONE for allergy
           $('#no_allergy').attr("disabled", true);
           <?php if(isset($patient_checkin)): ?>
              $("#allergy_rows").html("<br /><br /><br /><br />");
           <?php else: ?>
              $("#allergy_rows").html("<br />");
           <?php endif;?>
            for(var i = 0; i < data.allergy_list.length; i++)
            {
                var html = '<tr deleteable="true" class="js-page" itemvalue="'+data.allergy_list[i].PatientAllergy.allergy_id+'" >';
                
				<?php if($page_access == 'W'): ?>
				html += '<td width="15"><span class="del_icon" itemvalue="'+data.allergy_list[i].PatientAllergy.allergy_id+'" dosespot_allergy_id="'+data.allergy_list[i].PatientAllergy.dosespot_allergy_id+'" allergy_id_emdeon="'+data.allergy_list[i].PatientAllergy.allergy_id_emdeon+'"><?php echo $html->image('del.png', array('alt' => '')); ?></span></td>';
                <?php else: ?>
				html += '<td width="15"><span itemvalue="'+data.allergy_list[i].PatientAllergy.allergy_id+'"><?php echo $html->image('del_disabled.png', array('alt' => '')); ?></span></td>';
				<?php endif; ?>
				
				html += '<td>';
                reaction = '';
                if(data.allergy_list[i].PatientAllergy.reaction) {
                    reaction = '&nbsp;(' + data.allergy_list[i].PatientAllergy.reaction + ')';
                }
                html += '<div style="float:left;" class="allergy_row" itemvalue="'+data.allergy_list[i].PatientAllergy.allergy_id+'">'+data.allergy_list[i].PatientAllergy.type+': '+data.allergy_list[i].PatientAllergy.agent+reaction+',&nbsp;Status:&nbsp;</div>';
                html += '<div class="editable_field" allergy_id="'+data.allergy_list[i].PatientAllergy.allergy_id+'" style="float:left;">'+data.allergy_list[i].PatientAllergy.status+'</div>';
                html += '</td>';
                html += '</tr>';
                
                $("#table_allergy_listing").append(html);
                $("#allergy_rows").append("<br />");
            }
         
            $(".del_icon", $("#table_allergy_listing")).click(function()
            {
                deleteAllergy($(this).attr("itemvalue"), $(this).attr("dosespot_allergy_id"), $(this).attr("allergy_id_emdeon"));
            });
            
            $("#table_allergy_listing tr").each(function()
            {
                $(this).attr("oriclass", "");
            });
            
            $("#table_allergy_listing tr:even").each(function()
            {
                $(this).addClass("striped");
                $(this).attr("oriclass", "striped");
            });
            
            $("#table_allergy_listing tr").each(function()
            {
                $('td', $(this)).each(function()
                {
                    $(this).css("cursor", "pointer");
                });
            });
            
            $('.allergy_row').click(function()
            {   
                resetMedsAllergyTableState();
                
                var parent_tr = $(this).parent().parent();
                parent_tr.removeClass("striped");
                parent_tr.addClass("table_highlight");
                
                var allergy_id = $(this).attr("itemvalue");                    
                $('#form_area').html('');
                $("#imgLoadForm").show();
                $.post('<?php echo $this->Session->webroot; ?>encounters/allergy_data/encounter_id:<?php echo $encounter_id; ?>/', 
                'allergy_id='+allergy_id, 
                function(data){
                  $('#form_area').show();
                  $('#form_area').html(data);
                  $("#imgLoadForm").hide();
                  if(typeof($ipad)==='object')$ipad.ready();
                });
                
            });
            
			<?php if($page_access == 'W'): ?>      
            $('.editable_field').editable('<?php echo $this->Session->webroot; ?>patients/allergies/patient_id:<?php echo $patient_id; ?>/task:update_status/',
            { 
                indicator : '<?php echo $html->image('ajax_loaderback.gif', array('alt' => '')); ?>',
                data   : " {'':'Select Status', 'Active':'Active','Inactive':'Inactive','Resolved':'Resolved'}",
                type   : "select",
                cssclass: "dynamic_select",
                submitdata  : function(value, settings) 
                {
                    var allergy_id = $(this).attr("allergy_id");
                    return {'data[allergy_id]' : allergy_id};
                    
                }
            });
			<?php endif; ?>
        }
        else
        {
           //re-enable the NONE for allergy
           if($('#no_allergy').is(':disabled'))
           {
           	$('#no_allergy').removeAttr("disabled");
           }	
            var html = '<tr deleteable="true">';
            html += '<td colspan="2">No allergy.</td>';
            html += '</tr>';
            
            $("#table_allergy_listing").append(html);
        }
		var rows=$('#table_allergy_listing').find('tbody tr.js-page').length;
		if(rows > 15)
		{
		jsPaginate('#table_allergy_listing');
		}
    }
    
    function resetAllergy(items)
    {
        if(items == null)
        {
            var showAllAllergy = ($('#show_all_allergies').is(':checked'))?'yes':'no';
            $.post(
                '<?php echo $this->Session->webroot; ?>encounters/meds_allergy/encounter_id:<?php echo $encounter_id; ?>/task:get_allergies/show_all_allergies:'+showAllAllergy+'/', 
                '', 
                function(data)
                {
                    resetAllergyTable(data);
                },
                'json'
            );
        }
        else
        {
            resetAllergyTable(items);
        }
    }
    
    function addMedication(item_value, item_value2, rxnorm, medication_form, medication_strength_value, medication_strength_unit, medication_id)
    {
        if($.trim(item_value) == '')
        {
            return;
        }
        
        $("#imgLoading").show();
        var formobj = $("<form></form>");
        formobj.append('<input name="data[item_value]" type="hidden" value="'+item_value+'">');
        formobj.append('<input name="data[frequency]" type="hidden" value="'+ item_value2 +'">');
		formobj.append('<input name="data[rxnorm]" type="hidden" value="'+ rxnorm +'">');
		formobj.append('<input name="data[medication_form]" type="hidden" value="'+ medication_form +'">');
		formobj.append('<input name="data[medication_strength_value]" type="hidden" value="'+ medication_strength_value +'">');
		formobj.append('<input name="data[medication_strength_unit]" type="hidden" value="'+ medication_strength_unit +'">');
		formobj.append('<input name="data[medication_id]" type="hidden" value="'+ medication_id +'">');
        var showAllMedication = ($('#show_all_medications').is(':checked'))?'yes':'no';    
        var show_surescripts = ($('#show_surescripts').is(':checked'))?'yes':'no';    
        var show_reported = ($('#show_reported').is(':checked'))?'yes':'no';    
        var show_prescribed = ($('#show_prescribed').is(':checked'))?'yes':'no';
        
        $.post(
            '<?php echo $this->Session->webroot; ?>encounters/meds_allergy/encounter_id:<?php echo $encounter_id; ?>/task:addMedication/show_all_medications:'+showAllMedication+'/show_surescripts:'+show_surescripts+'/show_reported:'+show_reported+'/show_prescribed:'+show_prescribed+'/',
            formobj.serialize(), 
            function(data)
            {
                $("#imgLoading").hide();
                $("#medicationSearch").val(""); $("#medicationSig").val("");
                resetMedication(data);
            },
            'json'
        );
          initAutoLogoff();
    }
    
    function deleteMedication(item_value)
    {
        $("#imgLoadingMedication").show();
        var formobj = $("<form></form>");
        formobj.append('<input name="data[item_value]" type="hidden" value="'+item_value+'">');
        
        var showAllMedication = ($('#show_all_medications').is(':checked'))?'yes':'no';    
        var show_surescripts = ($('#show_surescripts').is(':checked'))?'yes':'no';    
        var show_reported = ($('#show_reported').is(':checked'))?'yes':'no';    
        var show_prescribed = ($('#show_prescribed').is(':checked'))?'yes':'no';    
        
        $.post(
            '<?php echo $this->Session->webroot; ?>encounters/meds_allergy/encounter_id:<?php echo $encounter_id; ?>/task:deleteMedication/show_all_medications:'+showAllMedication+'/show_surescripts:'+show_surescripts+'/show_reported:'+show_reported+'/show_prescribed:'+show_prescribed+'/', 
            formobj.serialize(), 
            function(data)
            {
                $("#imgLoadingMedication").hide();
                resetMedication(data);
                $('#form_area').html('');
            },
            'json'
        );
           initAutoLogoff();
    }
    
    function resetMedicationTable(data)
    {
        $("#table_medication_listing tr").each(function()
        {
            if($(this).attr("deleteable") == "true")
            {
                $(this).remove();
            }
        });
        
        if(data.medication_list.length > 0)
        {
					$('#no_medication').attr('disabled', 'disabled');
            for(var i = 0; i < data.medication_list.length; i++)
            {
                var html = '<tr class="js-page" deleteable="true"><td width="15">';
				
                if(data.medication_list[i].PatientMedicationList.source == 'Patient Reported') 
                {
					<?php if($page_access == 'W'): ?>
               		html += '<span class="del_icon" itemvalue="'+data.medication_list[i].PatientMedicationList.medication_list_id+'"><?php echo $html->image('del.png', array('alt' => '')); ?></span>'; 
					<?php else: ?>
					html += '<span itemvalue="'+data.medication_list[i].PatientMedicationList.medication_list_id+'"><?php echo $html->image('del_disabled.png', array('alt' => '')); ?></span>';
					<?php endif; ?>             
                }
				else
				{
					html += '<span itemvalue="'+data.medication_list[i].PatientMedicationList.medication_list_id+'"><?php echo $html->image('del_disabled.png', array('alt' => '')); ?></span>';
				}
				
                html += '</td>';
				
		var frequency = '';
		var source = '';
                var unit = '';
                var route = '';
                var quantity = '';
                var direction = '';
		var rx_alt='';
                if(data.medication_list[i].PatientMedicationList.quantity && data.medication_list[i].PatientMedicationList.quantity !='0')
                {
                  quantity = ',&nbsp;'+ data.medication_list[i].PatientMedicationList.quantity;
                }
                if(data.medication_list[i].PatientMedicationList.unit)
                {
                  unit = '&nbsp;'+ data.medication_list[i].PatientMedicationList.unit;
                }
                if(data.medication_list[i].PatientMedicationList.route)
                {
                  route = '&nbsp;'+ data.medication_list[i].PatientMedicationList.route;
                }
                if(data.medication_list[i].PatientMedicationList.frequency)
                {
                  frequency = ',&nbsp;'+ data.medication_list[i].PatientMedicationList.frequency;
                }
                if(data.medication_list[i].PatientMedicationList.rx_alt)
                {
                  rx_alt = ',&nbsp;'+ data.medication_list[i].PatientMedicationList.rx_alt;
                }
                if(data.medication_list[i].PatientMedicationList.direction)
                {
                  direction = ',&nbsp;'+ data.medication_list[i].PatientMedicationList.direction;
                }
                if(data.medication_list[i].PatientMedicationList.source)
                {
                  source += ',&nbsp;Source:&nbsp;'+ data.medication_list[i].PatientMedicationList.source;
                }
                
                html += '<td><div style="float:left;" class="medication_row" itemvalue="'+data.medication_list[i].PatientMedicationList.medication_list_id+'">'+data.medication_list[i].PatientMedicationList.medication+quantity+unit+route+frequency+rx_alt+direction+source+'</div>';
                
                html += '<div style="float:left;">,&nbsp;Status:&nbsp;</div><div class="editable_field" medication="'+data.medication_list[i].PatientMedicationList.medication+'" medication_list_id="'+data.medication_list[i].PatientMedicationList.medication_list_id+'" status_value="'+data.medication_list[i].PatientMedicationList.status+'" medication_list_id="'+data.medication_list[i].PatientMedicationList.medication_list_id+'" style="float:left;">'+data.medication_list[i].PatientMedicationList.status+'</div>';
                
                var refill_value = parseInt(data.medication_list[i].PatientMedicationList.refill_allowed);
                
				<?php if($isRefillEnable): ?>
				<?php if($rx_setup == 'Electronic_Dosespot'): ?>
					var display_refill = 'display:none;"';
                if(data.medication_list[i].PatientMedicationList.status != 'Cancelled' &&  data.medication_list[i].PatientMedicationList.status != 'Discontinued')
                {
                    display_refill = '';
                }
					var refill_link=''; //var refill_link = '<a href="" class="refill_link">Refill</a>';
                html += '<div style="float:left;'+display_refill+'" class="refill_link_area" refill_value="1" medication_list_id="'+data.medication_list[i].PatientMedicationList.medication_list_id+'">, '+refill_link+'</div>';
				<?php else: ?>
                var display_refill = 'display:none;"';
                if(refill_value > 0 && data.medication_list[i].PatientMedicationList.status != 'Cancelled' &&  data.medication_list[i].PatientMedicationList.status != 'Discontinued')
                {
                    display_refill = '';
                }
                
                var refill_link = '<a href="javascript:void(0);" onclick="top.document.location=\'<?php echo $link_to_medication; ?>/medication_list_id:'+data.medication_list[i].PatientMedicationList.medication_list_id+'\'" class="refill_link">Refill</a>';
                html += '<div style="float:left;'+display_refill+'" class="refill_link_area" refill_value="'+refill_value+'" medication_list_id="'+data.medication_list[i].PatientMedicationList.medication_list_id+'">, '+refill_link+'</div>';
                <?php endif; ?>
				<?php endif; ?>
				
                html += '</td></tr>';
                
                $("#table_medication_listing").append(html);
            }
            
            $(".del_icon", $("#table_medication_listing")).click(function()
            {
                deleteMedication($(this).attr("itemvalue"));
            });
            
            $("#table_medication_listing tr").each(function()
            {
                $(this).attr("oriclass", "");
            });
            
            $("#table_medication_listing tr:even").each(function()
            {
                $(this).addClass("striped");
                $(this).attr("oriclass", "striped");
            });
            
            $("#table_medication_listing tr").each(function()
            {
                $('td', $(this)).each(function()
                {
                    $(this).css("cursor", "pointer");
                });            
            });
            
            $('.medication_row').click(function()
            {   
                resetMedsAllergyTableState();
                
                var parent_tr = $(this).parent().parent();
                parent_tr.removeClass("striped");
                parent_tr.addClass("table_highlight");
                
                var medication_list_id = $(this).attr("itemvalue");                    
                $('#form_area').html('');
                $("#imgLoadForm").show();
                $.post('<?php echo $this->Session->webroot; ?>encounters/medications_data/encounter_id:<?php echo $encounter_id; ?>/', 
                'medication_list_id='+medication_list_id, 
                function(data){
                  $('#form_area').show();
                  $('#form_area').html(data);
                  $("#imgLoadForm").hide();
                  if(typeof($ipad)==='object')$ipad.ready();
                });
                
            });
            
			<?php if($page_access == 'W'): ?>
            $('.editable_field').editable('<?php echo $this->Session->webroot; ?>patients/medication_list/patient_id:<?php echo $patient_id; ?>/task:update_status/',
            { 
                indicator : '<?php echo $html->image('ajax_loaderback.gif', array('alt' => '')); ?>',
                data   : " {'':'Select Status', 'Active':'Active','Inactive':'Inactive','Cancelled':'Cancelled', 'Discontinued':'Discontinued','Completed':'Completed'}",
                type   : "select",
                cssclass: "dynamic_select",
                submitdata  : function(value, settings) 
                {
                    var medication_list_id = $(this).attr("medication_list_id");
                    var status_value = $(this).attr("status");
                    
                    return {'data[medication_list_id]' : medication_list_id, 'data[status_value]' : status_value};
                },
                callback: function(value, settings)
                {
                    var medication_list_id = $(this).attr("medication_list_id");
                    
                    var refill_link_area = $('.refill_link_area[medication_list_id="'+medication_list_id+'"]');
                    
                    if(value == "Cancelled" || value == "Discontinued")
                    {
                        refill_link_area.hide();
                    }
                    else
                    {
                        if(refill_link_area.attr("refill_value") != "0")
                        {
                            refill_link_area.show();
                        }
                    }
                }
            });
			<?php endif; ?>
                    
            $('.linkto_dosespot').click(function()
            {
                  $('#tabs').tabs('url', 11, "<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan', 'encounter_id' => $encounter_id, 'from_meds_allergy' => 'yes')); ?>").tabs('select', 11); 
            });
        }
        else
        {
						$('#no_medication').removeAttr('disabled');
            var html = '<tr deleteable="true">';
            html += '<td colspan="2">Not taking any medication.</td>';
            html += '</tr>';
            
            $("#table_medication_listing").append(html);
        }
		var rows=$('#table_medication_listing').find('tbody tr.js-page').length;
		if(rows > 15)
		{
		jsPaginate_medication('#table_medication_listing');
		}
    }
    
    function resetMedication(items)
    {
        if(items == null)
        {
            var showAllMedication = ($('#show_all_medications').is(':checked'))?'yes':'no';    
            var show_surescripts = ($('#show_surescripts').is(':checked'))?'yes':'no';    
            var show_reported = ($('#show_reported').is(':checked'))?'yes':'no';    
            var show_prescribed = ($('#show_prescribed').is(':checked'))?'yes':'no';
            $.post(
                '<?php echo $this->Session->webroot; ?>encounters/meds_allergy/encounter_id:<?php echo $encounter_id; ?>/task:get_medications/show_all_medications:'+showAllMedication+'/show_surescripts:'+show_surescripts+'/show_reported:'+show_reported+'/show_prescribed:'+show_prescribed+'/', 
                '', 
                function(data)
                {
                    resetMedicationTable(data);
                },
                'json'
            );
        }
        else
        {
            resetMedicationTable(items);
        }
    }
	
	function getCurrentType()
	{
		var type = $('#allergy_type').val();
		
		return {'data[type]': type};
	}
    
    $(document).ready(function()
    {
        $("input").addClear();

        $("#medicationSearch").autocomplete('<?php echo $this->Session->webroot; ?>encounters/meds_list/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete/', {
            minChars: 2,
            max: 20,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		
		$("#medicationSearch").result(function(event, data, formatted)
        {
			$('#medicationSearchNorm').val(data[1]);
			$('#medicationSearch_medication_form').val(data[2]);
			$('#medicationSearch_medication_strength_value').val(data[3]);
			$('#medicationSearch_medication_strength_unit').val(data[4]);
			$('#medicationSearch_medication_id').val(data[5]);
        });
		
        $("#allergySearch").autocomplete('<?php echo $this->Session->webroot; ?>encounters/meds_list/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete2/', {
            minChars: 2,
            max: 20,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		
		$("#allergy").autocomplete('<?php echo $this->Session->webroot; ?>encounters/meds_list/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete2/', {
            minChars: 2,
            max: 20,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300,
			extraParams: getCurrentType()
        });

		$("#allergy").result(function(event, data, formatted)
        {
			$('#allergy_code').val(data[1]);
			$('#allergy_code_type').val(data[2]);
        });
        		
        resetAllergy(null);
        resetMedication(null);
        
        $("#medication_reconciliated").click(function()
        {
            if(this.checked == true)
            {
                var reviewed = 1;
            }
            else
            {
                var reviewed = 0;
            }            
            var formobj = $("<form></form>");
            formobj.append('<input name="data[submitted][id]" type="hidden" value="medication_list">');
            formobj.append('<input name="data[submitted][value]" type="hidden" value="'+reviewed+'">');    
            $.post('<?php echo $this->Session->webroot; ?>encounters/plan/encounter_id:<?php echo $encounter_id; ?>/task:updateReview/', formobj.serialize(), 
            function(data){}
            );
        });
        
        $("#show_all_allergies").click(function()
        {        
            resetAllergy(null);
        });        

        $("#show_all_medications").click(function()
        {        
            resetMedication(null);
        });
        $("#show_surescripts").click(function()
        {        
            resetMedication(null);
        });
        $("#show_reported").click(function()
        {        
            resetMedication(null);
        });
        $("#show_prescribed").click(function()
        {        
            resetMedication(null);
        });
        $('#print_medications').bind('click',function(a){                    
        a.preventDefault();
        var href = $(this).attr('href');
        $('.visit_summary_load').attr('src',href).fadeIn(400,function(){
        $('.iframe_close').show();
        $('.visit_summary_load').load(function(){
            $(this).css('background','white');
            
            });
        });
        });
		<?php echo $this->element('dragon_voice'); ?>
    });    

   
   function NoOtherAllergy()
   {
       postUrl='<?php echo $this->Session->webroot; ?>encounters/meds_allergy/encounter_id:<?php echo $encounter_id; ?>/task:update_medication_allergy/'
       //$("#OrderRecs").html(ajax_load).load(postUrl);     
       var val = (jQuery('#no_allergy').is(':checked'))?'1':'0';

       if(val == '1')
       {
           $('#allergies_text_search').css('display', 'none');
       }
       else
       {
           $('#allergies_text_search').css('display', 'table-row');
       }
       var formobj = $("<form></form>");
       formobj.append('<input name="data[submitted][id]" type="hidden" value="allergy_none">');
       formobj.append('<input name="data[submitted][value]" type="hidden" value="'+val+'">');
            
       $.post(postUrl, formobj.serialize(), function(data){ });
   }
   
   function NoMedication()
   {
       postUrl='<?php echo $this->Session->webroot; ?>encounters/meds_allergy/encounter_id:<?php echo $encounter_id; ?>/task:update_medication_allergy/'
       //$("#OrderRecs").html(ajax_load).load(postUrl);     
       var val = (jQuery('#no_medication').is(':checked'))?'1':'0';
  
       if(val == '1')
       {
           $('#row_medications_text_search').css('display', 'none');
       }
       else
       {
           $('#row_medications_text_search').css('display', 'table-row');
       }
       
       var formobj = $("<form></form>");
       formobj.append('<input name="data[submitted][id]" type="hidden" value="taking_medication">');
       formobj.append('<input name="data[submitted][value]" type="hidden" value="'+val+'">');
            
       $.post(postUrl, formobj.serialize(), function(data){ });
   }
</script>
<?php echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 13)); ?>
<style>
div.js-paging { margin:0px; } div.js-paging span { margin-right:5px; } div.js-paging span.curPage a{ color:#D54E21 }
</style>
    
<div style="float: left; width: 38%;">
    <div style="padding-right: 2px;">
        <form>
        <table cellpadding="0" cellspacing="0" class="form" width="100%">
            <tr>
              <td colspan="2">
            <?php if(isset($patient_checkin)): ?>
              <br /><br /><br />
            <?php endif;?>                   
                </td>
            </tr>
   			<tr>

                <td colspan="2" align="left">
				    <select id="allergy_type" name="allergy_type">
					    <?php                    
							$type_array = array("Drug", "Environment", "Food", "Inhalant", "Insect", "Plant");
							for ($i = 0; $i < count($type_array); ++$i)
							{
								echo "<option value=\"$type_array[$i]\" >".$type_array[$i]."</option>";
							}
						?>
					</select> <b>Allergies?</b> &nbsp;&nbsp; 
                <label for="no_allergy" class="label_check_box">
                    <input type='checkbox' id='no_allergy' name='no_allergy'<?php echo $no_allergy==1?'checked':''; ?> onclick='javascript: NoOtherAllergy();' >
                NONE
                </label>                    
                </td>
            </tr>
            <tr id="allergies_text_search" style="display: <?php echo $no_allergy==1?'none':'table-row'; ?>;">
                <td colspan="2">
                    <table cellpadding="0" cellspacing="0" class="form">
            <tr>
                <td colspan=2 style="text-align:right;padding-right: 95px">Reaction?</td>
            </tr>
                        <tr>
                            <td style="padding-right: 10px;"><input type="text" name="allergy" id="allergy" style="width:200px;" class="dragon" /> <input type="hidden" name="allergy_code" id="allergy_code" /><input type="hidden" name="allergy_code_type" id="allergy_code_type" />  <input type="text" name="allergyReaction" id="allergyReaction" style="width:100px;" class="dragon" /></td>
                            <td style="padding-right: 10px;"><a class="btn" href="javascript:void(0);" style="float: none;" onclick="addAllergy($('#allergy').val(),$('#allergyReaction').val(),$('#allergy_type').val(),$('#allergy_code').val(),$('#allergy_code_type').val());">Save</a></td>
                        </tr>
                    </table>
                </td>
            </tr>
			<tr>
                <td colspan="2">&nbsp;
                            <br /><br /><div id='allergy_rows'></div>
                </td>
            </tr>
            <tr>
               <td colspan="2" align="left"><b>Current Medications?</b> &nbsp;&nbsp;
                <label for="no_medication" class="label_check_box"> 
                    <input type='checkbox' id='no_medication' name='no_medication' <?php echo $no_medication==1?'checked':''; ?> onclick='javascript: NoMedication();' />
                NONE
                </label>
                    
                </td>
            </tr>
            <?php if($page_access == 'W'): ?>
            <tr id="row_medications_text_search" removeonread="true" style="display: <?php echo $no_medication==1?'none':'table-row'; ?>;">
                <td colspan="2">
                    <table cellpadding="0" cellspacing="0" class="form">
                        <tr>
                            <td colspan=2 style="text-align:right;padding-right: 60px"><!--Sig?-->&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="padding-right: 10px;">
                                <input type="text" name="medicationSearch" id="medicationSearch" style="width:316px;" />
                                <input type="hidden" name="medicationSearchNorm" id="medicationSearchNorm" />
                                <input type="hidden" name="medicationSearch_medication_form" id="medicationSearch_medication_form" />
                                <input type="hidden" name="medicationSearch_medication_strength_value" id="medicationSearch_medication_strength_value" />
                                <input type="hidden" name="medicationSearch_medication_strength_unit" id="medicationSearch_medication_strength_unit" />
                                <input type="hidden" name="medicationSearch_medication_id" id="medicationSearch_medication_id" />
                                <input type="text" name="medicationSig" id="medicationSig" style="width:70px; display: none;" />
                            </td>
                            <td style="padding-right: 10px;"><a class="btn" href="javascript:void(0);" style="float: none;" onclick="addMedication($('#medicationSearch').val(),$('#medicationSig').val(), $('#medicationSearchNorm').val(), $('#medicationSearch_medication_form').val(), $('#medicationSearch_medication_strength_value').val(), $('#medicationSearch_medication_strength_unit').val(),$('#medicationSearch_medication_id').val() );">Save</a></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            <?php endif; ?>
        </table>        
        </form>
    </div>
</div>
<div style="float: right; width: 62%; ">
<?php echo $this->element('patient_checkin_disp_box', array('patient_checkin' => @$patient_checkin, 'field' => 'allergies')); ?>
    <table cellpadding="0" cellspacing="0" align="right">
    <tr>
        <td style="padding-bottom:10px;">

            <label for="show_all_allergies" class="label_check_box">
            <input type="checkbox" class="ignore_read_acl" name="show_all_allergies" id="show_all_allergies" <?php if($show_all_allergies == 'yes') { echo 'checked="checked"'; } else { echo ''; } ?> />
            &nbsp;Show All Allergies
            </label>

       </td>
    </tr>
    </table>
    <table id="table_allergy_listing" cellpadding="0" cellspacing="0" class="small_table" >
        <tr deleteable="false">
            <th colspan="2">
                Allergies (reaction)
                <span id="imgLoadingAllergy" style="float: right; display:none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
            </th>
        </tr>
    </table>

  <?php echo $this->element('patient_checkin_disp_box', array('patient_checkin' => @$patient_checkin, 'field' => 'medications')); ?>    
    
   	<?php // added margin to add space between meds and allergies ?>
    <div style="float:left; width:100%; margin:30px 0 5px 0">
     
    <table cellpadding="0" cellspacing="0">
    <tr>
        <td>
            <label for="show_surescripts" class="label_check_box"><input type="checkbox" class="ignore_read_acl" name="show_surescripts" id="show_surescripts" checked="checked" />&nbsp;e-Rx History</label>&nbsp;&nbsp;
            </td>
            <td>
                <label for="show_reported" class="label_check_box"><input type="checkbox" class="ignore_read_acl" name="show_reported" id="show_reported" checked="checked" />&nbsp;Patient Reported</label>&nbsp;&nbsp;
            </td>
            <td>
                <label for="show_prescribed" class="label_check_box"><input type="checkbox" class="ignore_read_acl" name="show_prescribed" id="show_prescribed" checked="checked"  />&nbsp;Practice Prescribed</label>
        </td>
	<td style="width:40px">&nbsp;</td>
        <td>
        <label for="show_all_medications" class="label_check_box">
            <input type="checkbox" class="ignore_read_acl" name="show_all_medications" id="show_all_medications" />
            &nbsp;Show All Medications Hx
            </label>
        </td>
    </tr>        
        
        </table></div>

 
    <table id="table_medication_listing" cellpadding="0" cellspacing="0" class="small_table hasSortingArrow">
        <tr deleteable="false">
            <th colspan="2">
                Medications  <div style="float: right; margin-bottom: 5px; margin-right:2px">
                    <?php if ($isMobile): ?> 
                    <a class="pdf-btn" href="<?php echo $html->url(array('controller'=>'patients', 'action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'get_report_pdf')); ?>" target="_blank" > PDF <?php echo $html->image('pdf.png', array('alt' => 'Print')); ?></a>
                    <?php else: ?> 
                    <a href="<?php echo $html->url(array('controller'=>'patients', 'action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'get_report_html')); ?>" id="print_medications" ><?php echo $html->image('printer_icon.png', array('alt' => 'Print')); ?></a>
                    <?php endif;?> 
                </div>
				<span id="imgLoadingMedication" style="float: right; display:none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
            </th>
        </tr>
    </table>
    <table id="table_medication_reconciliated" style="margin-top:10px;">
        <?php
        foreach($reconciliated_fields as $field_item)
        {
            echo '<tr><td style="padding-bottom:10px;">'.$field_item.'</td></tr>';
        }
        ?>
    </table>
    </div>
    
<div style="clear: both;">&nbsp;</div>    

<span id="imgLoadForm" style="float: center; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
<div style="text-align: left; width: 100%; margin-top: 10px; float:left; display: none;" id="form_area">

	
</div>
<?php if ($prescriptionAuth): ?> 
		<div id="prescription-auth-modal" title="Prescribing Authority">
			<p>
				Choose provider
			</p>	
				<select id="authorizing-user-id" name="authorizing-user-id">
					<?php foreach($prescriptionAuth as $u):  ?>
					<option value="<?php echo $u['UserAccount']['user_id']; ?>"><?php echo Sanitize::html($u['UserAccount']['full_name']); ?></option>
					<?php endforeach;?>
				</select>
				
        <div class="actions">
            <ul>
                <li><a href="" id="auth-cancel">Cancel</a></li>
                <li><a href="" id="auth-continue">Continue</a></li>
               </ul>
        </div>			
			
		</div>
			<script type="text/javascript">
					$(function(){
						var $dialog = $('#prescription-auth-modal');
						
						$dialog.dialog({
							modal: true,
							autoOpen: false
						});
						
						$('#table_medication_listing').on('click', '.refill_link',function(evt){
							evt.preventDefault();
							$dialog.dialog('open');
						});
						
						$dialog.find('#auth-cancel').click(function(evt){
							evt.preventDefault();
							$dialog.dialog('close');
						});
						
						$dialog.find('#auth-continue').click(function(evt){
							evt.preventDefault();
							var userId = $.trim($dialog.find('#authorizing-user-id').val());
							
							if (!userId) {
								return false;	
							}
							
							var url = '<?php echo $link_to_medication; ?>/encounter_id:<?php echo $encounter_id; ?>/dosespot:1/prescriber:' + userId;
							
							$dialog.dialog('close');
							
							window.top.document.location = url;
							
						});
						
						
					});
				</script>
<?php else:?> 				
			<script type="text/javascript">
					$(function(){
						$('#table_medication_listing').on('click', '.refill_link',function(evt){
							evt.preventDefault();
							var url = '<?php echo $link_to_medication; ?>/encounter_id:<?php echo $encounter_id; ?>/dosespot:1';
							window.top.document.location = url;
						});
						
					});
				</script>		
<?php endif;?> 
