<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<script language="javascript" type="text/javascript">
	var basePath = '<?php echo $this->Session->webroot; ?>';
</script>
<?php
	$display_settings = $this->Session->read('display_settings');
	$user = $this->Session->read('UserAccount');
	
	echo $this->Html->css(array(
		'global.css',
		'jquery.keypad.css',
		'jquery.autocomplete.css',
		(isset($new_uploader)? '../uploadify/uploadify.css':'uploadify.css'),
		'jPicker-1.1.6.css',
		'jquery.bubblepopup.v2.3.1.css',
        'jquery.lightbox-0.5.css'
	));
?>
<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>preferences/css/random:<?php echo md5(microtime()); ?>/" />
<style>
	body, html {
		background: none;	
	}
</style>
</head>
<body>
<div style="padding: 10px 20px 10px 10px;">
<table class="small_table" width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
    	<th colspan="2"><?php echo $current_cqm['measure_name']; ?> (<?php echo $current_cqm['code']; ?>)</th>
    </tr>
    <tr class="no_hover">
        <td width="180"><strong>EMeasure Name</strong></td>
        <td><?php echo $current_cqm['measure_name']; ?></td>
    </tr>
    <tr class="no_hover">
        <td class="striped"><strong>Measure Steward</strong></td>
        <td class="striped"><?php echo $current_cqm['measure_steward']; ?></td>
    </tr>
    <tr class="no_hover">
        <td><strong>Endorsed by</strong></td>
        <td><?php echo $current_cqm['endorsed_by']; ?></td>
    </tr>
    <tr class="no_hover">
        <td class="striped"><strong>Description</strong></td>
        <td class="striped"><?php echo $current_cqm['description']; ?></td>
    </tr>
    <tr class="no_hover">
        <td><strong>Measure scoring</strong></td>
        <td><?php echo $current_cqm['measure_scoring']; ?></td>
    </tr>
    <tr class="no_hover">
        <td class="striped"><strong>Measure type</strong></td>
        <td class="striped"><?php echo $current_cqm['measure_type']; ?></td>
    </tr>
    <tr class="no_hover">
        <td><strong>Rationale</strong></td>
        <td><?php echo $current_cqm['rationale']; ?></td>
    </tr>
    <tr class="no_hover">
        <td class="striped"><strong>Clinical Recommendation Statement</strong></td>
        <td class="striped"><?php echo $current_cqm['clinical_recommendation_statement']; ?></td>
    </tr>
    <tr class="no_hover">
        <td><strong>Improvement notation</strong></td>
        <td><?php echo $current_cqm['improvement_notation']; ?></td>
    </tr>
    <tr class="no_hover">
        <td class="striped"><strong>Measurement duration</strong></td>
        <td class="striped"><?php echo $current_cqm['measurement_duration']; ?></td>
    </tr>
    <tr class="no_hover">
        <td><strong>References</strong></td>
        <td><?php echo $current_cqm['references']; ?></td>
    </tr>
    <tr class="no_hover">
        <td class="striped"><strong>Definitions</strong></td>
        <td class="striped"><?php echo $current_cqm['definitions']; ?></td>
    </tr>
</table>
<br>
<?php echo $current_cqm['criteria']; ?>
</div>
</body>
</html>