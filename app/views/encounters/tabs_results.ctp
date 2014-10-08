<?php echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 18)); ?>
            <a href="javascript:void(0);" class="btn section_btn" url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_lab', 'encounter_id' => $encounter_id)); ?>">Labs</a>
            <a href="javascript:void(0);" class="btn section_btn" url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_radiology', 'encounter_id' => $encounter_id)); ?>">Radiology</a>
            <a href="javascript:void(0);" class="btn section_btn" url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_procedures', 'encounter_id' => $encounter_id)); ?>">Procedures</a>
			<a href="javascript:void(0);" class="btn section_btn" url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_immunizations', 'encounter_id' => $encounter_id)); ?>">Immunization</a>
			<a href="javascript:void(0);" class="btn section_btn" url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_injections', 'encounter_id' => $encounter_id)); ?>">Injections</a>
			<a href="javascript:void(0);" class="btn section_btn" url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_meds', 'encounter_id' => $encounter_id)); ?>">Meds</a>
			<br><br>
