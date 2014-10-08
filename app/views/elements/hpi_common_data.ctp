<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


$widget_class = isset($widget_class) ? $widget_class : 'common_hpi_info';

?>
<?php if (!empty($common_data) && isset($common_data[$hpi_element])): ?> 
                                    <div class="<?php echo $widget_class ?>" style="text-align: right;">
	                           <?php echo $this->Html->link($this->Html->image("add.png", array("alt" => "Show Common ".ucwords($hpi_element))), "/", array('class' => 'show_hpi_selection', "alt" => "Show Common ".ucwords($hpi_element), 'escape' => false)); ?>
                                      <div>
                                            <select class="common_hpi_selection" name="common_hpi_selection">
                                                <option value=""></option>
                                                <?php foreach ($common_data[$hpi_element] as $v): 
                                                    $v = htmlentities($v);
                                                ?> 
                                                <option value="<?php echo $v; ?>"><?php echo $v; ?></option>
                                                <?php endforeach; ?> 
                                            </select>
                                            <a href="" class="cancel_hpi_selection btn no-float">Cancel</a>
                                        </div>
                                    </div>
<?php endif;?> 