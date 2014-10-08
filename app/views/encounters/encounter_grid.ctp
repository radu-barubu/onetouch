<div id="encounter_area" class="tab_area">
    <table id="encounter_table" cellpadding="0" cellspacing="0" class="listing">
    <tr>
        <th><?php echo $paginator->sort('Date', 'EncounterMaster.encounter_date', array('model' => 'EncounterMaster', 'class' => 'ajax'));?></th>
        <?php if ( $totalLocations > 1 ) { ?>
        <th><?php echo $paginator->sort('Location', 'EncounterMaster.location_name', array('model' => 'EncounterMaster', 'class' => 'ajax'));?></th>
        <?php }?>
        <th><?php echo $paginator->sort('Encounter Type', 'EncounterMaster.visit_type_id', array('model' => 'EncounterMaster', 'class' => 'ajax'));?></th>         
        <th><?php echo $paginator->sort('Provider', 'EncounterMaster.provider_full_name', array('model' => 'EncounterMaster', 'class' => 'ajax'));?></th>
        <th><?php echo $paginator->sort('Patient Name', 'EncounterMaster.patient_full_name', array('model' => 'EncounterMaster', 'class' => 'ajax'));?></th>
        <th><?php echo $paginator->sort('MRN', 'EncounterMaster.patient_mrn', array('model' => 'EncounterMaster', 'class' => 'ajax'));?></th>
        <th><?php echo $paginator->sort('Gender', 'EncounterMaster.patient_gender', array('model' => 'EncounterMaster', 'class' => 'ajax'));?></th>
        <th><?php echo $paginator->sort('Status', 'EncounterMaster.encounter_status', array('model' => 'EncounterMaster', 'class' => 'ajax'));?></th>
    </tr>
    <?php
    $i = 0;
    foreach ($encounters as $encounter):
    
    		if($checkin_items):	
		  //seek patient checkin from patient portal if exists
		  foreach($checkin_items as $checkin_item):
		     $patient_checkin_id = ($checkin_item['PatientCheckinNotes']['calendar_id'] === $encounter['EncounterMaster']['calendar_id'])? $checkin_item['PatientCheckinNotes']['patient_checkin_id'] : "";
		  endforeach;
		endif;
    ?>
		<?php
			$urlParams = array('action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter['EncounterMaster']['encounter_id']);
			if(!empty($patient_checkin_id)) {
			 	$urlParams['patient_checkin_id'] = $patient_checkin_id;
			}
			$editLink = $html->url($urlParams, array('escape' => false));
			$middle_name = ($encounter['EncounterMaster']['patient_middlename'])? $encounter['EncounterMaster']['patient_middlename'].' ' : '';
		?>
        <tr editlink="<?php echo  $editLink?>">
            <td><?php echo __date($global_date_format, strtotime($encounter['EncounterMaster']['encounter_date'])); ?></td>
            <?php if ( $totalLocations >1 ) { ?>
            <td><?php echo $encounter['EncounterMaster']['location_name']; ?></td>
            <?php } ?>
            <td><?php echo isset($encounterTypes[$encounter['EncounterMaster']['visit_type_id']]) ? $encounterTypes[$encounter['EncounterMaster']['visit_type_id']] : 'Default'; ?></td>
            <td><?php echo $encounter['EncounterMaster']['provider_full_name']; ?></td>
            <td><?php echo $encounter['EncounterMaster']['patient_firstname'].' ' . $middle_name . $encounter['EncounterMaster']['patient_lastname']. " (" . __date($global_date_format, strtotime($encounter['EncounterMaster']['patient_dob'])).")"; ?></td>
            <td><?php echo $encounter['EncounterMaster']['patient_mrn']; ?></td>
            <td><?php echo ($encounter['EncounterMaster']['patient_gender'] == "M") ? "Male" : "Female"; ?></td>
            <td><?php echo $encounter['EncounterMaster']['encounter_status']; ?></td>
        </tr>
    <?php endforeach; ?>
    </table>
    
        <div class="paging">
            <?php echo $paginator->counter(array('model' => 'EncounterMaster', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
                if($paginator->hasPrev('EncounterMaster') || $paginator->hasNext('EncounterMaster'))
                {
                    echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                }
            ?>
            <?php 
                if($paginator->hasPrev('EncounterMaster'))
                {
                    echo $paginator->prev('<< Previous', array('model' => 'EncounterMaster', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
            <?php echo $paginator->numbers(array('model' => 'EncounterMaster', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
                if($paginator->hasNext('EncounterMaster'))
                {
                    echo $paginator->next('Next >>', array('model' => 'EncounterMaster', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
        </div>
</div>
