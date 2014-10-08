<?php

switch ($poc_type) {
	
	case 'labs':
		
		$title = 'Lab Categories';
		$urlAction = 'in_house_work_labs';
		break;
	
	case 'radiology':
		
		$title = 'Radiology Categories';
		$urlAction = 'in_house_work_radiology';
		break;
	
	case 'procedures':
		
		$title = 'Procedure Categories';
		$urlAction = 'in_house_work_procedures';
		break;
	
	case 'immunizations':
		
		$title = 'Immunization Categories';
		$urlAction = 'in_house_work_immunizations';
		break;
	
	case 'injections':
		
		$title = 'Injection Categories';
		$urlAction = 'in_house_work_injections';
		break;
	
	case 'meds':
		
		$title = 'Med Categories';
		$urlAction = 'in_house_work_meds';
		break;
	
	case 'supplies':
		
		$title = 'Supply Categories';
		$urlAction = 'in_house_work_supplies';
		break;
	
	
	default:
		break;
}


?>
    <div class="title_area">
        <div class="title_text">
					<h4><?php echo $title; ?></h4>
							<a href="<?php echo $html->url(array('controller' => 'encounters', 'action' => $urlAction, 'encounter_id' => $encounter_id, 'all_categories' => 1)); ?>" class="in_house_work_categories <?php echo ($currentCategory === true) ? 'active': ''; ?>"  style="float: none;">All Categories</a>
							<?php foreach($categories as $c):?>
							<a href="<?php echo $html->url(array('controller' => 'encounters', 'action' => $urlAction, 'encounter_id' => $encounter_id, 'category' => $c)); ?>" class="in_house_work_categories <?php echo ($currentCategory === $c) ? 'active': ''; ?>" style="float: none;"><?php echo Sanitize::html($c); ?></a>
							<?php endforeach;?>
					
        </div>
    </div>
	<script type="text/javascript">
	$(function(){
        $('.in_house_work_categories').click(function(evt)
        {
						evt.preventDefault();
            $(".tab_area").html('');
            $("#imgLoad").show();
            loadTab($(this),$(this).attr('href'));
        });		
	});
	</script>
