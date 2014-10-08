<?php echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 7)); ?>

        <div class="title_area">
					<?php if (isset($subHeadings['Medical History']['hide']) && !intval($subHeadings['Medical History']['hide'])): ?> 
            <a href="javascript:void(0);" class="btn section_btn" url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'hx_medical', 'encounter_id' => $encounter_id)); ?>"><?php echo (isset($subHeadings['Medical History']['name'])) ? htmlentities($subHeadings['Medical History']['name'])  : 'Medical History';?></a>
					<?php endif;?> 
					<?php if (isset($subHeadings['Surgical History']['hide']) && !intval($subHeadings['Surgical History']['hide'])): ?> 
            <a href="javascript:void(0);" class="btn section_btn" url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'hx_surgical', 'encounter_id' => $encounter_id)); ?>"><?php echo (isset($subHeadings['Surgical History']['name'])) ? htmlentities($subHeadings['Surgical History']['name'])  : 'Surgical History';?></a>
					<?php endif;?> 
					<?php if (isset($subHeadings['Social History']['hide']) && !intval($subHeadings['Social History']['hide'])): ?> 
            <a href="javascript:void(0);" class="btn section_btn" url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'hx_social', 'encounter_id' => $encounter_id)); ?>"><?php echo (isset($subHeadings['Social History']['name'])) ? htmlentities($subHeadings['Social History']['name'])  : 'Social History';?></a>
					<?php endif;?> 
					<?php if (isset($subHeadings['Family History']['hide']) && !intval($subHeadings['Family History']['hide'])): ?> 
            <a href="javascript:void(0);" class="btn section_btn"  url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'hx_family', 'encounter_id' => $encounter_id)); ?>"><?php echo (isset($subHeadings['Family History']['name'])) ? htmlentities($subHeadings['Family History']['name'])  : 'Family History';?></a>
					<?php endif;?> 
			<?php
			if ($obgyn_feature_include_flag == 1 && $gender == "F")
			{
				?><?php if (isset($subHeadings['Ob/Gyn History']['hide']) && !intval($subHeadings['Ob/Gyn History']['hide'])): ?> 
						<a href="javascript:void(0);" class="btn section_btn"  url="<?php echo $html->url(array('controller' => 'encounters', 'action' => 'hx_obgyn', 'encounter_id' => $encounter_id)); ?>"><?php echo (isset($subHeadings['Ob/Gyn History']['name'])) ? htmlentities($subHeadings['Ob/Gyn History']['name'])  : 'Ob/Gyn History';?></a>
						<?php endif; ?>
						<?php
						}
			?>
        </div>