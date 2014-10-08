<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$plan_id = (isset($this->params['named']['plan_id'])) ? $this->params['named']['plan_id'] : "";
echo $this->Html->script('ipad_fix.js');
$thisURL = str_replace("plan_id:$plan_id", "", $thisURL);

$page_access = $this->QuickAcl->getAccessType('patients', 'medical_information');
echo $this->element("enable_acl_read", array('page_access' => $page_access));
?>
<script language="javascript" type="text/javascript">
 $(document).ready(function()
    {
	    initCurrentTabEvents('vitals_area');
    });	
</script>
<div style="overflow: hidden;">    
    <span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <div id="vitals_area" class="tab_area">

      
      <?php echo $this->element('../encounters/sections/load_vitals'); ?>
      
    
    </div>
</div>
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
