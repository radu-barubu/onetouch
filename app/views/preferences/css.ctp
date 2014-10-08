<?php

$settings = $display_settings;

if(!is_array($settings))
{
	exit;
}

extract($settings);

//increase iPad text to match Windows browser size, so easier to touch
$isiPad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
$isdroid = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'Android');
if($isiPad || $isdroid) {
  $body_font_size = $body_font_size + 2;
  $button_font_size = $button_font_size + 1;
  $top_menu_font_size = $top_menu_font_size + 1;
	
  $tab_font_size = $tab_font_size + 2;
	
	if ($tab_font_size >= 20) {
		$tab_font_size = 19;
	}
	
  $autocomplete_line_height = $body_font_size + 15;
  $keypadkey = '28';
} else {
  $autocomplete_line_height = $body_font_size + 3;
    $keypadkey = '18';
}




$top_menu_font_bold = ($top_menu_font_bold == 1) ? "bold" : "normal";
$top_menu_font_italic = ($top_menu_font_italic == 1) ? "italic" : "normal";

$tab_font_bold = ($tab_font_bold == 1) ? "bold" : "normal";
$tab_font_italic = ($tab_font_italic == 1) ? "italic" : "normal";

$body_font_bold = ($body_font_bold == 1) ? "bold" : "normal";
$body_font_italic = ($body_font_italic == 1) ? "italic" : "normal";

$button_font_bold = ($button_font_bold == 1) ? "bold" : "normal";
$button_font_italic = ($button_font_italic == 1) ? "italic" : "normal";

$section_font_bold = ($section_font_bold == 1) ? "bold" : "normal";
$section_font_italic = ($section_font_italic == 1) ? "italic" : "normal";

if(isset($user) && $user['tutor_mode'] && empty($user['patient_id'])) //make sure not a patient
{
 $underline_links='text-decoration:underline;';
}
else
{
 $underline_links='';
}

?>
.logo {
	position: absolute; 
	top: 0; 
	left:0;
	margin:5px 0px 0px 5px;
}
.logo #logo_image {
	width: 140px; 
	height: 69px; 
	background-image:url(<?php echo (!empty($_SESSION['PartnerData']['small_logo']))? '/img/' .$_SESSION['PartnerData']['small_logo']:'/img/onetouch-small2.png';   ?>);
	background-repeat:no-repeat;
}

.hpi_txt_box, .hpi_txt_box2, .editable_field, .chronic_textbox {
	color: #<?php echo $editable_text_color; ?>;
    cursor: pointer;
}
.hpi_txt_box:hover, .hpi_txt_box2:hover, .editable_field:hover, .chronic_textbox:hover {
	background: #<?php echo $editable_hightlight_color; ?>;
}
a:link { 
    color: #<?php echo $link_color; ?>;
    <?php echo $underline_links;?>
}
a:visited {
    color: #<?php echo $link_visited_color; ?> !important; 
    <?php echo $underline_links;?>
}
a:hover { 
    color: #<?php echo $link_hover_color; ?> !important; 
    <?php echo $underline_links;?>
}
a:active {
    color: #<?php echo $link_active_color; ?> !important; 
    <?php echo $underline_links;?>
}

