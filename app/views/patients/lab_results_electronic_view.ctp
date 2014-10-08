<?php 

$named = $this->params['named'];
$named['page'] = 0;

$report_html = str_replace('{__PAGE_URL__}', $this->Html->url($named), $report_html);
$report_html = str_replace('{__BUTTON_FONT_FAMILY__}', $display_settings['button_font_family'], $report_html);
echo $report_html; 
?>