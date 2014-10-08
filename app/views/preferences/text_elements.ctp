<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

extract($settings);

$hidden = (isset($isiPadApp)&&$isiPadApp) ? ' style="display:none;"' : '';
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
        <table cellpadding="0" cellspacing="0">
            <tr<?php echo $hidden;?>>
                <td colspan="2"><h3>Top Menu</h3></td>
            </tr>
            <tr<?php echo $hidden;?>>
                <td width="150"><label>Font Style:</label></td>
                <td>
                	<table cellpadding="0" cellspacing="0">
                    	<tr>
                        	<td>
                            	<select name="data[PreferencesDisplay][top_menu_font_family]" id="top_menu_font_family">
									<?php
                                    foreach($fonts as $font)
                                    {
                                        ?>
                                        <option value="<?php echo $font; ?>" <?php if($font == $top_menu_font_family) { echo 'selected="selected"'; } ?>><?php echo $font; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                            <td style="padding-left: 10px;">
                            	<table cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding-right: 10px;">
 						<label for="top_menu_font_bold"   class="label_check_box" style="font-weight:700;">Bold
					   <input type="checkbox" name="data[PreferencesDisplay][top_menu_font_bold]" id="top_menu_font_bold" value="1" <?php if($top_menu_font_bold == 1) { echo 'checked="checked"'; } ?> />
                      </label>
					</td>
					<td>
                    	<label for="top_menu_font_italic"  class="label_check_box" style="font-style:italic;">Italic
					   <input type="checkbox" name="data[PreferencesDisplay][top_menu_font_italic]" id="top_menu_font_italic" value="1" <?php if($top_menu_font_italic == 1) { echo 'checked="checked"'; } ?> />
                       </label>

					</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr<?php echo $hidden;?>>
                <td style="vertical-align: middle;"><label>Font Color:</label></td>
                <td style="padding: 5px 0px;"><input id="top_menu_font_color" name="data[PreferencesDisplay][top_menu_font_color]" type="text" value="<?php echo $top_menu_font_color; ?>" size="8" class="simple_color_picker" bg_element_color="<?php echo $settings['color_scheme_properties']['nav_ul_li_hover']; ?>" contrast="<?php echo @$fields_contrast['top_menu_font_color']; ?>" /></td>
            </tr>
            <tr<?php echo $hidden;?>>
                <td><label>Font Size:</label></td>
                <td style="padding-top: 10px;">
                	<table cellpadding="0" cellspacing="">
                    	<tr>
                        	<td width="200"><div id="top_menu_font_size_slider"></div></td>
                            <td style="padding-left: 10px;">
                            	<input type="hidden" name="data[PreferencesDisplay][top_menu_font_size]" id="top_menu_font_size" readonly="readonly" />
                                <span id="top_menu_font_size_value"></span>
                            </td>
                        </tr>
                    </table>
                	
                    <script>
					$(function() {
						$( "#top_menu_font_size_slider" ).slider({
							range: "max",
							min: 12,
							max: 18,
							value: <?php echo str_replace('px', '', $top_menu_font_size); ?>,
							slide: function( event, ui ) {
								$( "#top_menu_font_size" ).val( ui.value ); //+ 'px'
								$('#top_menu_font_size_value').html(ui.value + 'px');
							}
						});
						$("#top_menu_font_size").val($("#top_menu_font_size_slider").slider("value") ); //+ 'px'
						$('#top_menu_font_size_value').html($("#top_menu_font_size_slider").slider("value") + 'px');
					});
					</script>
                </td>
            </tr>
            <tr<?php echo $hidden;?>>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr<?php echo $hidden;?>>
                <td colspan="2"><h3>Section Menu</h3></td>
            </tr>
            <tr<?php echo $hidden;?>>
                <td width="150"><label>Font Style:</label></td>
                <td>
                	<table cellpadding="0" cellspacing="0">
                    	<tr>
                        	<td>
                            	<select name="data[PreferencesDisplay][section_font_family]" id="section_font_family">
									<?php
                                    foreach($fonts as $font)
                                    {
                                        ?>
                                        <option value="<?php echo $font; ?>" <?php if($font == $section_font_family) { echo 'selected="selected"'; } ?>><?php echo $font; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                            <td style="padding-left: 10px;">
                            	<table cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding-right: 10px;">
                                        <label for="section_font_bold"   class="label_check_box" style="font-weight:700;">Bold
                                        <input type="checkbox" name="data[PreferencesDisplay][section_font_bold]" id="section_font_bold" value="1" <?php if($section_font_bold == 1) { echo 'checked="checked"'; } ?> />				
                                        </label>
                                        </td>
                                        <td>
                                        <label for="section_font_italic"  class="label_check_box" style="font-style:italic;">Italic
                                        <input type="checkbox" name="data[PreferencesDisplay][section_font_italic]" id="section_font_italic" value="1" <?php if($section_font_italic == 1) { echo 'checked="checked"'; } ?> />
                                        </label>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr<?php echo $hidden;?>>
                <td><label>Font Size:</label></td>
                <td>
                	<table cellpadding="0" cellspacing="">
                    	<tr>
                        	<td width="200"><div id="section_font_size_slider"></div></td>
                            <td style="padding-left: 10px;">
                            	<input type="hidden" name="data[PreferencesDisplay][section_font_size]" id="section_font_size" readonly="readonly" />
                                <span id="section_font_size_value"></span>
                            </td>
                        </tr>
                    </table>
                	
                    <script>
					$(function() {
						$( "#section_font_size_slider" ).slider({
							range: "max",
							min: 12,
							max: 18,
							value: <?php echo str_replace('px', '', $section_font_size); ?>,
							slide: function( event, ui ) {
								$( "#section_font_size" ).val( ui.value ); //+ 'px'
								$('#section_font_size_value').html(ui.value + 'px');
							}
						});
						$("#section_font_size").val($("#section_font_size_slider").slider("value"));
						$('#section_font_size_value').html($("#section_font_size_slider").slider("value") + 'px');
					});
					</script>
                </td>
            </tr>
            <tr<?php echo $hidden;?>>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2"><h3>Tabs (Patients &amp; Encounters)</h3></td>
            </tr>
            <tr>
                <td width="150"><label>Font Style:</label></td>
                <td>
                	<table cellpadding="0" cellspacing="0">
                    	<tr>
                        	<td>
                            	<select name="data[PreferencesDisplay][tab_font_family]" id="tab_font_family">
									<?php
                                    foreach($fonts as $font)
                                    {
                                        ?>
                                        <option value="<?php echo $font; ?>" <?php if($font == $tab_font_family) { echo 'selected="selected"'; } ?>><?php echo $font; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                            <td style="padding-left: 10px;">
                            	<table cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding-right: 10px;">
                                        <label for="tab_font_bold"  class="label_check_box" style="font-weight:700;">Bold
                                        <input type="checkbox" name="data[PreferencesDisplay][tab_font_bold]" id="tab_font_bold" value="1" <?php if($tab_font_bold == 1) { echo 'checked="checked"'; } ?> />
                                        </label>
                                        </td>
                                        <td>
                                        <label for="tab_font_italic"  class="label_check_box" style="font-style:italic;">Italic
                                        <input type="checkbox" name="data[PreferencesDisplay][tab_font_italic]" id="tab_font_italic" value="1" <?php if($tab_font_italic == 1) { echo 'checked="checked"'; } ?> />
                                        </label></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="vertical-align:middle;"><label>Active Color:</label></td>
                <td style="padding: 5px 0px;"><input id="tab_font_color_active" name="data[PreferencesDisplay][tab_font_color_active]" type="text" value="<?php echo $tab_font_color_active; ?>" size="8" class="simple_color_picker" bg_element_color="<?php echo $settings['color_scheme_properties']['tab_bg']; ?>" contrast="<?php echo @$fields_contrast['tab_font_color_active']; ?>" /></td>
            </tr>
            <tr>
                <td style="vertical-align:middle;"><label>Inctive Color:</label></td>
                <td style="padding: 5px 0px;"><input id="tab_font_color_inactive" name="data[PreferencesDisplay][tab_font_color_inactive]" type="text" value="<?php echo $tab_font_color_inactive; ?>" size="8" class="simple_color_picker" bg_element_color="<?php echo $settings['color_scheme_properties']['tab_bg']; ?>" contrast="<?php echo @$fields_contrast['tab_font_color_inactive']; ?>" /></td>
            </tr>
            <tr>
                <td><label>Font Size:</label></td>
                <td style="padding-top: 10px;">
                	<table cellpadding="0" cellspacing="">
                    	<tr>
                        	<td width="200"><div id="tab_font_size_slider"></div></td>
                            <td style="padding-left: 10px;">
                            	<input type="hidden" name="data[PreferencesDisplay][tab_font_size]" id="tab_font_size" readonly="readonly" />
                                <span id="tab_font_size_value"></span>
                            </td>
                        </tr>
                    </table>
                	
                    <script>
					$(function() {
						$( "#tab_font_size_slider" ).slider({
							range: "max",
							min: 12,
							max: 18,
							value: <?php echo str_replace('px', '', $tab_font_size); ?>,
							slide: function( event, ui ) {
								$( "#tab_font_size" ).val( ui.value ); //+ 'px'
								$('#tab_font_size_value').html(ui.value + 'px');
							}
						});
						$("#tab_font_size").val($("#tab_font_size_slider").slider("value"));
						$('#tab_font_size_value').html($("#tab_font_size_slider").slider("value") + 'px');
					});
					</script>
                </td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2"><h3>Body</h3></td>
            </tr>
            <tr>
                <td width="150"><label>Font Style:</label></td>
                <td>
                	<table cellpadding="0" cellspacing="0">
                    	<tr>
                        	<td>
                            	<select name="data[PreferencesDisplay][body_font_family]" id="body_font_family">
									<?php
                                    foreach($fonts as $font)
                                    {
                                        ?>
                                        <option value="<?php echo $font; ?>" <?php if($font == $body_font_family) { echo 'selected="selected"'; } ?>><?php echo $font; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                            <td style="padding-left: 10px;">
                            	<table cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding-right: 10px;">
                                        <label for="body_font_bold"  class="label_check_box" style="font-weight:700;">Bold
                                        <input type="checkbox" name="data[PreferencesDisplay][body_font_bold]" id="body_font_bold" value="1" <?php if($body_font_bold == 1) { echo 'checked="checked"'; } ?> />
                                        </label>
                                        </td>
                                        <td>
                                        <label for="body_font_italic"  class="label_check_box" style="font-style:italic;">Italic
                                        <input type="checkbox" name="data[PreferencesDisplay][body_font_italic]" id="body_font_italic" value="1" <?php if($body_font_italic == 1) { echo 'checked="checked"'; } ?> />
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
                <td style="vertical-align:middle;"><label>Font Color:</label></td>
                <td style="padding: 5px 0px;"><input id="body_font_color" name="data[PreferencesDisplay][body_font_color]" type="text" value="<?php echo $body_font_color; ?>" size="8" class="simple_color_picker" bg_element="content" contrast="<?php echo @$fields_contrast['body_font_color']; ?>" /></td>
            </tr>
            <tr>
                <td><label>Font Size:</label></td>
                <td style="padding-top: 10px;">
                	<table cellpadding="0" cellspacing="">
                    	<tr>
                        	<td width="200"><div id="body_font_size_slider"></div></td>
                            <td style="padding-left: 10px;">
                            	<input type="hidden" name="data[PreferencesDisplay][body_font_size]" id="body_font_size" readonly="readonly" />
                                <span id="body_font_size_value"></span>
                            </td>
                        </tr>
                    </table>
                	
                    <script>
					$(function() {
						$( "#body_font_size_slider" ).slider({
							range: "max",
							min: 12,
							max: 18,
							value: <?php echo str_replace('px', '', $body_font_size); ?>,
							slide: function( event, ui ) {
								$( "#body_font_size" ).val( ui.value);// + 'px'
								$('#body_font_size_value').html(ui.value + 'px');
							}
						});
						$("#body_font_size").val($("#body_font_size_slider").slider("value"));
						$('#body_font_size_value').html($("#body_font_size_slider").slider("value") + 'px');
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
