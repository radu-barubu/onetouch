<?php 

if (!isset($escape)) {
	$escape = true;
}

?>
<div class="title_area">
    <div class="title_text">
        <?php foreach ($links as $link_text => $link_action): ?>
            <?php
            if (is_array($link_action))
            {
                if(isset($link_action['action']))
                {
									
										if (!is_array($link_action['action'])) {
											$sent_action = $link_action['action'];

											$class = true;

											foreach($compare_params as $param)
											{
													if($link_action[$param] != ${$param})
													{
															$class = false;
													}
											}

											$full_link = $link_action;
										} else {
											
											$class = (in_array($this->params['action'], $link_action['action']));
											
											$default = $link_action['action'][0];
											
											$link_action['action'] = $default;
											
											$full_link = $link_action;
										}
                }
                else
                {
                    $sent_action = $link_action[0];
                    $class = (in_array($this->params['action'], $link_action));
                }
            }
            else
            {
                $sent_action = $link_action;
                $class = ($this->params['action'] == $link_action);
            }
            ?>
            <?php echo $html->link($link_text, (isset($full_link)?$full_link:array('action' => $sent_action)), array('class' => $class ? 'active' : '', 'escape' => $escape)); ?>
        <?php endforeach; ?>
    </div>
        <?php echo @$additional_contents; ?>
</div>