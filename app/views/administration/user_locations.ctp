<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
?>
<div style="overflow: hidden;">
	<?php echo $this->element("administration_users_links"); ?>

<p><i>This is a security log of where system users are logging in. It keeps track of their IP address.</i></p>

	<form id="frm" method="post" action="<?php echo $thisURL . '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="35%"><?php echo $paginator->sort('Username', 'UserAccount.username', array('model' => 'UserLocation'));?></th>
				<th width="35%"><?php echo $paginator->sort('Login Date and Time', 'UserLocation.login_timestamp', array('model' => 'UserLocation'));?></th>
				<th width="30%"><?php echo $paginator->sort('IP Address', 'UserLocation.ip_address', array('model' => 'UserLocation'));?></th>
			</tr>

			<?php $i = 0; foreach ($UserLocations as $UserLocation): ?>
			<tr>
				<td><?php echo $UserLocation['UserAccount']['username']; ?></td>
				<td><?php 
				list($login_date, $login_time) = explode(" ", $UserLocation['UserLocation']['login_timestamp']); 
				echo __date($global_date_format, strtotime($login_date)).' '.date($global_time_format, strtotime($login_time));
				?></td>
				<td><a href="http://www.geobytes.com/IpLocator.htm?GetLocation&IpAddress=<?php echo $UserLocation['UserLocation']['ip_address']; ?>" target=_blank><?php echo $UserLocation['UserLocation']['ip_address']; ?> <?php echo $this->Html->image('search-icon.png', array('alt' => 'Trace this IP'));?></a></td>
			</tr>
			<?php endforeach; ?>
		</table>
	</form>

		<div class="paging">
			<?php echo $paginator->counter(array('model' => 'UserLocation', 'format' => __('Display %start%-%end% of %count%', true))); ?>
			<?php
			if ($paginator->hasPrev('UserLocation') || $paginator->hasNext('UserLocation'))
			{
				echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
			}
			?>
			<?php
			if ($paginator->hasPrev('UserLocation'))
			{
				echo $paginator->prev('<< Previous', array('model' => 'UserLocation', 'url' => $paginator->params['pass']), null, array('class' => 'disabled'));
			}
			?>
			<?php echo $paginator->numbers(array('model' => 'UserLocation', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
			<?php
			if ($paginator->hasNext('UserLocation'))
			{
				echo $paginator->next('Next >>', array('model' => 'UserLocation', 'url' => $paginator->params['pass']), null, array('class' => 'disabled'));
			}
			?>
		</div>
</div>