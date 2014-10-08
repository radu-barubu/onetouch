<?php
$practice_settings = $this->Session->read("PracticeSetting");
$rx_setup =  $practice_settings['PracticeSetting']['rx_setup'];

if($rx_setup == 'Electronic_Dosespot')
{
    $show_all_refill_link = $html->url(array('controller'=>'patients','action' => 'dosespot_refill_summary'));
}
else
{
    $show_all_refill_link = $html->url(array('controller'=>'patients','action' => 'refill_summary'));
}
?>
<style>
.btn, a.btn {
	/*display:block;*/
	color: #464646;
	cursor: pointer;
	padding: 5px 6px;
	margin-right: 5px;
	text-decoration: none;
	font-weight: bold;
	float: left;
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
	border: 1px solid #ddd;
	background: -moz-linear-gradient(center top, #fefefe, #eee) repeat scroll 0 0 transparent;
	background: -webkit-gradient(linear, left top, left bottom, from(#fefefe), to(#eee));
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fefefe', endColorstr='#eeeeee');
}
</style>
<?php if (count($refills) > 0) { ?>
<div style="float:right;"><a target="_parent" class='btn' href="<?php echo $show_all_refill_link; ?>">Show All</a></div>
<?php } ?>
<div style="clear:both"></div>
<?php
if($rx_setup == 'Electronic_Dosespot')
{
    if (count($refills) > 0)
    {
        $i=0;
        foreach ($refills as $refill):
        if($refill['patient_exist'] == 0)
        {
            $url = $html->url(array('controller' => 'reports', 'action' => 'unmatched_rxrefill_requests', 'task' => 'view_refill_request', 'refill_request_id' => $refill['refill_request_id'], array('escape' => false)));             
        }
        else
        {
            $url = $html->url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information',  'task' => 'edit', 'patient_id' => $refill['patient_id'], 'view_medications' => 1, 'dosespot' => 'show_dosespot_refill'), array('escape' => false)); 
        }
        
        
        echo "<div id='msg_content' class='dashboard-hoverable'><a href=\"#\" onclick=\"top.document.location = '".$url."'\">".$refill['patient_name']." - ".$refill['medication_name'];
             
        if($refill['requested_date'] != '')
        {
            $date_only = explode('T', $refill['requested_date']);
            echo ", ".__date($global_date_format, strtotime($date_only[0]));
        }     
             
        echo "</a></div>";
            $i++;	 
        endforeach;
    ?>
    
    <?php	
    }
    else
    {
        echo "<p>No new refill...</p>";
    }
}
else
{
    if (count($refills) > 0)
    {
        $i=0;
        foreach ($refills as $refill):
        echo "<div id='msg_content' class='dashboard-hoverable'>
            <a href=\"#\" onclick=\"top.document.location = '".$html->url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information',  'task' => 'edit', 'patient_id' => $refill['patient_id'], 'view_medications' => 1, 'refill_id' => $refill['refill_id']), array('escape' => false))."'\">"
             .$refill['name']." - ".$refill['medication']."</a></div>";
            $i++;	 
        endforeach;
    ?>
    
    <?php	
    }
    else
    {
        echo "<p>No new refill...</p>";
    }
}
?>
