<table border='1' style="margin-top:10px;vertical-align:none;">
					<tr style="vertical-align:none;">
						
				<td style="width:119px;">Document Type: </td>
				<td>
				
					<?php
					$options =array();
					$doc_types_array = $doc_types;
					
					for ($i = 0; $i < count($doc_types_array); ++$i)
					{
						$options[$doc_types_array[$i]] = $doc_types_array[$i];
						//$options .= "<option value=\"$doc_types_array[$i]\">".$doc_types_array[$i]."</option>";
					}
					$options['Online Form'] = "Online Form";
					//$options .= "<option value='Online Form'>Online Form</option>";
					 
					?>
					<!--
					<select id="doc_type" style="border: 1px solid #AAAAAA;margin-left:5px;margin-right:5px;padding: 5px;">
					<option value="">All</option>
						<?php //echo $options; ?>
					</select> -->
					<?php 
					
					if(!empty($saved_search_array)){
					//$options = explode(",",$saved_search_array);
					echo $form->input('doc_types', array('type' => 'select','multiple'=>'true','options' => $options,'selected'=>$saved_search_array, 'style'=>'width:200px;','label' => false, 'id' => 'doc_types'));
					} else {
						if(!empty($doc)){
							
							echo $form->input('doc_types', array('type' => 'select','multiple'=>'true','options' => $options, 'selected' => $doc, 'style'=>'width:200px;','label' => false, 'id' => 'doc_types'));
						} else { 
							
							echo $form->input('doc_types', array('type' => 'select','multiple'=>'true','options' => $options, 'selected' => '', 'style'=>'width:200px;','label' => false, 'id' => 'doc_types'));
						}
					}
					 ?>

				</td>
						<td style="padding-left:10px;">Service Date:</td>				
				<td><?php echo $this->element("date", array('name'=>'from_date','id' => 'from_date', 'value' => '', 'required' => false)); ?>
				</td>
				<td><?php echo $this->element("date", array('name'=>'to_date','id' => 'to_date', 'value' => '', 'required' => false)); ?>
				</td>
				<td style="padding-left:10px;">Status: </td><td>
					<select id='doc_status' style="border:1px solid #AAAAAA;margin-left:5px;margin-right:5px;padding:5px;" >
							<option value="">All</option>
							<?php
								$statuses_array = array("Open", "Reviewed");
								for ($i = 0; $i < count($statuses_array); ++$i)
								{
									echo "<option value=\"$statuses_array[$i]\" >".$statuses_array[$i]."</option>";
								}
							?>
							</select>
					</td>
					
					<td><input type="button"  id="save_filter" class="btn" value="Save"></td>
			</tr>
		</table>
