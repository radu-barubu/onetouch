<?php

$patient_id = isset($this->params['named']['patient_id']) ? $this->params['named']['patient_id'] : '' ;

?>
<?php if(count($pe_images) == 0): ?>
    There is no available picture to display.
<?php else: ?>
<script language="javascript" type="text/javascript">
        var set_pe_photo_comment_link = '<?php echo $html->url(array("controller" => "encounters", "action" => "pe", "task" => "set_image_comment")); ?>';
        $(document).ready(function(){
		
		$('.summary').bind('click',function(event)
				{
					event.preventDefault();
					var href = $(this).attr('href');
					$('.visit_summary_load').attr('src',href).fadeIn(400,
					function()
					{
							$('.iframe_close').show();
							$('.visit_summary_load').load(function()
							{
									$(this).css('background','white');

							});
					});
				});
				
				$('.iframe_close').bind('click',function(){
				$(this).hide();
				$('.visit_summary_load').attr('src','').fadeOut(400,function(){
					$(this).removeAttr('style');
					});
				});
                
				// added Enter key event for blurring
			$('#patient-pictures').find('.pe_image_item_comment').keypress(function(e){
				if(e.which == 13){
					$(this).blur();
					return false;
				}
			});
                
            $('#patient-pictures')
                .find('.pe_image_item_comment')
                    .blur(function(){
                        var
                            data = $(this).attr('rel').split('|'),
                            peImageId = data[1],
                            encounterId = data[0],
                            peImageComment = $(this).val();

                        $.post(set_pe_photo_comment_link+'/encounter_id:'+encounterId, {
                            id: peImageId,
                            comment: peImageComment
                        });
                    })

            $('#patient-pictures').find('.cloud-zoom').CloudZoom();                                
                                
        });
</script>
<?php
    $paginator->options(array('url' => $this->passedArgs));
?>
<div id="pe_images_area" class="tab_area">
    
    <form>
        <ul id="patient-pictures">
            <?php $ct = 1; ?> 
            <?php foreach($pe_images as $peImg):?>
            <?php
						
							$otherPath = $paths['patients'] . $patient_id . DS . 'images' . DS . $peImg['EncounterPhysicalExamImage']['encounter_id'] . DS;
						
             $image_path = UploadSettings::existing(
							 $paths['encounters'].$peImg['EncounterPhysicalExamImage']['image'],
							  $otherPath.$peImg['EncounterPhysicalExamImage']['image']
						 );
						 
             if(file_exists($image_path)) 
             { ?>
            <li class="patient-picture-item">
            <div style="padding-left:230px;">
            		<!-- added delete button for image to delete with permission for only Practice Admin -->
                    <?php if($user['role_id'] == EMR_Roles::PRACTICE_ADMIN_ROLE_ID || $user['role_id'] == EMR_Roles::SYSTEM_ADMIN_ROLE_ID){ ?>
                	<a href="javascript:void(0)" class="del_pat_img_data" onclick="delPEPhoto('<?php echo $html->url(array("controller" => "patients", "action" => "pictures", "img_id" => $peImg['EncounterPhysicalExamImage']['physical_exam_image_id'] , "task" => "delete_image")); ?>','<?php echo $peImg['EncounterPhysicalExamImage']['physical_exam_image_id'] ?>','<?php echo addslashes($image_path); ?>')">delete</a>
                    <?php } ?>
                    
							<?php if(intval($peImg['EncounterPhysicalExamImage']['encounter_id'])): ?> 
							 <?php  echo $html->link($html->image("white_icon.png"), array('controller' => 'encounters', 'action' => 'superbill', 'encounter_id' => $peImg['EncounterPhysicalExamImage']['encounter_id'], 'task' => 'get_report_html'), array('class' => 'summary', 'escape' => false, 'title' => 'Visit Summary')); ?>
							<?php endif;?> 
                </div>
                <div class="patient-picture-image-wrap" style="clear:both">
                    <?php 
                        $position = 'right';
                    
                        if (!($ct++ % 3)) {
                            $position = 'left';
                        }
                    
                    ?> 
									<a href="<?php echo UploadSettings::toURL($image_path); ?>" rel="position: '<?php echo $position; ?>', zoomWidth: '300', zoomHeight: '300'" class="cloud-zoom"><img src="<?php echo UploadSettings::toURL($image_path); ?>" class="patient-picture-image" /></a>
                </div>
                <br />
                <input style="margin-left:30px;" type="text" name="pe_image_item_comment[<?php echo $peImg['EncounterPhysicalExamImage']['physical_exam_image_id']; ?>]" value="<?php echo htmlentities($peImg['EncounterPhysicalExamImage']['comment']); ?>" class="pe_image_item_comment" rel="<?php echo $peImg['EncounterPhysicalExamImage']['encounter_id']; ?>|<?php echo $peImg['EncounterPhysicalExamImage']['physical_exam_image_id']; ?>" />
            </li>
            <?php } ?>
            <?php endforeach;?> 
        </ul>
        <br style="clear: both;" />
        
    </form>

    <div style="width: 60%; float: right; margin-top: 0px;">
        <div class="paging">
            <?php echo $paginator->counter(array('model' => 'EncounterPhysicalExamImage', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
                if($paginator->hasPrev('EncounterPhysicalExamImage') || $paginator->hasNext('EncounterPhysicalExamImage'))
                {
                    echo '  &mdash;  ';
                }
            ?>
            <?php 
                if($paginator->hasPrev('EncounterPhysicalExamImage'))
                {
                    echo $paginator->prev('<< Previous', array('model' => 'EncounterPhysicalExamImage', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
            <?php echo $paginator->numbers(array('model' => 'EncounterPhysicalExamImage', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
                if($paginator->hasNext('EncounterPhysicalExamImage'))
                {
                    echo $paginator->next('Next >>', array('model' => 'EncounterPhysicalExamImage', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
        </div>
    </div>
</div>
<?php endif; ?>
