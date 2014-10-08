<?php
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$smallAjaxSwirl=$html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
$this->Html->script(array('sections/tab_navigation.js')); 
?>
 <script language="javascript" type="text/javascript">
     function timeout_listener() {

        if(tseconds < 61)
        {
          var timer_message = "Your session is about to expire due to inactivity in less than 1 minute. <a href='javascript:initAutoLogoff();timeout_listener();'>Click here</a> or just navigate below.";

          if ($("#error_message").is(":hidden"))
          {
             $('#error_message').html(timer_message).slideDown("slow");
          }
          setTimeout("timeout_listener()", 1000);
        } else {
          if ($("#error_message").is(":visible"))
          {
            $('#error_message').slideUp("slow");
          }
          setTimeout("timeout_listener()", 10000);
        }
     }
     setTimeout("timeout_listener()", 10000);
 </script>

            <table cellpadding="0" cellspacing="0" class="listing" border=1>
                <tr>
					<th ><?php echo $paginator->sort('Patient Name', 'PatientDemographic.first_name', array('model' => 'PatientOrders', 'class' => 'ajax'));?></th>
                    <th width="35%"><?php echo $paginator->sort('Test Name', 'PatientOrders.test_name', array('model' => 'PatientOrders', 'class' => 'ajax'));?></th>				
					<th width="10%"><?php echo $paginator->sort('Category', 'PatientOrders.category', array('model' => 'PatientOrders', 'class' => 'ajax'));?></th>
                    <th width="15%"><?php echo $paginator->sort('Type', 'PatientOrderstype', array('model' => 'PatientOrders', 'class' => 'ajax'));?></th>
                    <th width="10%"><?php echo $paginator->sort('Status', 'PatientOrders.status', array('model' => 'PatientOrders', 'class' => 'ajax'));?></th>
                </tr>
                <?php
                $i = 0;
                foreach ($patient_orders as $patient_order):
                ?>
                <tr   editlink="<?php echo $patient_order['PatientOrders']['editlink']; ?>">
                    <td><?php echo $patient_order['PatientDemographic']['patientName']; ?></td>					
                    <td><?php echo $patient_order['PatientOrders']['test_name']; ?></td>					
					<td><?php echo $patient_order['PatientOrders']['category']; ?></td>					
					<td><?php echo $patient_order['PatientOrders']['type']; ?></td>					
                    <td><?php echo $patient_order['PatientOrders']['status']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>

        
        <div style="width: 60%; float: right; margin-top: 15px;">
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'PatientOrders', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientOrders') || $paginator->hasNext('PatientOrders'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientOrders'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientOrders', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientOrders', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientOrders', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
        </div>
