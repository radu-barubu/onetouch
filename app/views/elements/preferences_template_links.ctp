<?php

$links = array(
    'ROS Template' => 'ros_template',
    'PE Template' => 'pe_template'
);

echo $this->element('links', array('links' => $links, 'additional_contents' => @$additional_contents));
?>