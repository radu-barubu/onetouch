<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

extract($settings);

?>
<link rel="stylesheet" type="text/css" href="<?php echo $this->Session->webroot; ?>css/colorPicker.css" />
<script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/jquery.colorPicker.min.js"></script>
<script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/sections/color_picker_init.js"></script>
<style>
div.colorPicker-palette {
  border: 1px solid <?php echo $display_settings['color_scheme_properties']['listing_border']; ?>;
}
</style>
<div style="overflow: hidden;">
 <h2>Preferences</h2>
    <?php echo $this->element('preferences_display_links'); ?>
    <form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
    	<input type="hidden" name="data[PreferencesDisplay][preferences_display_id]" id="preferences_display_id" value="<?php echo $preferences_display_id; ?>" />
        <input type="hidden" name="data[task]" id="task" value="save" />
        <table cellpadding="0" cellspacing="0" class="styles">
            <tr>
                <td colspan="2"><h3><label>General Appearance</label></h3></td>
            </tr>
            <tr>
                <td width="215"><label>Color Scheme:</label></td>
                <td>
                	<table cellpadding="0" cellspacing="0">
                    	<tr>
                        	<?php
							foreach($color_schemes as $scheme)
							{
								?>
                                <td style="padding-right:15px">
                                       <label style="position:relative; padding:7px 40px 7px 10px" class="label_check_box" for="color_scheme_<?php echo $scheme['scheme']; ?>" title="<?php echo $scheme['scheme']; ?>"> 
                                       <input id="color_scheme_<?php echo $scheme['scheme']; ?>" name="data[PreferencesDisplay][color_scheme]" value="<?php echo $scheme['scheme']; ?>"  type="radio" <?php if($color_scheme == $scheme['scheme']) { echo 'checked="checked"'; } ?> />
                                       <img src="<?php echo $this->Session->webroot; ?>img/themes/color_scheme/<?php echo $scheme['img']; ?>" style="position:absolute; top:3px; right:3px; border:1px solid white;" width="30" height="30"></label>
                                </td>
                                <?php
							}
							
							?>
                        </tr>
                    
                    </table>
                
                </td>
            </tr>
        </table>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2"><h3><label>Links</label></h3></td>
            </tr>
            <tr>
                <td style="vertical-align:middle;"><label>Editable Text Color:</label></td>
                <td style="padding: 5px 0px;"><input id="editable_text_color" name="data[PreferencesDisplay][editable_text_color]" type="text" value="<?php echo $editable_text_color; ?>" size="8" class="simple_color_picker" bg_element="content" contrast="<?php echo @$fields_contrast['editable_text_color']; ?>" /></td>
            </tr>
            <tr>
                <td style="vertical-align:middle;"><label>Editable Hightlight Color:</label></td>
                <td style="padding: 5px 0px;"><input id="editable_hightlight_color" name="data[PreferencesDisplay][editable_hightlight_color]" type="text" value="<?php echo $editable_hightlight_color; ?>" size="8" class="simple_color_picker" bg_element="content" contrast="<?php echo @$fields_contrast['editable_hightlight_color']; ?>" accepted_contrast_limit="300" /></td>
            </tr>
            <tr>
                <td width="215" style="vertical-align:middle;"><label>Link Color:</label></td>
                <td style="padding: 5px 0px;"><input id="link_color" name="data[PreferencesDisplay][link_color]" type="text" value="<?php echo $link_color; ?>" size="8" class="simple_color_picker" bg_element="content" contrast="<?php echo @$fields_contrast['link_color']; ?>" /></td>
            </tr>
            <tr>
                <td style="vertical-align:middle;"><label>Visited Color:</label></td>
                <td style="padding: 5px 0px;"><input id="link_visited_color" name="data[PreferencesDisplay][link_visited_color]" type="text" value="<?php echo $link_visited_color; ?>" size="8" class="simple_color_picker" bg_element="content" contrast="<?php echo @$fields_contrast['link_visited_color']; ?>" /></td>
            </tr>
            <tr>
                <td style="vertical-align:middle;"><label>Hover Color:</label></td>
                <td style="padding: 5px 0px;"><input id="link_hover_color" name="data[PreferencesDisplay][link_hover_color]" type="text" value="<?php echo $link_hover_color; ?>" size="8" class="simple_color_picker" bg_element="content" contrast="<?php echo @$fields_contrast['link_hover_color']; ?>" /></td>
            </tr>
            <tr>
                <td style="vertical-align:middle;"><label>Active Color:</label></td>
                <td style="padding: 5px 0px;"><input id="link_active_color" name="data[PreferencesDisplay][link_active_color]" type="text" value="<?php echo $link_active_color; ?>" size="8" class="simple_color_picker" bg_element="content" contrast="<?php echo @$fields_contrast['link_active_color']; ?>" /></td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2"><h3><label>Buttons</label></h3></td>
            </tr>
            <tr>
                <td style="vertical-align:middle;"><label>Background Color:</label></td>
                <td style="padding: 5px 0px;">
                	<table cellpadding="0" cellspacing="0">
                    	<tr>
                        	<td><input id="button_background_color_from" name="data[PreferencesDisplay][button_background_color_from]" type="text" value="<?php echo $button_background_color_from; ?>" size="8" class="simple_color_picker" bg_element="content" contrast="<?php echo @$fields_contrast['button_background_color_from']; ?>" accepted_contrast_limit="250" /></td>
                            <td width="40" align="center" style="vertical-align:middle;">to</td>
                            <td><input id="button_background_color_to" name="data[PreferencesDisplay][button_background_color_to]" type="text" value="<?php echo $button_background_color_to; ?>" size="8" class="simple_color_picker" bg_element="content" contrast="<?php echo @$fields_contrast['button_background_color_to']; ?>" accepted_contrast_limit="250" /></td>
                        </tr>
                    </table>
                	
                </td>
            </tr>
            <tr>
                <td style="vertical-align:middle;"><label>Border Color:</label></td>
                <td style="padding: 5px 0px;"><input id="button_border_color" name="data[PreferencesDisplay][button_border_color]" type="text" value="<?php echo $button_border_color; ?>" size="8" class="simple_color_picker" bg_element="content" contrast="<?php echo @$fields_contrast['button_border_color']; ?>" /></td>
            </tr>
            <tr>
                <td style="vertical-align:middle;"><label>Text Color:</label></td>
                <td style="padding: 5px 0px;"><input id="button_text_color" name="data[PreferencesDisplay][button_text_color]" type="text" value="<?php echo $button_text_color; ?>" size="8" class="simple_color_picker" bg_element="content" contrast="<?php echo @$fields_contrast['button_text_color']; ?>" /></td>
            </tr>
            <tr>
                <td style="vertical-align:middle;"><label>Hover Border Color:</label></td>
                <td style="padding: 5px 0px;"><input id="button_hover_border_color" name="data[PreferencesDisplay][button_hover_border_color]" type="text" value="<?php echo $button_hover_border_color; ?>" size="8" class="simple_color_picker" bg_element="content" contrast="<?php echo @$fields_contrast['button_hover_border_color']; ?>" /></td>
            </tr>
            <tr>
                <td style="vertical-align:middle;"><label>Hover Text Color:</label></td>
                <td style="padding: 5px 0px 10px 0px;"><input id="button_hover_text_color" name="data[PreferencesDisplay][button_hover_text_color]" type="text" value="<?php echo $button_hover_text_color; ?>" size="8" class="simple_color_picker" bg_element="content" contrast="<?php echo @$fields_contrast['button_hover_text_color']; ?>" /></td>
            </tr>
            <tr>
                <td><label>Font Style:</label></td>
                <td>
                	<table cellpadding="0" cellspacing="0">
                    	<tr>
                        	<td>
                            	<select name="data[PreferencesDisplay][button_font_family]" id="button_font_family">
									<?php
                                    foreach($fonts as $font)
                                    {
                                        ?>
                                        <option value="<?php echo $font; ?>" <?php if($font == $button_font_family) { echo 'selected="selected"'; } ?>><?php echo $font; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                            <td style="padding-left: 10px;">
                            	<table cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding-right: 10px;">
                                        <label for="button_font_bold"  class="label_check_box" style="font-weight:700;">Bold
                                        <input type="checkbox" name="data[PreferencesDisplay][button_font_bold]" id="button_font_bold" value="1" <?php if($button_font_bold == 1) { echo 'checked="checked"'; } ?> />
                                        </label>
                                        </td>
                                        <td>
                                        <label for="button_font_italic"  class="label_check_box" style="font-style:italic;">Italic
                                        <input type="checkbox" name="data[PreferencesDisplay][button_font_italic]" id="button_font_italic" value="1" <?php if($button_font_italic == 1) { echo 'checked="checked"'; } ?> />
                                        </label>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                	
                </td>
            </tr>
            <tr>
                <td><label>Font Size:</label></td>
                <td>
                	<table cellpadding="0" cellspacing="">
                    	<tr>
                        	<td width="200"><div id="slider-font-size"></div></td>
                            <td style="padding-left: 10px;">
                            	<input type="hidden" name="data[PreferencesDisplay][button_font_size]" id="button_font_size" readonly="readonly" size="2" />
                                <span id="button_font_size_value"></span>
                            </td>
                        </tr>
                    </table>
                	
                    <script>
					$(function() {
						<?php if($button_font_size) : ?>
						$("div.actions ul li a").css( {'font-size' : <?php echo str_replace('px','',$button_font_size);?>});
						<?php endif;?>
						$( "#slider-font-size" ).slider({
							range: "max",
							min: 12,
							max: 18,
							value: <?php echo str_replace('px', '', $button_font_size); ?>,
							slide: function( event, ui ) {
								$("div.actions ul li a").css( {'font-size' : ui.value});
								
								$( "#button_font_size" ).val( ui.value + 'px' );
								$('#button_font_size_value').html(ui.value + 'px');
								
								
							}
						}
						);
						$("#button_font_size").val($("#slider-font-size").slider("value") );//+ 'px'
						$('#button_font_size_value').html($("#slider-font-size").slider("value") + 'px');
					});
					</script>
                </td>
            </tr>
        </table>
    </form>
</div>
<div class="actions">
    <ul>
        <li><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
        <li><a href="javascript: void(0);" onclick="$('#task').val('default'); $('#frm').submit();">Use Default</a></li>
    </ul>
</div>
