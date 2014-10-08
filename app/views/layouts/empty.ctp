<?php echo $content_for_layout; ?>
<?php
$debugval=Configure::read('debug');
    $SERVERNAME=isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
    list($customer,)=explode('.', $SERVERNAME);
    $customer = strtolower($customer);

if($debugval == 2 && (substr($customer, 0, 2) != 'qa') ){
 //echo $this->element('sql_dump');
}
?>