div.actions ul li a, .btn, a.btn { 
	background: #<?php echo $button_background_color_from; ?>;
    background: -moz-linear-gradient(center top, #<?php echo $button_background_color_from; ?>, #<?php echo $button_background_color_to; ?>) repeat scroll 0 0 transparent; 
    background: -webkit-gradient(linear, left top, left bottom, from(#<?php echo $button_background_color_from; ?>), to(#<?php echo $button_background_color_to; ?>));
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#<?php echo $button_background_color_from; ?>', endColorstr='#<?php echo $button_background_color_to; ?>');
    color: #<?php echo $button_text_color; ?>; 
    font-family: "<?php echo $button_font_family; ?>";
    font-weight: <?php echo $button_font_bold; ?>;
    font-style: <?php echo $button_font_italic; ?>;
    border: 1px solid #<?php echo $button_border_color; ?>; 
    font-size: <?php echo $button_font_size; ?>px;
}

.smallbtn, a.smallbtn {
        font-weight:<?php echo $button_font_bold; ?>;
        font-size:<?php echo $button_font_size - 3; ?>px;
	cursor: pointer;
	padding: 3px 3px;
	margin-right: 2px;
	text-decoration: none;
	color: #<?php echo $button_text_color; ?>; 
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	border: 1px solid #<?php echo $button_border_color; ?>;
	background: -moz-linear-gradient(center top, #fefefe, #eee) repeat scroll 0 0 transparent;
	background: -webkit-gradient(linear, left top, left bottom, from(#fefefe), to(#eee));
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fefefe', endColorstr='#eeeeee');
}

div.actions ul li a:hover, .btn:hover, a.btn:hover, .smallbtn:hover, a.smallbtn:hover{ 
    color: #<?php echo $button_hover_text_color; ?> !important; 
    border: 1px solid #<?php echo $button_hover_border_color; ?>; 
}

div.actions ul li a:active, .btn:active, a.btn:active, .smallbtn:active, a.smallbtn:active { 
    background: -moz-linear-gradient(center top, #<?php echo $button_background_color_to; ?>, #<?php echo $button_background_color_from; ?>) repeat scroll 0 0 transparent; 
    background: -webkit-gradient(linear, left top, left bottom, from(#<?php echo $button_background_color_to; ?>), to(#<?php echo $button_background_color_from; ?>)); 
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#<?php echo $button_background_color_to; ?>', endColorstr='#<?php echo $button_background_color_from; ?>');
}

#nav, #nav ul li a {
    font-weight: <?php echo $top_menu_font_bold; ?>;
    font-size: <?php echo $top_menu_font_size; ?>px;
    font-family: "<?php echo $top_menu_font_family; ?>";
    font-style: <?php echo $top_menu_font_italic; ?>;
    color: #<?php echo $top_menu_font_color; ?>;
}
#nav, #nav ul li a:link {
	color: #<?php echo $top_menu_font_color; ?> !important;
}
#nav, #nav ul li a:visited {
	color: #<?php echo $top_menu_font_color; ?> !important;
}
#nav, #nav ul li a:hover {
	color: #<?php echo $top_menu_font_color; ?> !important;
}
#nav, #nav ul li a:active {
	color: #<?php echo $top_menu_font_color; ?> !important;
}
.ui-tabs .ui-tabs-nav li  {
    font-weight: <?php echo $tab_font_bold; ?>;
    font-size: <?php echo $tab_font_size; ?>px;
    font-family: "<?php echo $tab_font_family; ?>";
    font-style: <?php echo $tab_font_italic; ?>;
}
.ui-tabs .ui-tabs-nav li.ui-tabs-selected a {
    color: #<?php echo $tab_font_color_active; ?>;
}
.ui-tabs .ui-state-default a, .ui-tabs .ui-state-default a:link, .ui-tabs .ui-state-default a:visited {
    color: #<?php echo $tab_font_color_inactive; ?>;
}
html, body, form input[type="text"], form input[type="date"], form input[type="time"], form input[type="password"], form input[type="file"], form select, form textarea, form.dynamic_select select {
    font-weight: <?php echo $body_font_bold; ?>;
    font-size: <?php echo $body_font_size; ?>px;
    font-family: "<?php echo $body_font_family; ?>";
    font-style: <?php echo $body_font_italic; ?>;
    color: #<?php echo $body_font_color; ?>;
}

.file_upload_desc {
	font-weight: <?php echo $body_font_bold; ?>;
    font-size: <?php echo $body_font_size; ?>px;
    font-family: "<?php echo $body_font_family; ?>";
    font-style: <?php echo $body_font_italic; ?>;
    color: #<?php echo $body_font_color; ?>;
}

.ui-widget-content {
	font-weight: <?php echo $body_font_bold; ?>;
    font-size: <?php echo $body_font_size; ?>px;
    font-family: "<?php echo $body_font_family; ?>";
    font-style: <?php echo $body_font_italic; ?>;
    color: #<?php echo $body_font_color; ?>;
}

/* color scheme properties */
html, body {
	background-color: <?php echo $color_scheme_properties['background']; ?>;
}
#header {
	background-color: <?php echo $color_scheme_properties['header']; ?>;
	border-bottom: 1px solid <?php echo $color_scheme_properties['header_border_bottom']; ?>;
}
#nav-container {
	background: <?php echo $color_scheme_properties['nav_container']; ?>;
}
#nav ul li a:hover, #nav ul li.sfHover > a {
	background: <?php echo $color_scheme_properties['nav_ul_li_hover']; ?>;
}
#nav ul li ul li {
	background: <?php echo $color_scheme_properties['nav_ul_li_hover']; ?>;
}
#nav ul li ul li a:hover, #nav ul li ul li.sfHover > a {
	background: <?php echo $color_scheme_properties['nav_ul_li_ul_li_hover']; ?>;
}
.title_area .title_text a:link {
	color: <?php echo $color_scheme_properties['header']; ?> !important;
}
.title_area .title_text a:visited {
	color: <?php echo $color_scheme_properties['header']; ?> !important;
}
.title_area .title_text a:hover {
	color: <?php echo $color_scheme_properties['header']; ?> !important;
}
.title_area .title_text a:active {
	color: <?php echo $color_scheme_properties['header']; ?> !important;
}
.title_area .title_text .title_item:hover, .title_area .title_text .active, .title_area .title_text a:hover, .title_area .title_text a.active, .title_area .title_text a:active {
	background-color: <?php echo $color_scheme_properties['header']; ?>;
    color: #ffffff !important;
}
.title_area .title_text .title_item, .title_area .title_text a, .title_area .title_text .title_disabled {
	border: 1px solid <?php echo $color_scheme_properties['header']; ?>;
    font-weight: <?php echo $section_font_bold; ?>;
    font-size: <?php echo $section_font_size; ?>px;
    font-family: "<?php echo $section_font_family; ?>";
    font-style: <?php echo $section_font_italic; ?>;
    
}
.title_area_small .title_text .title_item:hover, .title_area_small .title_text .active, .title_area_small .title_text a:hover, .title_area_small .title_text a.active, .title_area_small .title_text a:active {
	background-color: <?php echo $color_scheme_properties['header']; ?>;
}

