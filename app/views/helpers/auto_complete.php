<?php

class AutoCompleteHelper extends AppHelper 
{
	var $helpers = array('Html');
	
	function updateAutocomplete($model, $key, $value, $save)
	{
		$this->AutocompleteOption =& ClassRegistry::init('AutocompleteOption');
		
		$data = array();
		$data['autocomplete_model'] = $model;
		$data['autocomplete_keyfield'] = $key;
		$data['autocomplete_valuefield'] = $value;
		$data['autocomplete_save'] = ($save) ? "1" : "0";
		
		$search_result = $this->AutocompleteOption->find(
				'first', 
				array(
					'conditions' => array(
						'AutocompleteOption.autocomplete_model' => $data['autocomplete_model'], 
						'AutocompleteOption.autocomplete_keyfield' => $data['autocomplete_keyfield'],
						'AutocompleteOption.autocomplete_valuefield' => $data['autocomplete_valuefield'],
						'AutocompleteOption.autocomplete_save' => $data['autocomplete_save']
					),
					'order' => array('AutocompleteOption.autocomplete_orderby' => 'DESC')
				)
		);
		
		if(count($search_result) > 0)
		{
			return $search_result['AutocompleteOption']['autocomplete_id'];
		}
		else
		{
			$this->AutocompleteOption->create();
			$this->AutocompleteOption->save($data);
			
			return $this->AutocompleteOption->getLastInsertID();
		}
	}
	
    function createAutocomplete($options = array()) 
	{
		$autocomplete_id = $this->updateAutocomplete($options['Model'], $options['key_id'], $options['key_value'], $options['save']);
		
		if($options['required'])
		{
			$required_val = 'class="required"';
		}
		else
		{
			$required_val = '';
		}
		
		$current_controller = (strlen($this->params['controller']) > 0) ? $this->params['controller'] : "";
		
		$output = '
        <input notification="autocomplete_notification'.$options['field_id'].'" fieldid="'.$options['field_id'].'" type="text" name="'.$options['field_name'].'" id="'.$options['field_id'].'" '.$required_val.' value="'.$options['init_value'].'" style="width: '.$options['width'].';"  />
        <div id="autocomplete_notification'.$options['field_id'].'" class="a_notify">
            <div id="autocomplete_notification_details'.$options['field_id'].'">
				The value you entered is not found in autocomplete database. Would you like to save it?
				<div class="act_button">
					<a id="link_autocomplete_yes_'.$options['field_id'].'" href="javascript:void(0);">Yes</a>
					<a id="link_autocomplete_no_'.$options['field_id'].'" href="javascript:void(0);">No</a>
				</div>
			</div>
			<div style="clear: both;"></div>
			<div id="autocomplete_notification_save'.$options['field_id'].'">
				' . $this->Html->image('ajax_loaderback.gif', array('alt' => 'Loading...')) . ' Saving...
			</div>
        </div>
        <script language="javascript" type="text/javascript">
			/*var delay = (function(){
			  var timer = 0;
			  return function(callback, ms){
				clearTimeout (timer);
				timer = setTimeout(callback, ms);
			  };
			})();*/
			function save_autocomplete(){
				if($("#autocomplete_notification'.$options['field_id'].'").css("display") == "block"){
					var formobj = $("<form></form>");
					formobj.append(\'<input name="data[AutocompleteCache][autocomplete_id]" type="hidden" value="'.$autocomplete_id.'">\');
					formobj.append(\'<input name="data[AutocompleteCache][cache_item]" type="hidden" value="\'+$("#'.$options['field_id'].'").val()+\'">\');
					
					$.post(
						"' . $this->Html->url(array('controller' => $current_controller, 'action' => 'autocomplete', 'task' => 'save')) .'", 
						formobj.serialize(), 
						function(data)
						{
							$("#frm").submit();
						},
						"json"
					);
				}
				else{
					$("#frm").submit();
				}
				return false;
			}
            $(document).ready(function()
            {
                $("input").addClear();
				
				// added keyup event and condition for complaints to save automatically and do not prompt from user
				var p = "'.$options['field_id'].'";
				if(p == "complaint"){
					$("#autocomplete_notification'.$options['field_id'].'").css(
						{position:"absolute",height:0,padding:0}
					);
					
					/*$("#'.$options['field_id'].'").keyup(function(e){
						
						$("#'.$options['field_id'].'").ajaxStop(function(){
							if(e.which != 13){
								delay(function(){
									if($("#autocomplete_notification'.$options['field_id'].'").css("display") == "block"){
										$("#frm").attr("onsubmit","return save_autocomplete(this)");
									}
									else{
										$("#frm").removeAttr("onsubmit");
									}
								}, 800 );
							}
						});
					});*/
				}
                
                $("#'.$options['field_id'].'").autocomplete("' . $this->Html->url(array('controller' => $current_controller, 'action' => 'autocomplete', 'autocomplete_id' => $autocomplete_id)) .'", {
                    minChars: 2,
		    cacheLength: 1
                });
				
				$("#link_autocomplete_yes_'.$options['field_id'].'").click(function()
				{
					$("#autocomplete_notification_details'.$options['field_id'].'").hide();
					$("#autocomplete_notification_save'.$options['field_id'].'").show();
					
					var formobj = $("<form></form>");
					formobj.append(\'<input name="data[AutocompleteCache][autocomplete_id]" type="hidden" value="'.$autocomplete_id.'">\');
					formobj.append(\'<input name="data[AutocompleteCache][cache_item]" type="hidden" value="\'+$("#'.$options['field_id'].'").val()+\'">\');
					
					$.post(
						"' . $this->Html->url(array('controller' => $current_controller, 'action' => 'autocomplete', 'task' => 'save')) .'", 
						formobj.serialize(), 
						function(data)
						{
							$("#autocomplete_notification'.$options['field_id'].'").slideUp("slow");
						},
						"json"
					);
				});
				
				$("#link_autocomplete_no_'.$options['field_id'].'").click(function()
				{
					$("#autocomplete_notification'.$options['field_id'].'").slideUp("slow");
				});
            });
        
        </script>
        ';
        
        return $output;
    }
}

?>