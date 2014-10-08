<?php

$links = array();
$links['Template Styles'] = 'template_styles';
$links['Text Elements'] = 'text_elements';

echo $this->element('links', array('links' => $links));
?>