table.listing, table.listingDis {
	border: 1px solid <?php echo $color_scheme_properties['listing_border']; ?>;
}
table.small_table {
	border: 1px solid <?php echo $color_scheme_properties['listing_border']; ?>;
}
table.listing tr th, table.listingDis tr th {
	background: <?php echo $color_scheme_properties['listing_tr_th']; ?>;
}
table.listing tr td, table.listingDis tr td {
	border-bottom: 1px solid <?php echo $color_scheme_properties['listing_border']; ?>;
}

.striped {
	background-color: <?php echo $color_scheme_properties['table_stripped']; ?>;
}
table.small_table th {
	background: <?php echo $color_scheme_properties['listing_tr_th']; ?>;
}
#footer {
	background: <?php echo $color_scheme_properties['nav_container']; ?>;
}

.hpi_area {
	background: <?php echo $color_scheme_properties['listing_border']; ?>;
}

.keypad-popup, .keypad-inline, .keypad-key, .keypad-special {
  font-size:   <?php echo $keypadkey;?>px; 
  /* inside jquery.keypad.css also can adjust the width of the background of the entire box --> width:($.browser.opera?'1000px':'200px')}) */
}

form input[type="text"], form input[type="date"], form input[type="time"], form input[type="password"], form input[type="file"], form select, form textarea {
	border: 1px solid <?php echo $color_scheme_properties['field_border_color']; ?>;
}
#pe_section .pe_section_area, .pe_section .pe_section_area, .pe_image_list .pe_image_item {
	border: 1px solid <?php echo $color_scheme_properties['listing_border']; ?>;
}
/*
.hpi_area .btn {
	font-size: 10px;
}
*/
.photo_area_vertical, .photo_area_horizontal {
	border: 1px solid <?php echo $color_scheme_properties['field_border_color']; ?>;
}

.ui-accordion-content-active, .ui-state-active, .ui-widget-content .ui-state-active, .ui-widget-header .ui-state-active {
	border: 1px solid <?php echo $color_scheme_properties['listing_border']; ?>;
}

#message_notification {
	border: 2px solid <?php echo $color_scheme_properties['field_border_color']; ?>; 
	background:#ffffff; 
}
/* ros tab */
.encounter_section_area { font-size:<?php echo $body_font_size; ?>px}
/* pe tab */
.pe_title_area { font-size:<?php echo $body_font_size; ?>px;}
.pe_item_area { font-size:<?php echo $body_font_size; ?>px;}

/* autocomplete input box text */
.ac_results li {
        margin: 0px;
        padding: 5px 5px;
        cursor: default;
        display: block;
        /*
        if width will be 100% horizontal scrollbar will apear
        when scroll mode will be used
        */
        /*width: 100%;*/
        font: <?=$body_font_size?>px "Lucida Grande", Arial, sans-serif;
        /*
        it is very important, if line-height not setted or setted
        in relative units scroll will be broken in firefox
        */
        line-height: <?=$autocomplete_line_height?>px;
        overflow: hidden;
}


/* jquery ui style fix */
.ui-priority-primary, .ui-widget-content .ui-priority-primary, .ui-widget-header .ui-priority-primary {
	font-weight: normal;
}

.ui-priority-secondary, .ui-widget-content .ui-priority-secondary, .ui-widget-header .ui-priority-secondary  {
	opacity: 1;
}


div.actions ul li a.button_disabled, .button_disabled, a.button_disabled {
	color: #d1d1d1;
	border: 1px solid #ddd;
	background: -moz-linear-gradient(center top, #ffffff, #eeeeee) repeat scroll 0 0 transparent;
	background: -webkit-gradient(linear, left top, left bottom, from(#ffffff), to(#eeeeee));
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#eeeeee');
    cursor: default;
}

div.actions ul li a.button_disabled:hover, .button_disabled:hover, a.button_disabled:hover {
	color: #d1d1d1;
	border: 1px solid #ddd;
}

div.actions ul li a.button_disabled:active, .button_disabled:active, a.button_disabled:active {
	background: -moz-linear-gradient(center top, #ffffff, #eeeeee) repeat scroll 0 0 transparent;
	background: -webkit-gradient(linear, left top, left bottom, from(#ffffff), to(#eeeeee));
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#eeeeee');
}

input.disabled {
    color: #aaa !important;
}

.ui-tabs-selected a{
	cursor: pointer !important;
}

div.colorPicker-palette {
  border: 1px solid <?php echo $color_scheme_properties['listing_border']; ?>;
}

.pe_element_text_edit, .pe_txt_editable_specifier, .pe_txt_editable {
	font-family: "<?php echo $body_font_family; ?>";
}
